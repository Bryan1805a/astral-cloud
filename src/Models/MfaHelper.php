<?php
namespace App\Models;

/**
 * MfaHelper — RFC 6238 TOTP (Time-based One-Time Password)
 *
 * No external dependencies. Pure PHP implementation of:
 *   - Base32 encode/decode for secret storage
 *   - HMAC-SHA1 TOTP code generation (6 digits, 30-second period)
 *   - Verification with ±1 window to handle clock drift
 *   - otpauth:// URI generation for QR codes
 *
 * Compatible with Google Authenticator, Authy, and any RFC 6238 app.
 */
class MfaHelper {
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const DIGITS = 6;
    private const PERIOD = 30;
    private const ALGORITHM = 'sha1';

    public static function generateSecret(): string {
        $bytes = random_bytes(20);
        return self::base32Encode($bytes);
    }

    public static function generateCode(string $secret, ?int $timeSlice = null): string {
        $timeSlice = $timeSlice ?? intdiv(time(), self::PERIOD);
        $secret = self::base32Decode($secret);
        $timeBytes = pack('J', $timeSlice);
        $hash = hash_hmac(self::ALGORITHM, $timeBytes, $secret, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = (
            ((ord($hash[$offset + 0]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) <<  8) |
            ((ord($hash[$offset + 3]) & 0xFF))
        );
        $code = $binary % (10 ** self::DIGITS);
        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    public static function verifyCode(string $secret, string $code, int $window = 1): bool {
        $timeSlice = intdiv(time(), self::PERIOD);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::generateCode($secret, $timeSlice + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    public static function generateProvisioningUri(string $secret, string $email, string $issuer = 'Astral Cloud'): string {
        $label = rawurlencode($issuer . ':' . $email);
        return "otpauth://totp/{$label}?secret={$secret}&issuer=" . rawurlencode($issuer);
    }

    private static function base32Encode(string $data): string {
        $result = '';
        $binary = '';
        $length = strlen($data);

        for ($i = 0; $i < $length; $i++) {
            $binary .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
        }

        $chunks = str_split($binary, 5);
        foreach ($chunks as $chunk) {
            $chunk = str_pad($chunk, 5, '0');
            $result .= self::BASE32_ALPHABET[bindec($chunk)];
        }

        $padding = strlen($result) % 8;
        if ($padding > 0) {
            $result .= str_repeat('=', 8 - $padding);
        }

        return $result;
    }

    private static function base32Decode(string $data): string {
        $data = rtrim(strtoupper($data), '=');
        $binary = '';

        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $pos = strpos(self::BASE32_ALPHABET, $data[$i]);
            if ($pos === false) continue;
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $result = '';
        $chunks = str_split($binary, 8);
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 8) break;
            $result .= chr(bindec($chunk));
        }

        return $result;
    }
}
