<?php

class User {
    // Find user by Email
    public static function findByEmail(string $email): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, password, role, tier, is_locked FROM users WHERE email = :email LIMIT 1");
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
}
