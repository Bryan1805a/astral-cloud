<?php

require_once __DIR__ . '/../config/vnpay.php';

class PaymentController {
    public function vnpayReturn(): void {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $inputData = $_GET;
        $secureHash = $inputData['vnp_SecureHash'] ?? '';

        if (!vnpayVerifyReturn($inputData, $secureHash)) {
            $this->redirectWithError("Invalid signature from payment gateway.");
        }

        $txnRef      = $inputData['vnp_TxnRef'] ?? '';
        $responseCode = $inputData['vnp_ResponseCode'] ?? '';

        // Parse order_id from txnRef (format: order_{id}_{timestamp})
        $txnParts = explode('_', $txnRef);
        $orderId = (int)($txnParts[1] ?? 0);

        $payment = Payment::findByOrderId($orderId);
        if (!$payment) {
            $this->redirectWithError("Payment transaction not found.");
        }

        $order = Order::findById($payment['order_id']);
        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            $this->redirectWithError("Order not found.");
        }

        if ($payment['status'] === 'success') {
            header("Location: /checkout/success?order_id=" . $payment['order_id']);
            exit;
        }

        if ($responseCode === '00') {
            $vnpTransactionNo = $inputData['vnp_TransactionNo'] ?? '';
            Payment::markSuccess($payment['id'], $vnpTransactionNo);

            AuditLog::log('payment.success', 'payment', $payment['id'],
                "VNPay payment successful for order #{$order['id']}. Transaction: {$vnpTransactionNo}"
            );

            header("Location: /checkout/success?order_id=" . $payment['order_id']);
            exit;
        } else {
            Payment::markFailed($payment['id']);

            AuditLog::log('payment.failed', 'payment', $payment['id'],
                "VNPay payment failed for order #{$order['id']}. ResponseCode: {$responseCode}"
            );

            $errorMsg = urlencode("Payment failed (code: {$responseCode}). Please try again.");
            header("Location: /checkout?error={$errorMsg}&order_id=" . $payment['order_id']);
            exit;
        }
    }

    public function vnpayIpn(): void {
        $inputData  = $_GET;
        $secureHash = $inputData['vnp_SecureHash'] ?? '';

        if (!vnpayVerifyReturn($inputData, $secureHash)) {
            $this->ipnResponse('97', 'Invalid signature');
        }

        $txnRef       = $inputData['vnp_TxnRef'] ?? '';
        $responseCode = $inputData['vnp_ResponseCode'] ?? '';

        // Parse order_id from txnRef (format: order_{id}_{timestamp})
        $txnParts = explode('_', $txnRef);
        $orderId = (int)($txnParts[1] ?? 0);

        $payment = Payment::findByOrderId($orderId);
        if (!$payment) {
            $this->ipnResponse('01', 'Order not found');
        }

        if ($payment['status'] === 'success') {
            $this->ipnResponse('02', 'Order already confirmed');
        }

        if ($responseCode === '00') {
            $vnpTransactionNo = $inputData['vnp_TransactionNo'] ?? '';
            Payment::markSuccess($payment['id'], $vnpTransactionNo);

            AuditLog::logSystem('payment.success', 'payment', $payment['id'],
                "VNPay IPN confirmed payment for order #{$payment['order_id']}. Transaction: {$vnpTransactionNo}"
            );

            $this->ipnResponse('00', 'Confirm Success');
        } else {
            Payment::markFailed($payment['id']);

            AuditLog::logSystem('payment.failed', 'payment', $payment['id'],
                "VNPay IPN: payment failed for order #{$payment['order_id']}. ResponseCode: {$responseCode}"
            );

            $this->ipnResponse('00', 'Payment Failed');
        }
    }

    private function ipnResponse(string $code, string $message): void {
        echo json_encode(['RspCode' => $code, 'Message' => $message]);
        exit;
    }

    private function redirectWithError(string $message): void {
        $error = urlencode($message);
        header("Location: /checkout?error={$error}");
        exit;
    }
}
