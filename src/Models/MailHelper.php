<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper {
    private static function createMailer(): ?PHPMailer {
        $smtpUser = getenv("SMTP_USER") ?: "";
        if (empty($smtpUser)) return null;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = getenv("SMTP_HOST") ?: 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = getenv("SMTP_PASS") ?: '';
            $mail->SMTPSecure = getenv("SMTP_ENCRYPTION") ?: PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = (int)(getenv("SMTP_PORT") ?: 465);
            $mail->CharSet    = 'UTF-8';

            $fromEmail = getenv("SMTP_FROM_EMAIL") ?: 'noreply@astralcloud.com';
            $fromName  = getenv("SMTP_FROM_NAME") ?: 'Astral Cloud';
            $mail->setFrom($fromEmail, $fromName);

            $mail->SMTPOptions = [
                "ssl" => [
                    "verify_peer"       => false,
                    "verify_peer_name"  => false,
                    "allow_self_signed" => true,
                ],
            ];

            return $mail;
        } catch (Exception $e) {
            error_log("MailHelper: failed to create mailer: " . $e->getMessage());
            return null;
        }
    }

    public static function send(string $toEmail, string $toName, string $subject, string $bodyHtml): bool {
        $mail = self::createMailer();
        if (!$mail) return false;

        try {
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $bodyHtml;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("MailHelper: send failed to {$toEmail}: " . $e->getMessage());

            // Retry with STARTTLS on port 587
            try {
                $mail2 = self::createMailer();
                if (!$mail2) return false;
                $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail2->Port       = 587;
                $mail2->addAddress($toEmail, $toName);
                $mail2->isHTML(true);
                $mail2->Subject = $subject;
                $mail2->Body    = $bodyHtml;
                $mail2->send();
                return true;
            } catch (Exception $e2) {
                error_log("MailHelper: retry also failed: " . $e2->getMessage());
                return false;
            }
        }
    }

    public static function sendOrderConfirmation(string $email, string $name, int $orderId, float $total, array $items): void {
        $itemsHtml = '';
        foreach ($items as $item) {
            $itemsHtml .= "
                <tr>
                    <td style='padding:10px 14px;border-bottom:1px solid #1e293b;'>{$item['product_name']}</td>
                    <td style='padding:10px 14px;border-bottom:1px solid #1e293b;'>{$item['quantity']}x</td>
                    <td style='padding:10px 14px;border-bottom:1px solid #1e293b;text-align:right;'>" . number_format($item['unit_price'], 0, ',', '.') . " VND</td>
                </tr>";
        }

        $body = "
            <div style='max-width:600px;margin:0 auto;font-family:Arial,sans-serif;background:#0f172a;color:#e2e8f0;border-radius:16px;overflow:hidden;'>
                <div style='background:linear-gradient(135deg,#1e293b,#0f172a);padding:32px;text-align:center;'>
                    <h1 style='color:#38bdf8;margin:0;font-size:24px;'>Order Confirmed!</h1>
                    <p style='color:#94a3b8;margin:8px 0 0;'>Thank you, {$name}!</p>
                </div>
                <div style='padding:24px 32px;'>
                    <p style='font-size:15px;line-height:1.6;'>
                        Your order <strong style='color:#38bdf8;'>#{$orderId}</strong> has been placed and is pending payment.
                    </p>
                    <table style='width:100%;border-collapse:collapse;margin:20px 0;'>
                        <thead>
                            <tr style='color:#94a3b8;font-size:13px;text-transform:uppercase;letter-spacing:1px;'>
                                <th style='padding:10px 14px;text-align:left;border-bottom:2px solid #1e293b;'>Product</th>
                                <th style='padding:10px 14px;text-align:left;border-bottom:2px solid #1e293b;'>Qty</th>
                                <th style='padding:10px 14px;text-align:right;border-bottom:2px solid #1e293b;'>Price</th>
                            </tr>
                        </thead>
                        <tbody>{$itemsHtml}</tbody>
                    </table>
                    <div style='text-align:right;padding:16px 0;border-top:2px solid #1e293b;margin-top:8px;'>
                        <span style='font-size:14px;color:#94a3b8;'>Total: </span>
                        <strong style='font-size:22px;color:#38bdf8;'>" . number_format($total, 0, ',', '.') . " VND</strong>
                    </div>
                    <div style='margin-top:24px;padding:16px;background:#1e293b;border-radius:12px;font-size:14px;color:#94a3b8;'>
                        Your VPS will be provisioned automatically after payment is confirmed.
                        Track your order at <a href='" . rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/') . "/orders' style='color:#38bdf8;'>My Orders</a>.
                    </div>
                </div>
                <div style='padding:20px 32px;background:#0c0c0c;text-align:center;font-size:12px;color:#64748b;'>
                    Astral Cloud — High-performance VPS for everyone
                </div>
            </div>";

        self::send($email, $name, "Order #{$orderId} Confirmed | Astral Cloud", $body);
    }

    public static function sendPasswordReset(string $email, string $name, string $resetLink): void {
        $body = "
            <div style='max-width:560px;margin:0 auto;font-family:Arial,sans-serif;background:#0f172a;color:#e2e8f0;border-radius:16px;overflow:hidden;'>
                <div style='background:linear-gradient(135deg,#1e293b,#0f172a);padding:32px;text-align:center;'>
                    <h1 style='color:#38bdf8;margin:0;font-size:22px;'>Reset Your Password</h1>
                </div>
                <div style='padding:28px 32px;'>
                    <p style='font-size:15px;line-height:1.6;'>Hello {$name},</p>
                    <p style='font-size:15px;line-height:1.6;color:#94a3b8;'>
                        We received a request to reset your Astral Cloud account password.
                        Click the button below to set a new password. This link expires in <strong>30 minutes</strong>.
                    </p>
                    <div style='text-align:center;margin:28px 0;'>
                        <a href='{$resetLink}' style='display:inline-block;padding:14px 36px;background:#38bdf8;color:#0f172a;text-decoration:none;border-radius:999px;font-weight:700;font-size:15px;'>Reset Password</a>
                    </div>
                    <p style='font-size:13px;color:#64748b;'>
                        If you didn't request this, you can safely ignore this email.
                    </p>
                </div>
                <div style='padding:18px 32px;background:#0c0c0c;text-align:center;font-size:12px;color:#64748b;'>
                    Astral Cloud
                </div>
            </div>";

        self::send($email, $name, "Password Reset | Astral Cloud", $body);
    }

    public static function isConfigured(): bool {
        return !empty(getenv("SMTP_USER") ?: "");
    }
}
