<?php

class User {
    // Find user by Email
    public static function findByEmail(string $email): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, password, role, tier, is_locked, is_verified FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    // Finf user by ID
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, phone, role, tier FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // Check if email already exists
    public static function emailExists(string $email): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetch();
    }

    // Create a account and save to database
    public static function create(string $name, string $email, string $password, ?string $phone): void {
        $pdo = Database::getConnection();
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone) VALUES (:name, :email, :password, :phone)");
        $stmt->execute([
            'name'     => $name,
            'email'    => $email,
            'password' => $hashed,
            'phone'    => $phone,
        ]);
    }

    // Get customer list
    public static function getAllCustomers(): array {
        $pdo = Database::getConnection();
        $sql = "
            SELECT id, name, email, phone, tier, total_spent, is_locked, created_at 
            FROM users 
            WHERE role = 'user' 
            ORDER BY created_at DESC
        ";
        
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Change user account status
    // Lock / Unlock account
    public static function toggleLock(int $id): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET is_locked = NOT is_locked WHERE id = ? AND role = 'user'");
        $stmt->execute([$id]);
    }

    // Create account with token
    // Save token and set is_verified = 0
    public static function createWithToken(array $data, string $token): void {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, verification_token, is_verified) 
            VALUES (:name, :email, :password, :token, 0)
        ");
        
        $stmt->execute([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => password_hash($data["password"], PASSWORD_DEFAULT),
            "token" => $token
        ]);
    }

    // Find user by token
    public static function findByToken(string $token): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_token = ? AND is_verified = 0");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Activate account
    public static function verifyEmail(int $userId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $stmt->execute([$userId]);
    }

    // Auto-verify account by email (for dev environments without SMTP)
    public static function verifyByEmail(string $email): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE email = ?");
        $stmt->execute([$email]);
    }

    // Update profile (name, phone)
    public static function updateProfile(int $id, string $name, ?string $phone): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :id");
        $stmt->execute(['name' => $name, 'phone' => $phone, 'id' => $id]);
    }

    // Change password
    public static function updatePassword(int $id, string $newPassword): void {
        $pdo = Database::getConnection();
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute(['password' => $hashed, 'id' => $id]);
    }

    // Get full profile for profile page
    public static function getProfile(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, password, phone, role, tier, total_spent, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // Set password reset token
    public static function setResetToken(string $email, string $token): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET reset_token = :token, reset_token_expires = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE email = :email");
        $stmt->execute(['token' => $token, 'email' => $email]);
        return $stmt->rowCount() > 0;
    }

    // Find user by valid reset token
    public static function findByResetToken(string $token): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE reset_token = :token AND reset_token_expires > NOW() LIMIT 1");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    // Clear reset token after use
    public static function clearResetToken(int $id): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET reset_token = NULL, reset_token_expires = NULL WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    // Force-set new password (for reset flow, no current password needed)
    public static function forceUpdatePassword(int $id, string $newPassword): void {
        $pdo = Database::getConnection();
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute(['password' => $hashed, 'id' => $id]);
    }
}
