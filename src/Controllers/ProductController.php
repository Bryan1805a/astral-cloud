<?php

class ProductController {
    public function index(): void {
        $vps_plans = Product::getActive();

        view('products/index', [
            'vps_plans' => $vps_plans,
            'styles' => '
                .glass-card {
                    background: rgba(30, 41, 59, 0.7);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                }
                .glass-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
                    border-color: rgba(56, 189, 248, 0.5);
                }
                .price-text {
                    color: #38bdf8;
                    font-size: 1.5rem;
                    font-weight: bold;
                }
            ',
            'title' => 'Astral Cloud - Virtual Server Solutions',
        ]);
    }
}
