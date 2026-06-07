<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    // LOGIN
    public function login(): void {
        // Redirect user to unique page base on role
        if (isset($_SESSION["user_id"])) {
            $this->redirectBasedOnRole();
        }

        $error = "";
        
        // Check notification
        $success = "";
        if (isset($_GET["msg"])) {
            if ($_GET["msg"] === "check_email") $success = "Please check your email to activate your account.";
            if ($_GET["msg"] === "verified_success") $success = "Account verified successfully! You can now log in.";
            if ($_GET["msg"] === "invalid_token") $error = "The verification link is invalid or has expired.";
        }

        // Check method post
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            $email    = trim($_POST["email"] ?? "");
            $password = $_POST["password"] ?? "";

            if (empty($email) || empty($password)) {
                $error = "Please enter your email and password.";
            } else {
                $user = User::findByEmail($email);

                // Check email and verify password from database
                if ($user && password_verify($password, $user["password"])) {
                    if ($user["is_locked"] == 1) {
                        $error = "Your account has been locked. Please contact the administrators."; // The account locked by administrator or BOT
                    }
                    elseif (isset($user["is_verified"]) && $user["is_verified"] == 0) {
                        $error = "Your account has not been verified. Please check your email inbox."; // Email verify
                    }
                    else {
                        $_SESSION["user_id"]    = $user["id"];
                        $_SESSION["user_name"]  = $user["name"];
                        $_SESSION["user_role"]  = $user["role"];
                        $_SESSION["user_tier"]  = $user["tier"];

                        $this->redirectBasedOnRole();
                    }
                }
                else {
                    $error = "Incorrect email or password.";
                }
            }
        }

        view("auth/login", [
            "error"  => $error,
            "success" => $success,
            "title"  => "Log in | Astral Cloud",
        ]);
    }

    // REGISTRATION
    public function register(): void {
        $error   = "";
        $success = "";

        // Check the request method
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            $name             = trim($_POST["name"] ?? "");
            $email            = trim($_POST["email"] ?? "");
            $phone            = trim($_POST["phone"] ?? "");
            $password         = $_POST["password"] ?? "";
            $confirm_password = $_POST["confirm_password"] ?? "";

            // Check valid registing data
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
                    // Check if email exists in the database
                    if (User::emailExists($email)) {
                        $error = "This email address is already registered. Please use a different email address.";
                    }
                    // Call create function with token function and save to database
                    else {
                        $token = bin2hex(random_bytes(32));

                        User::createWithToken([
                            "name"     => $name,
                            "email"    => $email,
                            "password" => $password,
                            "phone"    => $phone ?: null,
                        ], $token);

                        $appUrl = rtrim(getenv("APP_URL") ?: "http://localhost:8080", "/");
                        $verifyLink = $appUrl . "/verify?token=" . $token;
                        $mail = new PHPMailer(true);

                        try {
                            $mail->isSMTP();
                            $mail->Host       = getenv("SMTP_HOST") ?: 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = getenv("SMTP_USER") ?: '';
                            $mail->Password   = getenv("SMTP_PASS") ?: '';
                            $mail->SMTPSecure = getenv("SMTP_ENCRYPTION") ?: PHPMailer::ENCRYPTION_SMTPS;
                            $mail->Port       = (int)(getenv("SMTP_PORT") ?: 465);

                            $fromEmail = getenv("SMTP_FROM_EMAIL") ?: 'noreply@astralcloud.com';
                            $fromName  = getenv("SMTP_FROM_NAME") ?: 'Astral Cloud';
                            $mail->setFrom($fromEmail, $fromName);
                            $mail->addAddress($email, $name);

                            $mail->isHTML(true);
                            $mail->Subject = 'Verify your Astral Cloud Account';
                            $mail->Body    = "
                                <h3>Hello {$name},</h3>
                                <p>Thank you for registering at Astral Cloud. Please click the button below to verify your email address:</p>
                                <a href='{$verifyLink}' style='display:inline-block; padding:10px 20px; background-color:#38bdf8; color:#ffffff; text-decoration:none; border-radius:5px; font-weight:bold;'>Verify Account</a>
                                <br><br>
                                <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                                <p>{$verifyLink}</p>
                                <br>
                                <p>Best regards,<br>Astral Cloud Team</p>
                            ";

                            $mail->send();
                            header("Location: /login?msg=check_email");
                            exit;
                        } catch (Exception $e) {
                            $error = "Failed to send verification email. Please try again.";
                        }
                    }
                } catch (PDOException $e) {
                    $error = "A system error has occurred. Please try again later.";
                }
            }
        }

        view("auth/register", [
            "error"   => $error,
            "success" => $success,
            "title"   => "Registration | Astral Cloud",
        ]);
    }

    // Verify email via token
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
    // just delete session
    public function logout(): void {
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