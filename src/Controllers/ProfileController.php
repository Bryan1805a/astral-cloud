<?php

class ProfileController {
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

        header("Location: /profile");
        exit;
    }
}
