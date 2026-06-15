<?php

class ProductController {
    public function index(): void {
        $vps_plans = Product::getActive();

        $product_reviews = [];
        foreach ($vps_plans as $plan) {
            $product_reviews[$plan['id']] = Review::getByProduct((int) $plan['id']);
        }

        $can_review = [];
        if (isset($_SESSION['user_id'])) {
            foreach ($vps_plans as $plan) {
                $can_review[$plan['id']] = Review::canReview($_SESSION['user_id'], (int) $plan['id']);
            }
        }

        view('products/index', [
            'vps_plans'       => $vps_plans,
            'product_reviews' => $product_reviews,
            'can_review'      => $can_review,
            'css'             => ['products'],
            'title'           => 'Astral Cloud - Virtual Server Solutions',
        ]);
    }
}
