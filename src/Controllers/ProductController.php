<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Review;

class ProductController extends Controller {
    public function index(): void {
        view('home/index', [
            'css'   => ['products'],
            'title' => 'Astral Cloud - Virtual Server Solutions',
        ]);
    }

    public function plans(): void {
        $perPage = 3;
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $search  = trim($_GET['search'] ?? '');
        $sort    = $_GET['sort'] ?? 'price_asc';

        $allPlans = Product::getFiltered($search, $sort);
        $totalPages = (int) ceil(count($allPlans) / $perPage);
        $page = min($page, $totalPages ?: 1);

        $offset = ($page - 1) * $perPage;
        $vps_plans = array_slice($allPlans, $offset, $perPage);

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

        view('plans/index', [
            'vps_plans'       => $vps_plans,
            'featured_plans'  => array_slice(Product::getFiltered(), 0, 3),
            'product_reviews' => $product_reviews,
            'can_review'      => $can_review,
            'page'            => $page,
            'total_pages'     => $totalPages,
            'search'          => $search,
            'sort'            => $sort,
            'css'             => ['products'],
            'title'           => 'VPS Plans | Astral Cloud',
        ]);
    }

    public function blog(): void {
        view('blog/index', [
            'css'   => ['products'],
            'title' => 'Cloud Knowledge | Astral Cloud',
        ]);
    }

    public function detail(): void {
        $slug = trim($_GET['slug'] ?? '');
        if (empty($slug)) {
            header('Location: /plans');
            exit;
        }

        $product = Product::findBySlug($slug);
        if (!$product) {
            header('Location: /plans');
            exit;
        }

        $reviews = Review::getByProduct((int) $product['id']);
        $canReview = null;
        if (isset($_SESSION['user_id'])) {
            $canReview = Review::canReview($_SESSION['user_id'], (int) $product['id']);
        }

        view('product/index', [
            'product'   => $product,
            'reviews'   => $reviews,
            'can_review'=> $canReview,
            'css'       => ['products'],
            'title'     => htmlspecialchars($product['name']) . ' | Astral Cloud',
        ]);
    }

    public function docs(): void {
        view('docs/index', [
            'css'   => ['docs'],
            'title' => 'Documentation | Astral Cloud',
        ]);
    }
}
