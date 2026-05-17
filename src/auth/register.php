<?php
    session_start();

    // Import database connection file
    require_once("../config/db.php");

    // Notification storing variables
    $error = "";
    $success = "";

    // Check if the request is POST method
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Clean data
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $password = $_POST["password"] ?? "";
        $confirm_password = $_POST["confirm_password"] ?? "";
    

        // Validiate
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
                // Check if email exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
                $stmt->execute(["email" => $email]);

                if ($stmt->rowCount() > 0) {
                    $error = "This email address is already registered. Please use a different email address.";
                }
                else {
                    // Hash password using Bcrypt
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Add new user to database
                    $insert_stmt = $pdo->prepare("
                        INSERT INTO users (name, email, password, phone) 
                        VALUES (:name, :email, :password, :phone)
                        ");
                    
                    $insert_stmt->execute([
                        "name" => $name,
                        "email" => $email,
                        "password" => $hashed_password,
                        "phone" => empty($phone) ? null : $phone
                    ]);

                    $success = "Registration successful! You can now proceed to the login page.";
                    header("Location: ../admin/index.php");

                    // =======================================
                    // ADD FRONTEND HERE
                    // ==============================================
                }
            } catch (PDOException $e) {
                $error = "A system error has occurred. Please try again later.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration || Astral Cloud</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="bg-dark text-light"> <div class="container mt-5" style="max-width: 500px;">
        <h2 class="text-center mb-4 text-primary">Create an Astral Cloud account</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card bg-secondary text-light p-4 shadow">
            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Họ và Tên *</label>
                    <input type="text" name="name" class="form-control bg-dark text-light border-secondary" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control bg-dark text-light border-secondary" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone number</label>
                    <input type="text" name="phone" class="form-control bg-dark text-light border-secondary" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control bg-dark text-light border-secondary" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="confirm_password" class="form-control bg-dark text-light border-secondary" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
        </div>
    </div>
</body>

</html>