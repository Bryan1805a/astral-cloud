<?php

// Load language from session or default to English
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Change language via GET param
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'vi'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'];

$langData = [
    'en' => [
        // Navbar
        'nav_home'         => 'HOME',
        'nav_features'     => 'FEATURES',
        'nav_plans'        => 'VPS PLANS',
        'nav_docs'         => 'DOCS',
        'nav_blog'         => 'BLOG',
        'nav_login'        => 'Login',
        'nav_register'     => 'Start now',
        'nav_inbox'        => 'Inbox',
        'nav_profile'      => 'My Profile',
        'nav_orders'       => 'My Orders',
        'nav_logout'       => 'Logout',
        'nav_cart'         => 'Cart',
        'nav_admin'        => 'Admin',

        // Hero
        'hero_title1'      => 'Cloud VPS that makes your',
        'hero_title2'      => 'FASTER!',
        'hero_sub'         => 'Astral Cloud provides high-performance VPS, fast deployment, stable security, and easy management for students, developers, and businesses.',
        'hero_btn_plans'   => 'View VPS Plans',
        'hero_btn_start'   => 'Get Started',

        // About
        'about_title'      => 'Why Astral Cloud?',
        'about_desc'       => 'We simulate a modern VPS rental and management system, helping users find server packages, place orders, make demo payments, and track activation status.',
        'about_uptime'     => 'Uptime',
        'about_uptime_desc'=> 'Always-on infrastructure for stable VPS performance.',
        'about_storage'    => 'SSD Storage',
        'about_storage_desc'=>'Ultra-fast storage for websites, APIs and applications.',
        'about_support'    => 'Support',
        'about_support_desc'=>'Professional assistance whenever you need help.',
        'about_ddos'       => 'Protection',
        'about_ddos_desc'  => 'Advanced protection against malicious attacks.',

        // Plans page
        'plans_title'      => 'Our VPS Packages',
        'plans_sub'        => 'Popular VPS packages for a variety of usage needs.',
        'plans_all_title'  => 'All VPS Plans',
        'plans_all_sub'    => 'Choose the VPS package that suits your needs.',
        'plans_search'     => 'Search plans...',
        'plans_sort_price_asc'=> 'Price: Low → High',
        'plans_sort_price_desc'=>'Price: High → Low',
        'plans_sort_rating'=> 'Highest Rated',
        'plans_sort_name'  => 'Name: A → Z',
        'plans_search_btn' => 'Search',
        'plans_clear'      => 'Clear',
        'plans_no_results' => 'No VPS plans match your search.',
        'plans_results_for'=> 'Results for',
        'plans_found'      => 'product(s) found',
        'plans_month'      => '/month',
        'plans_reviews'    => 'Reviews',
        'plans_no_reviews' => 'No reviews',
        'plans_add_cart'   => 'Add to Cart',
        'plans_out_of_stock'=>'Out of Stock',
        'plans_view'       => 'View plan',
        'plans_cpu'        => 'CPU',
        'plans_ram'        => 'RAM',
        'plans_storage'    => 'Storage',
        'plans_bandwidth'  => 'Bandwidth',

        // Auth
        'login_title'      => 'Log in | Astral Cloud',
        'login_heading'    => 'Login',
        'login_email'      => 'Email',
        'login_password'   => 'Password',
        'login_btn'        => 'Log In',
        'login_no_account' => "Don't have an account?",
        'login_forgot'     => 'Forgot password?',
        'login_register'   => 'Register',

        'register_title'   => 'Registration | Astral Cloud',
        'register_heading' => 'Create Account',
        'register_name'    => 'Full Name',
        'register_phone'   => 'Phone (optional)',
        'register_confirm' => 'Confirm Password',
        'register_btn'     => 'Create Account',
        'register_have_account' => 'Already have an account?',

        'mfa_title'        => 'MFA Verification | Astral Cloud',
        'mfa_heading'      => 'Two-Factor Authentication',
        'mfa_sub'          => 'Enter the 6-digit code from your authenticator app.',
        'mfa_btn'          => 'Verify',

        'otp_title'        => 'Verify Account | Astral Cloud',
        'otp_heading'      => 'Verify Your Email',
        'otp_sent_to'      => 'A verification code has been sent to',
        'otp_btn'          => 'Verify Account',

        // Product detail
        'prod_back'        => '← Back to Plans',
        'prod_in_stock'    => 'In stock',
        'prod_available'   => 'available',
        'prod_out_of_stock'=>'Out of stock',
        'prod_add_cart'    => 'Add to Cart',
        'prod_reviews'     => 'Customer Reviews',
        'prod_no_reviews'  => 'No reviews yet. Be the first to review this product!',

        // Blog
        'blog_title'       => 'Cloud Knowledge',
        'blog_card1_title' => 'What is VPS?',
        'blog_card1_desc'  => 'Learn how virtual private servers work in a cloud environment.',
        'blog_card2_title' => 'Choosing the Right VPS Plan',
        'blog_card2_desc'  => 'Understand how RAM, CPU, SSD and bandwidth affect performance.',
        'blog_card3_title' => 'Server Security Guide',
        'blog_card3_desc'  => 'Basic practices to keep your VPS safer and more reliable.',
        'blog_read_more'   => 'Read More →',

        // General
        'lang_switch'      => 'Tiếng Việt',
        'footer_tagline'   => 'High-performance VPS hosting for students, developers, and businesses. Deploy your server in minutes.',
        'footer_platform'  => 'Platform',
        'footer_account'   => 'Account',
        'footer_support'   => 'Support',
        'footer_copyright' => 'Astral Cloud. All rights reserved.',
        'footer_powered'   => 'Powered by PHP + MySQL + VMware',

        // Docs quick
        'docs_faq'         => 'FAQ',
        'docs_started'     => 'Getting Started',
        'docs_commands'    => 'Linux Commands',
    ],
    'vi' => [
        // Navbar
        'nav_home'         => 'TRANG CHỦ',
        'nav_features'     => 'TÍNH NĂNG',
        'nav_plans'        => 'GÓI VPS',
        'nav_docs'         => 'HƯỚNG DẪN',
        'nav_blog'         => 'BLOG',
        'nav_login'        => 'Đăng nhập',
        'nav_register'     => 'Bắt đầu ngay',
        'nav_inbox'        => 'Hộp thư',
        'nav_profile'      => 'Hồ sơ',
        'nav_orders'       => 'Đơn hàng',
        'nav_logout'       => 'Đăng xuất',
        'nav_cart'         => 'Giỏ hàng',
        'nav_admin'        => 'Quản trị',

        // Hero
        'hero_title1'      => 'Cloud VPS giúp dự án của bạn',
        'hero_title2'      => 'NHANH HƠN!',
        'hero_sub'         => 'Astral Cloud cung cấp VPS hiệu suất cao, triển khai nhanh, bảo mật ổn định và dễ dàng quản lý cho sinh viên, lập trình viên và doanh nghiệp.',
        'hero_btn_plans'   => 'Xem gói VPS',
        'hero_btn_start'   => 'Bắt đầu',

        // About
        'about_title'      => 'Tại sao chọn Astral Cloud?',
        'about_desc'       => 'Chúng tôi mô phỏng hệ thống cho thuê và quản lý VPS hiện đại, giúp người dùng tìm gói máy chủ, đặt hàng, thanh toán demo và theo dõi trạng thái kích hoạt.',
        'about_uptime'     => 'Thời gian hoạt động',
        'about_uptime_desc'=> 'Hạ tầng luôn sẵn sàng cho hiệu suất VPS ổn định.',
        'about_storage'    => 'Ổ cứng SSD',
        'about_storage_desc'=>'Lưu trữ siêu nhanh cho website, API và ứng dụng.',
        'about_support'    => 'Hỗ trợ',
        'about_support_desc'=>'Hỗ trợ chuyên nghiệp bất cứ khi nào bạn cần.',
        'about_ddos'       => 'Bảo vệ',
        'about_ddos_desc'  => 'Bảo vệ nâng cao chống lại các cuộc tấn công độc hại.',

        // Plans page
        'plans_title'      => 'Các gói VPS',
        'plans_sub'        => 'Các gói VPS phổ biến cho nhiều nhu cầu sử dụng.',
        'plans_all_title'  => 'Tất cả gói VPS',
        'plans_all_sub'    => 'Chọn gói VPS phù hợp với nhu cầu của bạn.',
        'plans_search'     => 'Tìm kiếm gói...',
        'plans_sort_price_asc'=> 'Giá: Thấp → Cao',
        'plans_sort_price_desc'=>'Giá: Cao → Thấp',
        'plans_sort_rating'=> 'Đánh giá cao nhất',
        'plans_sort_name'  => 'Tên: A → Z',
        'plans_search_btn' => 'Tìm',
        'plans_clear'      => 'Xóa',
        'plans_no_results' => 'Không tìm thấy gói VPS phù hợp.',
        'plans_results_for'=> 'Kết quả cho',
        'plans_found'      => 'sản phẩm được tìm thấy',
        'plans_month'      => '/tháng',
        'plans_reviews'    => 'Đánh giá',
        'plans_no_reviews' => 'Chưa có đánh giá',
        'plans_add_cart'   => 'Thêm vào giỏ',
        'plans_out_of_stock'=>'Hết hàng',
        'plans_view'       => 'Xem gói',
        'plans_cpu'        => 'CPU',
        'plans_ram'        => 'RAM',
        'plans_storage'    => 'Lưu trữ',
        'plans_bandwidth'  => 'Băng thông',

        // Auth
        'login_title'      => 'Đăng nhập | Astral Cloud',
        'login_heading'    => 'Đăng nhập',
        'login_email'      => 'Email',
        'login_password'   => 'Mật khẩu',
        'login_btn'        => 'Đăng nhập',
        'login_no_account' => 'Chưa có tài khoản?',
        'login_forgot'     => 'Quên mật khẩu?',
        'login_register'   => 'Đăng ký',

        'register_title'   => 'Đăng ký | Astral Cloud',
        'register_heading' => 'Tạo tài khoản',
        'register_name'    => 'Họ và tên',
        'register_phone'   => 'Số điện thoại (tùy chọn)',
        'register_confirm' => 'Xác nhận mật khẩu',
        'register_btn'     => 'Tạo tài khoản',
        'register_have_account' => 'Đã có tài khoản?',

        'mfa_title'        => 'Xác thực 2 lớp | Astral Cloud',
        'mfa_heading'      => 'Xác thực hai yếu tố',
        'mfa_sub'          => 'Nhập mã 6 chữ số từ ứng dụng xác thực của bạn.',
        'mfa_btn'          => 'Xác nhận',

        'otp_title'        => 'Xác thực tài khoản | Astral Cloud',
        'otp_heading'      => 'Xác thực Email',
        'otp_sent_to'      => 'Mã xác thực đã được gửi đến',
        'otp_btn'          => 'Xác thực tài khoản',

        // Product detail
        'prod_back'        => '← Quay lại gói VPS',
        'prod_in_stock'    => 'Còn hàng',
        'prod_available'   => 'có sẵn',
        'prod_out_of_stock'=>'Hết hàng',
        'prod_add_cart'    => 'Thêm vào giỏ',
        'prod_reviews'     => 'Đánh giá khách hàng',
        'prod_no_reviews'  => 'Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá!',

        // Blog
        'blog_title'       => 'Kiến thức Cloud',
        'blog_card1_title' => 'VPS là gì?',
        'blog_card1_desc'  => 'Tìm hiểu cách máy chủ ảo hoạt động trong môi trường đám mây.',
        'blog_card2_title' => 'Chọn gói VPS phù hợp',
        'blog_card2_desc'  => 'Hiểu RAM, CPU, SSD và băng thông ảnh hưởng đến hiệu suất.',
        'blog_card3_title' => 'Hướng dẫn bảo mật máy chủ',
        'blog_card3_desc'  => 'Các biện pháp cơ bản giúp VPS an toàn và đáng tin cậy hơn.',
        'blog_read_more'   => 'Đọc thêm →',

        // General
        'lang_switch'      => 'English',
        'footer_tagline'   => 'Dịch vụ VPS hiệu suất cao cho sinh viên, lập trình viên và doanh nghiệp. Triển khai máy chủ trong vài phút.',
        'footer_platform'  => 'Nền tảng',
        'footer_account'   => 'Tài khoản',
        'footer_support'   => 'Hỗ trợ',
        'footer_copyright' => 'Astral Cloud. Đã đăng ký bản quyền.',
        'footer_powered'   => 'Xây dựng bởi PHP + MySQL + VMware',

        // Docs quick
        'docs_faq'         => 'Câu hỏi thường gặp',
        'docs_started'     => 'Bắt đầu',
        'docs_commands'    => 'Lệnh Linux',
    ]
];

// Translation helper — use in views: __( 'nav_home' )
function __(string $key): string {
    global $lang, $langData;
    return $langData[$lang][$key] ?? $langData['en'][$key] ?? $key;
}
