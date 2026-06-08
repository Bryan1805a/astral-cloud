<?php

function vnpayCreatePaymentUrl(string $txnRef, float $amount, string $orderInfo, string $ipAddr, string $returnUrl, ?string $ipnUrl = null): string {
    $tmnCode    = getenv('VNP_TMN_CODE');
    $hashSecret = getenv('VNP_HASH_SECRET');
    $vnpUrl     = getenv('VNP_URL');

    if ($ipnUrl === null) {
        $ipnUrl = preg_replace('/\/vnpay-return$/', '/vnpay-ipn', $returnUrl);
    }

    $inputData = [
        'vnp_Version'    => '2.1.0',
        'vnp_TmnCode'    => $tmnCode,
        'vnp_Amount'     => (int) round($amount * 100),
        'vnp_Command'    => 'pay',
        'vnp_CreateDate' => date('YmdHis'),
        'vnp_CurrCode'   => 'VND',
        'vnp_IpAddr'     => $ipAddr,
        'vnp_IpnUrl'     => $ipnUrl,
        'vnp_Locale'     => 'vn',
        'vnp_OrderInfo'  => $orderInfo,
        'vnp_OrderType'  => 'other',
        'vnp_ReturnUrl'  => $returnUrl,
        'vnp_TxnRef'     => $txnRef,
    ];

    ksort($inputData);

    $hashData = [];
    foreach ($inputData as $key => $value) {
        $hashData[] = urlencode($key) . '=' . urlencode($value);
    }
    $hashString = implode('&', $hashData);

    $inputData['vnp_SecureHash'] = hash_hmac('sha512', $hashString, $hashSecret);

    return $vnpUrl . '?' . http_build_query($inputData);
}

function vnpayVerifyReturn(array $inputData, string $secureHash): bool {
    $hashSecret = getenv('VNP_HASH_SECRET');

    unset($inputData['vnp_SecureHash']);

    ksort($inputData);

    $hashData = [];
    foreach ($inputData as $key => $value) {
        $hashData[] = urlencode($key) . '=' . urlencode($value);
    }
    $hashString = implode('&', $hashData);

    $computedHash = hash_hmac('sha512', $hashString, $hashSecret);

    return hash_equals($computedHash, $secureHash);
}
