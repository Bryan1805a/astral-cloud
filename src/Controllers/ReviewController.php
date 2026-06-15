<?php

class ReviewController {
    public function submit(): void {
        if (!isset($_SESSION["user_id"])) {
            if (isAjaxRequest()) {
                jsonResponse(["success" => false, "message" => "Please log in first."], 401);
            }
            header("Location: /login");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: /");
            exit;
        }

        verifyCsrfToken();

        $userId    = $_SESSION["user_id"];
        $productId = (int) ($_POST["product_id"] ?? 0);
        $orderId   = !empty($_POST["order_id"]) ? (int) $_POST["order_id"] : null;
        $rating    = (int) ($_POST["rating"] ?? 0);
        $comment   = trim($_POST["comment"] ?? "");

        if ($productId <= 0 || $rating < 1 || $rating > 5 || empty($comment)) {
            if (isAjaxRequest()) {
                jsonResponse(["success" => false, "message" => "Please provide a rating (1-5) and a comment."], 400);
            }
            header("Location: /?err=invalid_review");
            exit;
        }

        if (strlen($comment) < 10) {
            if (isAjaxRequest()) {
                jsonResponse(["success" => false, "message" => "Comment must be at least 10 characters."], 400);
            }
            header("Location: /?err=review_too_short");
            exit;
        }

        $eligible = Review::canReview($userId, $productId);
        if (!$eligible) {
            if (isAjaxRequest()) {
                jsonResponse(["success" => false, "message" => "You must have a successful order for this product to leave a review."], 403);
            }
            header("Location: /?err=not_eligible");
            exit;
        }

        $orderId = $orderId ?? $eligible["order_id"];

        Review::create($userId, $productId, $orderId, $rating, $comment);

        AuditLog::log("review.create", "product", $productId,
            "User submitted review (rating: {$rating}) for product #{$productId}"
        );

        if (isAjaxRequest()) {
            $reviews = Review::getByProduct($productId);
            jsonResponse(["success" => true, "message" => "Review submitted!", "reviews" => $reviews]);
        }

        header("Location: /?msg=review_submitted#plan-" . $productId);
        exit;
    }
}
