<?php

class AuthController {
    public function login(): void {
        // Redirect user to unique page base on role
        if (isset($_SESSION["user_id"])) {
            $this->redirectBasedOnRole();
        }

        $error = "";

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $email    = trim($_POST["email"] ?? "");
            $password = $_POST["password"] ?? "";

            if (empty($email) || empty($password)) {
                $error = "Please enter your email and password.";
            } else {
                $user = User::findByEmail($email);

                if ($user && password_verify($password, $user["password"])) {
                    if ($user["is_locked"] == 1) {
                        $error = "Your account has been locked. Please contact the administrators.";
                    } else {
                        $_SESSION["user_id"]    = $user["id"];
                        $_SESSION["user_name"]  = $user["name"];
                        $_SESSION["user_role"]  = $user["role"];
                        $_SESSION["user_tier"]  = $user["tier"];

                        $this->redirectBasedOnRole();
                    }
                } else {
                    $error = "Incorrect email or password.";
                }
            }
        }

        view('auth/login', [
            'error'  => $error,
            'styles' => '',
            'title'  => 'Log in | Astral Cloud',
        ]);
    }

    public function register(): void {
        $error   = "";
        $success = "";

        // Check the request method
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
                    // Call create function and save to database
                    else {
                        User::create($name, $email, $password, $phone ?: null);
                        header("Location: /login?registered=success");
                        exit;
                    }
                } catch (PDOException $e) {
                    $error = "A system error has occurred. Please try again later.";
                }
            }
        }

        view('auth/register', [
            'error'   => $error,
            'success' => $success,
            'styles'  => '',
            'title'   => 'Registration | Astral Cloud',
        ]);
    }

    // Logout
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