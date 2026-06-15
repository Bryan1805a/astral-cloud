<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    // LOGIN
    public function login(): void {
        if (isset($_SESSION["user_id"])) {
            $this->redirectBasedOnRole();
        }

        $error   = "";
        $success = "";
        if (isset($_GET["msg"])) {
            if ($_GET["msg"] === "check_email") $success = "Please check your email to activate your account.";
            if ($_GET["msg"] === "verified_success") $success = "Account verified successfully! You can now log in.";
            if ($_GET["msg"] === "otp_sent") $success = "A verification code has been sent to your email.";
            if ($_GET["msg"] === "invalid_token") $error = "The verification link is invalid or has expired.";
            if ($_GET["msg"] === "password_reset") $success = "Password reset successfully! You can now log in with your new password.";
            if ($_GET["msg"] === "mfa_timeout") $error = "MFA session expired. Please log in again.";
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();

            $rateLimitError = RateLimiter::check("login");
            if ($rateLimitError) {
                view("auth/login", [
                    "error"   => $rateLimitError,
                    "success" => "",
                    "title"   => "Log in | Astral Cloud",
                ]);
                return;
            }

            $email    = trim($_POST["email"] ?? "");
            $password = $_POST["password"] ?? "";

            if (empty($email) || empty($password)) {
                $error = "Please enter your email and password.";
            } else {
                $user = User::findByEmail($email);

                if ($user && password_verify($password, $user["password"])) {
                    if ($user["is_locked"] == 1) {
                        $error = "Your account has been locked. Please contact the administrators.";
                    }
                    elseif (isset($user["is_verified"]) && $user["is_verified"] == 0) {
                        $error = "Your account has not been verified. Please check your email inbox.";
                    }
                    elseif (!empty($user["mfa_secret"]) && $user["mfa_enabled"] == 1) {
                        RateLimiter::clear("login");
                        $_SESSION["_mfa_pending"] = [
                            "user_id"   => $user["id"],
                            "user_name" => $user["name"],
                            "user_role" => $user["role"],
                            "user_tier" => $user["tier"],
                            "secret"    => $user["mfa_secret"],
                            "expires"   => time() + 300,
                        ];
                        header("Location: /mfa-verify");
                        exit;
                    }
                    else {
                        RateLimiter::clear("login");
                        $_SESSION["user_id"]    = $user["id"];
                        $_SESSION["user_name"]  = $user["name"];
                        $_SESSION["user_role"]  = $user["role"];
                        $_SESSION["user_tier"]  = $user["tier"];

                        AuditLog::log("auth.login", "user", $user["id"],
                            "User logged in: {$user["name"]} ({$email})"
                        );

                        $this->redirectBasedOnRole();
                    }
                }
                else {
                    $error = "Incorrect email or password.";
                    RateLimiter::record("login");
                }
            }
        }

        view("auth/login", [
            "error"   => $error,
            "success" => $success,
            "title"   => "Log in | Astral Cloud",
        ]);
    }

    // REGISTRATION
    public function register(): void {
        $error   = "";
        $success = "";

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();

            $rateLimitError = RateLimiter::check("register");
            if ($rateLimitError) {
                view("auth/register", [
                    "error"   => $rateLimitError,
                    "success" => "",
                    "title"   => "Registration | Astral Cloud",
                ]);
                return;
            }

            $name             = trim($_POST["name"] ?? "");
            $email            = trim($_POST["email"] ?? "");
            $phone            = trim($_POST["phone"] ?? "");
            $password         = $_POST["password"] ?? "";
            $confirm_password = $_POST["confirm_password"] ?? "";

            if (empty($name) || empty($email) || empty($password)) {
                $error = "Please fill in all required fields (Name, Email, Password).";
            }
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            }
            elseif ($password !== $confirm_password) {
                $error = "The confirm password doesn't match.";
            }
            elseif (strlen($password) < 6) {
                $error = "The password must have at least 6 characters.";
            }
            else {
                try {
                    if (User::emailExists($email)) {
                        $error = "This email address is already registered. Please use a different email address.";
                    }
                    else {
                        $token = bin2hex(random_bytes(32));

                        User::createWithToken([
                            "name"     => $name,
                            "email"    => $email,
                            "password" => $password,
                            "phone"    => $phone ?: null,
                        ], $token);

                        RateLimiter::clear("register");

                        AuditLog::log("auth.register", "user", (int) Database::getConnection()->lastInsertId(),
                            "New account registered: {$name} ({$email})"
                        );

                        $otp = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);

                        $_SESSION["otp_verification"] = [
                            "email"      => $email,
                            "name"       => $name,
                            "code"       => $otp,
                            "expires_at" => time() + 300,
                        ];

                        header("Location: /verify-otp");

                        if (ob_get_level()) ob_end_flush();
                        flush();

                        $smtpUser = getenv("SMTP_USER") ?: "";

                        if (!empty($smtpUser)) {
                            $mail = new PHPMailer(true);

                            try {
                                $mail->isSMTP();
                                $mail->Host       = getenv("SMTP_HOST") ?: 'smtp.gmail.com';
                                $mail->SMTPAuth   = true;
                                $mail->Username   = $smtpUser;
                                $mail->Password   = getenv("SMTP_PASS") ?: '';
                                $mail->SMTPSecure = getenv("SMTP_ENCRYPTION") ?: PHPMailer::ENCRYPTION_SMTPS;
                                $mail->Port       = (int)(getenv("SMTP_PORT") ?: 465);

                                $fromEmail = getenv("SMTP_FROM_EMAIL") ?: 'noreply@astralcloud.com';
                                $fromName  = getenv("SMTP_FROM_NAME") ?: 'Astral Cloud';
                                $mail->setFrom($fromEmail, $fromName);
                                $mail->addAddress($email, $name);

                                $mail->isHTML(true);
                                $mail->Subject = 'Your Astral Cloud Verification Code';
                                $mail->Body    = "
                                    <h3>Hello {$name},</h3>
                                    <p>Thank you for registering at Astral Cloud. Use the following code to verify your account:</p>
                                    <div style='text-align:center; margin:30px 0;'>
                                        <span style='display:inline-block; padding:15px 30px; background-color:#38bdf8; color:#ffffff; font-size:32px; font-weight:bold; letter-spacing:8px; border-radius:8px;'>{$otp}</span>
                                    </div>
                                    <p>This code will expire in <strong>5 minutes</strong>.</p>
                                    <p>If you did not create an account, please ignore this email.</p>
                                    <br>
                                    <p>Best regards,<br>Astral Cloud Team</p>
                                ";

                                $mail->SMTPOptions = [
                                    "ssl" => [
                                        "verify_peer"       => false,
                                        "verify_peer_name"  => false,
                                        "allow_self_signed" => true,
                                    ],
                                ];

                                $mail->send();
                            } catch (Exception $e) {
                                error_log("AuthController registration email (port 465) failed: " . $e->getMessage());

                                $mail2 = new PHPMailer(true);
                                try {
                                    $mail2->isSMTP();
                                    $mail2->Host       = getenv("SMTP_HOST") ?: 'smtp.gmail.com';
                                    $mail2->SMTPAuth   = true;
                                    $mail2->Username   = $smtpUser;
                                    $mail2->Password   = getenv("SMTP_PASS") ?: '';
                                    $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                    $mail2->Port       = 587;
                                    $mail2->SMTPOptions = [
                                        "ssl" => [
                                            "verify_peer"       => false,
                                            "verify_peer_name"  => false,
                                            "allow_self_signed" => true,
                                        ],
                                    ];

                                    $mail2->setFrom($fromEmail, $fromName);
                                    $mail2->addAddress($email, $name);
                                    $mail2->isHTML(true);
                                    $mail2->Subject = 'Your Astral Cloud Verification Code';
                                    $mail2->Body    = $mail->Body;

                                    $mail2->send();
                                } catch (Exception $e2) {
                                    error_log("AuthController registration email (port 587) also failed: " . $e2->getMessage());
                                }
                            }
                        }

                        exit;
                    }
                } catch (PDOException $e) {
                    $error = "A system error has occurred. Please try again later.";
                }
            }
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && $error) {
            RateLimiter::record("register");
        }

        view("auth/register", [
            "error"   => $error,
            "success" => $success,
            "title"   => "Registration | Astral Cloud",
        ]);
    }

    // MFA Verification (during login)
    public function mfaVerify(): void {
        if (!isset($_SESSION["_mfa_pending"])) {
            header("Location: /login");
            exit;
        }

        $pending = $_SESSION["_mfa_pending"];

        if (time() > $pending["expires"]) {
            unset($_SESSION["_mfa_pending"]);
            header("Location: /login?msg=mfa_timeout");
            exit;
        }

        $error = "";

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            $code = trim($_POST["code"] ?? "");

            if (empty($code) || strlen($code) !== 6) {
                $error = "Please enter a valid 6-digit code.";
            } elseif (!MfaHelper::verifyCode($pending["secret"], $code)) {
                $error = "Invalid verification code. Please try again.";
            } else {
                $_SESSION["user_id"]   = $pending["user_id"];
                $_SESSION["user_name"] = $pending["user_name"];
                $_SESSION["user_role"] = $pending["user_role"];
                $_SESSION["user_tier"] = $pending["user_tier"];
                unset($_SESSION["_mfa_pending"]);

                AuditLog::log("auth.login", "user", $pending["user_id"],
                    "User logged in (MFA verified): {$pending["user_name"]}"
                );

                if ($_SESSION["user_role"] === "admin" || $_SESSION["user_role"] === "staff") {
                    header("Location: /admin");
                } else {
                    header("Location: /");
                }
                exit;
            }
        }

        view("auth/mfa-verify", [
            "error" => $error,
            "title" => "MFA Verification | Astral Cloud",
        ]);
    }

    // Show OTP verification form
    public function showOtpForm(): void {
        if (!isset($_SESSION["otp_verification"])) {
            header("Location: /register");
            exit;
        }

        $otpData = $_SESSION["otp_verification"];
        $email   = $otpData["email"];
        $parts   = explode("@", $email);
        $masked  = substr($parts[0], 0, 2) . str_repeat("*", max(0, strlen($parts[0]) - 2)) . "@" . $parts[1];
        $error   = "";
        $otpDev  = "";

        if (empty(getenv("SMTP_USER") ?: "")) {
            $otpDev = $otpData["code"];
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            $inputOtp = trim($_POST["otp"] ?? "");

            if (time() > $otpData["expires_at"]) {
                $error = "The verification code has expired. Please register again.";
                unset($_SESSION["otp_verification"]);
            }
            elseif ($inputOtp !== $otpData["code"]) {
                $error = "Invalid verification code. Please try again.";
            }
            else {
                User::verifyByEmail($email);
                unset($_SESSION["otp_verification"]);

                $user = User::findByEmail($email);
                AuditLog::log("auth.verify_otp", "user", $user ? $user["id"] : null,
                    "Account verified via OTP: {$email}"
                );

                header("Location: /login?msg=verified_success");
                exit;
            }
        }

        view("auth/otp", [
            "masked_email" => $masked,
            "error"        => $error,
            "otp_dev"      => $otpDev,
            "title"        => "Verify Account | Astral Cloud",
        ]);
    }

    // Verify email via token (kept for backward compatibility)
    public function verify(): void {
        $token = $_GET["token"] ?? "";

        if (empty($token)) {
            header("Location: /login");
            exit;
        }

        $user = User::findByToken($token);

        if ($user) {
            User::verifyEmail($user["id"]);
            header("Location: /login?msg=verified_success");
        } else {
            header("Location: /login?msg=invalid_token");
        }
        exit;
    }

    // Logout
    public function logout(): void {
        $userId = $_SESSION["user_id"] ?? null;
        $userName = $_SESSION["user_name"] ?? "Unknown";
        if ($userId) {
            AuditLog::log("auth.logout", "user", $userId,
                "User logged out: {$userName}"
            );
        }
        session_destroy();
        header("Location: /");
        exit;
    }

    // Redirect user to unique page base on role
    private function redirectBasedOnRole(): void {
        if ($_SESSION["user_role"] === "admin" || $_SESSION["user_role"] === "staff") {
            header("Location: /admin");
        } else {
            header("Location: /");
        }
        exit;
    }
}
