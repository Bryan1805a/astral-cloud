<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
function vnpayCreatePaymentUrl(string $txnRef, float $amount, string $orderInfo, string $ipAddr, string $returnUrl): string {
    $tmnCode    = trim(getenv('VNP_TMN_CODE'));
    $hashSecret = trim(getenv('VNP_HASH_SECRET'));
    $vnpUrl     = getenv('VNP_URL');

    $inputData = [
        'vnp_Version'    => '2.1.0',
        'vnp_TmnCode'    => $tmnCode,
        'vnp_Amount'     => (int) round($amount * 100),
        'vnp_Command'    => 'pay',
        'vnp_CreateDate' => date('YmdHis'),
        'vnp_CurrCode'   => 'VND',
        'vnp_ExpireDate' => date('YmdHis', strtotime('+15 minutes')),
        'vnp_IpAddr'     => $ipAddr,
        'vnp_Locale'     => 'vn',
        'vnp_OrderInfo'  => $orderInfo,
        'vnp_OrderType'  => 'other',
        'vnp_ReturnUrl'  => $returnUrl,
        'vnp_TxnRef'     => $txnRef,
    ];

    ksort($inputData);

    $query = '';
    $hashData = '';
    $i = 0;
    foreach ($inputData as $key => $value) {
        $encoded = urlencode($key) . '=' . urlencode($value);
        if ($i === 1) {
            $hashData .= '&' . $encoded;
        } else {
            $hashData .= $encoded;
            $i = 1;
        }
        $query .= $encoded . '&';
    }

    $secureHash = hash_hmac('sha512', $hashData, $hashSecret);

    return $vnpUrl . '?' . $query . 'vnp_SecureHash=' . $secureHash;
}

function vnpayVerifyReturn(array $inputData, string $secureHash): bool {
    $hashSecret = getenv('VNP_HASH_SECRET');

    $filtered = [];
    foreach ($inputData as $key => $value) {
        if (str_starts_with($key, 'vnp_') && $key !== 'vnp_SecureHash' && $key !== 'vnp_SecureHashType') {
            $filtered[$key] = $value;
        }
    }

    ksort($filtered);

    $hashData = [];
    foreach ($filtered as $key => $value) {
        $hashData[] = urlencode($key) . '=' . urlencode($value);
    }
    $hashString = implode('&', $hashData);

    $computedHash = hash_hmac('sha512', $hashString, $hashSecret);

    return hash_equals($computedHash, $secureHash);
}
