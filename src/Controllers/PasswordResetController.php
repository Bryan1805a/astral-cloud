<?php

class PasswordResetController {
    public function forgot(): void {
        $error   = "";
        $success = "";

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            $email = trim($_POST["email"] ?? "");

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address.";
            } else {
                $user = User::findByEmail($email);

                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    User::setResetToken($email, $token);

                    $appUrl   = rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/');
                    $resetUrl = "{$appUrl}/reset-password?token={$token}";

                    if (MailHelper::isConfigured()) {
                        MailHelper::sendPasswordReset($email, $user['name'], $resetUrl);
                    }

                    AuditLog::log("auth.forgot_password", "user", $user['id'],
                        "Password reset requested for {$email}"
                    );
                }

                $success = "If this email is registered, a reset link has been sent.";
            }
        }

        view("auth/forgot-password", [
            "error"   => $error,
            "success" => $success,
            "title"   => "Forgot Password | Astral Cloud",
        ]);
    }

    public function reset(): void {
        $token = $_GET["token"] ?? "";
        $error   = "";
        $success = "";

        if (empty($token)) {
            header("Location: /forgot-password");
            exit;
        }

        $user = User::findByResetToken($token);

        if (!$user) {
            $error = "The reset link is invalid or has expired.";
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && $user) {
            verifyCsrfToken();
            $password        = $_POST["password"] ?? "";
            $confirmPassword = $_POST["confirm_password"] ?? "";

            if (strlen($password) < 6) {
                $error = "Password must be at least 6 characters.";
            } elseif ($password !== $confirmPassword) {
                $error = "Passwords do not match.";
            } else {
                User::forceUpdatePassword($user['id'], $password);
                User::clearResetToken($user['id']);

                AuditLog::log("auth.reset_password", "user", $user['id'],
                    "Password reset completed for {$user['email']}"
                );

                header("Location: /login?msg=password_reset");
                exit;
            }
        }

        view("auth/reset-password", [
            "error"   => $error,
            "success" => $success,
            "token"   => $token,
            "title"   => "Reset Password | Astral Cloud",
        ]);
    }
}
