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
            die('vnp_ResponseCode=97&vnp_Message=InvalidSignature');
        }

        $txnRef       = $inputData['vnp_TxnRef'] ?? '';
        $responseCode = $inputData['vnp_ResponseCode'] ?? '';

        // Parse order_id from txnRef (format: order_{id}_{timestamp})
        $txnParts = explode('_', $txnRef);
        $orderId = (int)($txnParts[1] ?? 0);

        $payment = Payment::findByOrderId($orderId);
        if (!$payment) {
            die('vnp_ResponseCode=01&vnp_Message=TransactionNotFound');
        }

        if ($payment['status'] === 'success') {
            die('vnp_ResponseCode=02&vnp_Message=AlreadyProcessed');
        }

        if ($responseCode === '00') {
            $vnpTransactionNo = $inputData['vnp_TransactionNo'] ?? '';
            Payment::markSuccess($payment['id'], $vnpTransactionNo);

            AuditLog::log('payment.success', 'payment', $payment['id'],
                "VNPay IPN confirmed payment for order #{$payment['order_id']}. Transaction: {$vnpTransactionNo}"
            );

            die('vnp_ResponseCode=00&vnp_Message=Success');
        } else {
            Payment::markFailed($payment['id']);

            AuditLog::log('payment.failed', 'payment', $payment['id'],
                "VNPay IPN: payment failed for order #{$payment['order_id']}. ResponseCode: {$responseCode}"
            );

            die('vnp_ResponseCode=00&vnp_Message=PaymentFailed');
        }
    }

    private function redirectWithError(string $message): void {
        $error = urlencode($message);
        header("Location: /checkout?error={$error}");
        exit;
    }
}
