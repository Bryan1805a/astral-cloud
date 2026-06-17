<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\RateLimiter;
use App\Models\User;

class AuthController extends Controller
{
    public function login(): void
    {
        if ($this->isLoggedIn()) {
            $role = $this->user('user_role', 'user');
            $this->redirect($role === 'admin' || $role === 'staff' ? '/admin' : '/');
        }

        $error   = '';
        $success = '';
        $msg     = $this->request->get('msg', '');
        if ($msg === 'check_email')      $success = 'Please check your email to activate your account.';
        elseif ($msg === 'verified_success') $success = 'Account verified successfully! You can now log in.';
        elseif ($msg === 'otp_sent')      $success = 'A verification code has been sent to your email.';
        elseif ($msg === 'invalid_token') $error   = 'The verification link is invalid or has expired.';
        elseif ($msg === 'password_reset')$success = 'Password reset successfully! You can now log in with your new password.';
        elseif ($msg === 'mfa_timeout')   $error   = 'MFA session expired. Please log in again.';

        if ($this->request->isPost()) {
            $this->verifyCsrf();

            $rateLimitError = RateLimiter::check('login');
            if ($rateLimitError) {
                $this->render('auth/login', [
                    'error' => $rateLimitError, 'success' => '',
                    'title' => 'Log in | Astral Cloud',
                ]);
                return;
            }

            $email    = trim($this->request->post('email', ''));
            $password = $this->request->post('password', '');

            if (empty($email) || empty($password)) {
                $error = 'Please enter your email and password.';
            } else {
                $user = User::findByEmail($email);
                if ($user && password_verify($password, $user['password'])) {
                    if ($user['is_locked'] == 1) {
                        $error = 'Your account has been locked.';
                    } elseif (($user['is_verified'] ?? 1) == 0) {
                        $error = 'Your account has not been verified.';
                    } elseif (!empty($user['mfa_secret']) && ($user['mfa_enabled'] ?? 0) == 1) {
                        RateLimiter::clear('login');
                        $_SESSION['_mfa_pending'] = [
                            'user_id' => $user['id'], 'user_name' => $user['name'],
                            'user_role' => $user['role'], 'user_tier' => $user['tier'],
                            'secret' => $user['mfa_secret'], 'expires' => time() + 300,
                        ];
                        $this->redirect('/mfa-verify');
                    } else {
                        RateLimiter::clear('login');
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_role'] = $user['role'];
                        $_SESSION['user_tier'] = $user['tier'];
                        AuditLog::log('auth.login', 'user', $user['id'],
                            "User logged in: {$user['name']} ({$email})");
                        $role = $user['role'];
                        $this->redirect($role === 'admin' || $role === 'staff' ? '/admin' : '/');
                    }
                } else {
                    $error = 'Incorrect email or password.';
                    RateLimiter::record('login');
                }
            }
        }

        $this->render('auth/login', [
            'error' => $error, 'success' => $success,
            'title' => 'Log in | Astral Cloud',
        ]);
    }

    public function register(): void
    {
        $error   = '';
        $success = '';

        if ($this->request->isPost()) {
            $this->verifyCsrf();

            $rateLimitError = RateLimiter::check('register');
            if ($rateLimitError) {
                $this->render('auth/register', [
                    'error'   => $rateLimitError, 'success' => '',
                    'title'   => 'Registration | Astral Cloud',
                ]);
                return;
            }

            $name            = trim($this->request->post('name', ''));
            $email           = trim($this->request->post('email', ''));
            $phone           = trim($this->request->post('phone', ''));
            $password        = $this->request->post('password', '');
            $confirmPassword = $this->request->post('confirm_password', '');

            if (empty($name) || empty($email) || empty($password)) {
                $error = 'Please fill in all required fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email format.';
            } elseif ($password !== $confirmPassword) {
                $error = "The confirm password doesn't match.";
            } elseif (strlen($password) < 6) {
                $error = 'The password must have at least 6 characters.';
            } else {
                try {
                    if (User::emailExists($email)) {
                        $error = 'This email is already registered.';
                    } else {
                        $token = bin2hex(random_bytes(32));
                        User::createWithToken([
                            'name'     => $name,
                            'email'    => $email,
                            'password' => $password,
                            'phone'    => $phone ?: null,
                        ], $token);

                        RateLimiter::clear('register');

                        $pdo = \App\Core\Database::getConnection();
                        AuditLog::log('auth.register', 'user', (int) $pdo->lastInsertId(),
                            "New account registered: {$name} ({$email})"
                        );

                        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                        $_SESSION['otp_verification'] = [
                            'email'      => $email,
                            'name'       => $name,
                            'code'       => $otp,
                            'expires_at' => time() + 300,
                        ];

                        $emailBody = "
                            <h3>Hello {$name},</h3>
                            <p>Thank you for registering at Astral Cloud. Use this code to verify:</p>
                            <div style='text-align:center;margin:30px 0;'>
                                <span style='padding:15px 30px;background:#38bdf8;color:#fff;font-size:32px;font-weight:bold;letter-spacing:8px;border-radius:8px;'>{$otp}</span>
                            </div>
                            <p>This code expires in 5 minutes.</p>";

                        if (\App\Models\MailHelper::isConfigured()) {
                            \App\Models\MailHelper::send($email, $name, 'Your Astral Cloud Verification Code', $emailBody);
                        }

                        $this->redirect('/verify-otp');
                    }
                } catch (\PDOException $e) {
                    $error = 'A system error occurred. Please try again later.';
                }
            }
        }

        if ($this->request->isPost() && $error) {
            RateLimiter::record('register');
        }

        $this->render('auth/register', [
            'error'   => $error, 'success' => $success,
            'title'   => 'Registration | Astral Cloud',
        ]);
    }

