<?php
require_once __DIR__ . "/../Models/Product.php";

class AdminProductController {
    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SESSION["user_role"] !== "admin" && $_SESSION["user_role"] !== "staff") {
            die("403 - No access.");
        }
    }

    // Display product list
    public function index() {
        $this->checkAdmin();
        $products = Product::getAllForAdmin();
        require_once __DIR__ . "/../Views/admin/products/index.php";
    }

    // Add new product
    public function store() {
        $this->checkAdmin();
        
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // create slug
            $slug = strtolower(trim(preg_replace("/[^A-Za-z0-9-]+/", "-", $_POST["name"])));
            $slug .= "-" . time(); // add timestamp for indetifying

            $data = [
                "name" => $_POST["name"],
                "slug" => $slug,
                "description" => $_POST["description"],
                "cpu" => $_POST["cpu"],
                "ram" => $_POST["ram"],
                'storage' => $_POST['storage'],
                'bandwidth' => $_POST['bandwidth'],
                'price' => $_POST['price'],
                'stock' => $_POST['stock'] ?? 100,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'created_by' => $_SESSION['user_id']
            ];

            Product::create($data);
            header("Location: /admin/products?msg=created");
        }
    }

    // Update processing
    public function update() {
        $this->checkAdmin();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id = (int)$_POST["id"];
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'cpu' => $_POST['cpu'],
                'ram' => $_POST['ram'],
                'storage' => $_POST['storage'],
                'bandwidth' => $_POST['bandwidth'],
                'price' => $_POST['price'],
                'stock' => $_POST['stock'] ?? 100,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            Product::update($id, $data);
            header("Location: /admin/products?msg=updated");
        }
    }

    // Toggle visible
    public function toggle() {
        $this->checkAdmin();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            Product::toggleActive((int)$_POST["id"]);
            header("Location: /admin/products?msg=toggled");
        }
    }
}