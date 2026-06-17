<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\MfaHelper;
use App\Models\User;

class ProfileController extends Controller {
    public function index(): void {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        $user    = User::getProfile($_SESSION["user_id"]);
        $error   = $_GET["error"] ?? "";
        $success = $_GET["success"] ?? "";

        view("profile/index", [
            "user"    => $user,
            "error"   => $error,
            "success" => $success,
            "title"   => "My Profile | Astral Cloud",
        ]);
    }

    public function setupMfa(): void {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        if (!isset($_SESSION["_mfa_setup_secret"])) {
            header("Location: /profile");
            exit;
        }

        $user    = User::getProfile($_SESSION["user_id"]);
        $secret  = $_SESSION["_mfa_setup_secret"];
        $uri     = MfaHelper::generateProvisioningUri($secret, $user["email"]);
        $error   = "";

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            $code = trim($_POST["code"] ?? "");

            if (empty($code) || strlen($code) !== 6) {
                $error = "Please enter a valid 6-digit code.";
            } elseif (!MfaHelper::verifyCode($secret, $code)) {
                $error = "Invalid code. Make sure your authenticator app is synchronized.";
            } else {
                User::enableMfa($_SESSION["user_id"], $secret);
                unset($_SESSION["_mfa_setup_secret"]);

                AuditLog::log("profile.enable_mfa", "user", $_SESSION["user_id"],
                    "Enabled MFA for {$user["email"]}"
                );

                header("Location: /profile?success=" . urlencode("MFA enabled successfully!"));
                exit;
            }
        }

        view("auth/mfa-setup", [
            "secret" => $secret,
            "uri"    => $uri,
            "error"  => $error,
            "title"  => "Setup MFA | Astral Cloud",
        ]);
    }

    public function update(): void {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: /profile");
            exit;
        }

        verifyCsrfToken();

        $user   = User::getProfile($_SESSION["user_id"]);
        $action = $_POST["action"] ?? "";

        if ($action === "profile") {
            $name  = trim($_POST["name"] ?? "");
            $phone = trim($_POST["phone"] ?? "");

            if (empty($name)) {
                header("Location: /profile?error=" . urlencode("Name cannot be empty."));
                exit;
            }

            User::updateProfile($_SESSION["user_id"], $name, $phone ?: null);
            $_SESSION["user_name"] = $name;

            AuditLog::log("profile.update", "user", $_SESSION["user_id"],
                "Updated profile: {$name}"
            );

            header("Location: /profile?success=" . urlencode("Profile updated."));
            exit;
        }

        if ($action === "password") {
            $currentPassword  = $_POST["current_password"] ?? "";
            $newPassword      = $_POST["new_password"] ?? "";
            $confirmPassword  = $_POST["confirm_password"] ?? "";

            if (empty($currentPassword) || empty($newPassword)) {
                header("Location: /profile?error=" . urlencode("All password fields are required."));
                exit;
            }

            if (!password_verify($currentPassword, $user["password"])) {
                header("Location: /profile?error=" . urlencode("Current password is incorrect."));
                exit;
            }

            if (strlen($newPassword) < 6) {
                header("Location: /profile?error=" . urlencode("New password must be at least 6 characters."));
                exit;
            }

            if ($newPassword !== $confirmPassword) {
                header("Location: /profile?error=" . urlencode("New passwords do not match."));
                exit;
            }

            User::updatePassword($_SESSION["user_id"], $newPassword);

            AuditLog::log("profile.change_password", "user", $_SESSION["user_id"],
                "Changed password"
            );

            header("Location: /profile?success=" . urlencode("Password changed."));
            exit;
        }

        if ($action === "enable_mfa") {
            $secret = MfaHelper::generateSecret();
            $_SESSION["_mfa_setup_secret"] = $secret;

            header("Location: /mfa-setup");
            exit;
        }

        if ($action === "disable_mfa") {
            $code = trim($_POST["mfa_code"] ?? "");

            if (empty($code)) {
                header("Location: /profile?error=" . urlencode("Please enter your MFA code to disable."));
                exit;
            }

            $secret = User::getMfaSecret($_SESSION["user_id"]);
            if (!$secret || !MfaHelper::verifyCode($secret, $code)) {
                header("Location: /profile?error=" . urlencode("Invalid MFA code. Try again."));
                exit;
            }

            User::disableMfa($_SESSION["user_id"]);

            AuditLog::log("profile.disable_mfa", "user", $_SESSION["user_id"],
                "Disabled MFA"
            );

            header("Location: /profile?success=" . urlencode("MFA has been disabled."));
            exit;
        }

        header("Location: /profile");
        exit;
    }
}
