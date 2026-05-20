<?php
    session_start();
    require_once "config/db.php";

    // Check login
    if (!isset($_SESSION["user_id"])) {
        header("Location: auth/login.php?msg=login_required");
        exit;
    }

    $user_id = $_SESSION["user_id"];

    // Only accept POST method
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Get action and product id from form
        $action = $_POST["action"] ?? "";
        $product_id = (int)($_POST["product_id"] ?? 0);

        // Action: add to cart
        if ($action === "add" && $product_id > 0) {
            try {
                // Check the valid product
                $stmtCheck = $pdo->prepare("SELECT id FROM products WHERE id = :id AND is_active = 1 LIMIT 1");
                $stmtCheck->execute(["id" => $product_id]);

                if ($stmtCheck->fetchAll()) {
                    // Add to cart UI
                    $sqlInsert = "
                        INSERT INTO cart (user_id, product_id, quantity)
                        VALUES (:user_id, :product_id, 1)
                        ON DUPLICATE KEY UPDATE quantity = quantity + 1
                    ";

                    $stmtInsert = $pdo->prepare($sqlInsert);
                    $stmtInsert->execute([
                        "user_id" => $user_id,
                        "product_id" => $product_id
                    ]);

                    // Redirect to Cart page
                    header("Location: cart.php?msg=added_success");
                    exit;
                }
                else {
                    die("<h3 style='color:red;'>Error: The VPS package does not exist or has been discontinued.</h3>");
                }
            } catch (PDOException $e) {
                die("<h3 style='color:red;'>Database error: " . $e->getMessage() . "</h3>");
            }
        }

        // Action: remove from cart
        elseif ($action === "remove" && $product_id > 0) {
            try {
                $stmtDel = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
                $stmtDel->execute([
                    "user_id" => $user_id,
                    "product_id" => $product_id
                ]);
                header("Location: cart.php?msg=removed_success");
                exit;
            } catch (PDOException $e) {
                die("<h3 style='color:red;'>Database error: " . $e->getMessage() . "</h3>");
            }
        }
    }
    else {
        header("Location: index.php");
        exit;
    }
?>