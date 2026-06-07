<?php

class ProductController {
    public function index(): void {
        $vps_plans = Product::getActive();

        view('products/index', [
            'vps_plans' => $vps_plans,
            'css' => ['products'],
            'title' => 'Astral Cloud - Virtual Server Solutions',
        ]);
    }
}
