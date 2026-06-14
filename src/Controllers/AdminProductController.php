<?php

class AdminProductController {
    private function checkAdmin() {
        if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
            header("Location: /login");
            exit;
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
            verifyCsrfToken();
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
                'stock' => (int)($_POST['stock'] ?? 100),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'created_by' => $_SESSION['user_id']
            ];

            Product::create($data);

            $newId = (int) Database::getConnection()->lastInsertId();
            AuditLog::log("product.create", "product", $newId,
                "Created VPS package: {$data["name"]} ({$data["price"]} VND/month)"
            );

            header("Location: /admin/products?msg=created");
            exit;
        }
    }

    // Update processing
    public function update() {
        $this->checkAdmin();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            $id = (int)$_POST["id"];
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'cpu' => $_POST['cpu'],
                'ram' => $_POST['ram'],
                'storage' => $_POST['storage'],
                'bandwidth' => $_POST['bandwidth'],
                'price' => $_POST['price'],
                'stock' => $_POST['stock'] !== '' ? (int)$_POST['stock'] : 100,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            Product::update($id, $data);
            AuditLog::log("product.update", "product", $id,
                "Updated VPS package: {$data["name"]}"
            );
            header("Location: /admin/products?msg=updated");
            exit;
        }
    }

    // Toggle visible
    public function toggle() {
        $this->checkAdmin();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            verifyCsrfToken();
            $productId = (int)$_POST["id"];
            Product::toggleActive($productId);
            AuditLog::log("product.toggle_active", "product", $productId,
                "Toggled VPS package visibility (ID: {$productId})"
            );
            header("Location: /admin/products?msg=toggled");
            exit;
        }
    }
}