    public function mfaVerify(): void
    {
        if (!isset($_SESSION['_mfa_pending'])) {
            $this->redirect('/login');
        }

        $pending = $_SESSION['_mfa_pending'];
        if (time() > $pending['expires']) {
            unset($_SESSION['_mfa_pending']);
            $this->redirect('/login?msg=mfa_timeout');
        }

        $error = '';
        if ($this->request->isPost()) {
            $this->verifyCsrf();
            $code = trim($this->request->post('code', ''));

            if (empty($code) || strlen($code) !== 6) {
                $error = 'Please enter a valid 6-digit code.';
            } elseif (!\App\Models\MfaHelper::verifyCode($pending['secret'], $code)) {
                $error = 'Invalid verification code. Please try again.';
            } else {
                $_SESSION['user_id']   = $pending['user_id'];
                $_SESSION['user_name'] = $pending['user_name'];
                $_SESSION['user_role'] = $pending['user_role'];
                $_SESSION['user_tier'] = $pending['user_tier'];
                unset($_SESSION['_mfa_pending']);

                AuditLog::log('auth.login', 'user', $pending['user_id'],
                    "User logged in (MFA verified): {$pending['user_name']}"
                );

                if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff') {
                    $this->redirect('/admin');
                } else {
                    $this->redirect('/');
                }
            }
        }

        $this->render('auth/mfa-verify', [
            'error' => $error,
            'title' => 'MFA Verification | Astral Cloud',
        ]);
    }

    public function showOtpForm(): void
    {
        if (!isset($_SESSION['otp_verification'])) {
            $this->redirect('/register');
        }

        $otpData = $_SESSION['otp_verification'];
        $email   = $otpData['email'];
        $parts   = explode('@', $email);
        $masked  = substr($parts[0], 0, 2) . str_repeat('*', max(0, strlen($parts[0]) - 2)) . '@' . $parts[1];
        $error   = '';
        $otpDev  = '';

        if (empty(getenv('SMTP_USER') ?: '')) {
            $otpDev = $otpData['code'];
        }

        if ($this->request->isPost()) {
            $this->verifyCsrf();
            $inputOtp = trim($this->request->post('otp', ''));

            if (time() > $otpData['expires_at']) {
                $error = 'The verification code has expired. Please register again.';
                unset($_SESSION['otp_verification']);
            } elseif ($inputOtp !== $otpData['code']) {
                $error = 'Invalid verification code. Please try again.';
            } else {
                User::verifyByEmail($email);
                unset($_SESSION['otp_verification']);

                $user = User::findByEmail($email);
                AuditLog::log('auth.verify_otp', 'user', $user ? $user['id'] : null,
                    "Account verified via OTP: {$email}"
                );

                $this->redirect('/login?msg=verified_success');
            }
        }

        $this->render('auth/otp', [
            'masked_email' => $masked, 'error' => $error, 'otp_dev' => $otpDev,
            'title'        => 'Verify Account | Astral Cloud',
        ]);
    }

    public function verify(): void
    {
        $token = $this->request->get('token', '');
        if (empty($token)) { $this->redirect('/login'); }

        $user = User::findByToken($token);
        if ($user) {
            User::verifyEmail($user['id']);
            $this->redirect('/login?msg=verified_success');
        } else {
            $this->redirect('/login?msg=invalid_token');
        }
    }

    public function logout(): void
    {
        $userId   = $this->userId();
        $userName = $this->user('user_name', 'Unknown');
        if ($userId) {
            AuditLog::log('auth.logout', 'user', $userId,
                "User logged out: {$userName}"
            );
        }
        session_destroy();
        $this->redirect('/');
    }

    private function redirectBasedOnRole(): void
    {
        $role = $this->user('user_role', 'user');
        $this->redirect($role === 'admin' || $role === 'staff' ? '/admin' : '/');
    }
}
