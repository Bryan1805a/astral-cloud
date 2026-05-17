<?php
    session_start();

    // Prevent redirect user to login page if they are already logged in
    if (isset($_SESSION["user_id"])) {
        if ($_SESSION["user_role"] === "admin" || $_SESSION["user_role"] === "staff") {
            header("Location: ../admin/index.php");
        }
        else {
            header("Location: ../index.php");
        }
        exit;
    }

    require_once "../config/db.php";

    $error = "";
    
    // Check request method
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if (empty($email) || empty($password)) {
            $error = "Please enter your email and password.";
        }
        else {
            try {
                // Find user based on email
                $stmt = $pdo->prepare("SELECT id, name, password, role, is_locked FROM users WHERE email = :email LIMIT 1");
                $stmt->execute(["email" => $email]);
                $user = $stmt->fetch();

                // Check if user exists and password
                if ($user && password_verify($password, $user["password"])) {
                    // Check if account has been locked
                    if ($user["is_locked"] == 1) {
                        $error = "Your account has been locked. Please contact the administrators.";
                    }
                    else {
                        // if logged in sucessfully
                        // save to Session
                        $_SESSION["user_id"] = $user["id"];
                        $_SESSION["user_name"] = $user["name"];
                        $_SESSION["user_role"] = $user["role"];

                        // Redirect based on role
                        if ($user["role"] === "admin" || $user["role"] === "staff") {
                            // Redirect to admin page
                            header("Location: ../admin/index.php");
                        }
                        else {
                            // Redirect to page for customer
                            header("Location: ../index.php");
                        }
                        exit;
                    }
                }
                else {
                    $error = "Incorrect email or password.";
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
    <title>Log in | Astral Cloud</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-light">
    <div class="container mt-5" style="max-width: 400px;">
        <h2 class="text-center mb-4 text-primary">Astral Cloud Login</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['registered']) && $_GET['registered'] == 'success'): ?>
            <div class="alert alert-success">Registration successful! Please log in.</div>
        <?php endif; ?>

        <div class="card bg-secondary text-light p-4 shadow">
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control bg-dark text-light border-secondary" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control bg-dark text-light border-secondary" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
                
                <div class="text-center mt-3">
                    <a href="register.php" class="text-info text-decoration-none">Don't have an account yet? Register now!</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>