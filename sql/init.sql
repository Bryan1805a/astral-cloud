SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS astral_cloud;
CREATE DATABASE astral_cloud
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE astral_cloud;


-- ============================================================
-- TABLE 1: USERS
-- Lưu tất cả tài khoản: user, staff, admin
-- ============================================================
CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password      VARCHAR(255)  NOT NULL,                        -- Hash bằng password_hash()
    phone         VARCHAR(20)   DEFAULT NULL,
    avatar        VARCHAR(255)  DEFAULT 'default_avatar.png',
    role          ENUM('user','staff','admin') NOT NULL DEFAULT 'user',
    -- Hạng khách hàng - tự động update theo total_spent
    tier          ENUM('silver','gold','diamond') NOT NULL DEFAULT 'silver',
    total_spent   DECIMAL(15,2) NOT NULL DEFAULT 0.00,           -- Dùng để xếp hạng
    is_locked     TINYINT(1)    NOT NULL DEFAULT 0,              -- 0 = active, 1 = locked
    locked_reason VARCHAR(255)  DEFAULT NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_role (role),
    INDEX idx_tier (tier),
    INDEX idx_is_locked (is_locked)
);

-- Logic xếp hạng:
--   silver  : total_spent <  5,000,000 VND
--   gold    : total_spent >= 5,000,000 VND
--   diamond : total_spent >= 20,000,000 VND


-- ============================================================
-- TABLE 2: PRODUCTS (Gói VPS / VM)
-- ============================================================
CREATE TABLE products (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150)  NOT NULL,                        -- VD: "VPS Starter", "Cloud Pro X2"
    slug          VARCHAR(200)  NOT NULL UNIQUE,                 -- URL: "vps-starter"
    description   TEXT          DEFAULT NULL,
    -- Thông số kỹ thuật
    cpu           VARCHAR(50)   NOT NULL,                        -- VD: "2 vCPU"
    ram           VARCHAR(50)   NOT NULL,                        -- VD: "4 GB"
    storage       VARCHAR(50)   NOT NULL,                        -- VD: "80 GB SSD"
    bandwidth     VARCHAR(50)   NOT NULL,                        -- VD: "1 Gbps"
    os_options    VARCHAR(255)  DEFAULT NULL,                    -- VD: "Ubuntu, Debian, CentOS"
    -- Giá và tồn kho
    price         DECIMAL(15,2) NOT NULL,                        -- VND / tháng
    stock         INT           NOT NULL DEFAULT 100,            -- Số lượng tối đa có thể cấp phát
    image         VARCHAR(255)  DEFAULT 'default_product.png',
    -- Trạng thái
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,              -- 0 = ẩn, 1 = hiện
    created_by    INT UNSIGNED  DEFAULT NULL,                    -- Admin/staff tạo
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_is_active (is_active),
    INDEX idx_price (price),
    INDEX idx_slug (slug)
);


-- ============================================================
-- TABLE 3: PRODUCT_PROMOTIONS
-- Khuyến mãi gắn riêng vào từng sản phẩm
-- ============================================================
CREATE TABLE product_promotions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      INT UNSIGNED  NOT NULL,
    label           VARCHAR(100)  NOT NULL,                      -- VD: "Giảm 20% tháng 6"
    discount_type   ENUM('percent','fixed') NOT NULL,
    discount_value  DECIMAL(10,2) NOT NULL,                      -- % hoặc VND
    start_date      DATE          NOT NULL,
    end_date        DATE          NOT NULL,
    is_active       TINYINT(1)    NOT NULL DEFAULT 1,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_active (product_id, is_active),
    INDEX idx_dates (start_date, end_date)
);


-- ============================================================
-- TABLE 4: VOUCHERS
-- ============================================================
CREATE TABLE vouchers (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code                 VARCHAR(50)   NOT NULL UNIQUE,           -- VD: "ASTRAL2025"
    description          VARCHAR(255)  DEFAULT NULL,
    discount_type        ENUM('percent','fixed') NOT NULL,
    discount_value       DECIMAL(10,2) NOT NULL,
    min_order_value      DECIMAL(15,2) NOT NULL DEFAULT 0.00,     -- Đơn tối thiểu để áp dụng
    max_discount         DECIMAL(15,2) DEFAULT NULL,              -- Trần giảm (cho voucher percent)
    quantity             INT           NOT NULL DEFAULT 1,        -- Tổng số lượt dùng
    used_count           INT           NOT NULL DEFAULT 0,        -- Đã dùng bao nhiêu lần
    usage_limit_per_user INT           NOT NULL DEFAULT 1,        -- [NEW] Mỗi user dùng tối đa bao nhiêu lần
    applicable_tier      ENUM('all','silver','gold','diamond') NOT NULL DEFAULT 'all', -- [NEW] Áp dụng cho tier nào
    expiry_date          DATE          NOT NULL,
    is_active            TINYINT(1)    NOT NULL DEFAULT 1,
    created_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_code_active (code, is_active),
    INDEX idx_expiry (expiry_date)
);


-- ============================================================
-- TABLE 5: CART (Giỏ hàng - lưu DB thay vì session)
-- ============================================================
CREATE TABLE cart (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED NOT NULL,
    quantity    INT          NOT NULL DEFAULT 1,
    added_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_cart_item (user_id, product_id),               -- 1 sản phẩm chỉ có 1 dòng/user
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);


-- ============================================================
-- TABLE 6: ORDERS
-- ============================================================
CREATE TABLE orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED  NOT NULL,
    voucher_id      INT UNSIGNED  DEFAULT NULL,
    voucher_code    VARCHAR(50)   DEFAULT NULL,                  -- Snapshot code lúc đặt
    subtotal        DECIMAL(15,2) NOT NULL,                      -- Tổng trước giảm
    discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,         -- Số tiền đã giảm
    total_price     DECIMAL(15,2) NOT NULL,                      -- Tổng sau giảm (thực thu)
    -- Trạng thái đơn hàng
    status          ENUM(
                        'pending',         -- Chờ thanh toán
                        'confirmed',       -- Đã xác nhận thanh toán
                        'provisioning',    -- Đang cấp phát VPS
                        'active',          -- VPS đang chạy
                        'success',         -- Hoàn tất (đã tính vào total_spent)
                        'cancelled'        -- Đã huỷ
                    ) NOT NULL DEFAULT 'pending',
    note            TEXT          DEFAULT NULL,                  -- Ghi chú của khách
    cancel_reason   VARCHAR(255)  DEFAULT NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE SET NULL,
    INDEX idx_user_status (user_id, status),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);


-- ============================================================
-- TABLE 7: ORDER_ITEMS
-- Mỗi đơn có thể chứa nhiều gói VPS
-- ============================================================
CREATE TABLE order_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        INT UNSIGNED  NOT NULL,
    product_id      INT UNSIGNED  DEFAULT NULL,                  -- NULL nếu sản phẩm bị xoá sau
    -- Snapshot thông tin lúc đặt (chuẩn hoá để giữ lịch sử)
    product_name    VARCHAR(150)  NOT NULL,
    product_cpu     VARCHAR(50)   NOT NULL,
    product_ram     VARCHAR(50)   NOT NULL,
    product_storage VARCHAR(50)   NOT NULL,
    unit_price      DECIMAL(15,2) NOT NULL,
    quantity        INT           NOT NULL DEFAULT 1,
    subtotal        DECIMAL(15,2) NOT NULL,                      -- unit_price * quantity

    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
);


-- ============================================================
-- TABLE 8: ORDER_STATUS_HISTORY
-- Dùng để hiển thị timeline trên trang "Xem tiến trình giao hàng"
-- ============================================================
CREATE TABLE order_status_history (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED  NOT NULL,
    status      ENUM('pending','confirmed','provisioning','active','success','cancelled') NOT NULL,
    note        VARCHAR(255)  DEFAULT NULL,                      -- VD: "Kỹ thuật viên đang cấu hình server"
    changed_by  INT UNSIGNED  DEFAULT NULL,                      -- Staff/admin đổi (NULL = hệ thống)
    changed_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id)  REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_time (order_id, changed_at)
);


-- ============================================================
-- TABLE 9: VOUCHER_USAGES
-- Log lại mỗi lần voucher được dùng
-- ============================================================
CREATE TABLE voucher_usages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    voucher_id  INT UNSIGNED  NOT NULL,
    user_id     INT UNSIGNED  NOT NULL,
    order_id    INT UNSIGNED  NOT NULL,
    used_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_voucher_user_order (voucher_id, user_id, order_id),
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id)  ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)     ON DELETE CASCADE,
    FOREIGN KEY (order_id)   REFERENCES orders(id)    ON DELETE CASCADE,
    INDEX idx_voucher_user (voucher_id, user_id)               -- Phục vụ check usage_limit_per_user
);


-- ============================================================
-- TABLE 10: REVIEWS
-- Đánh giá sản phẩm sau khi mua
-- ============================================================
CREATE TABLE reviews (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED  NOT NULL,
    product_id  INT UNSIGNED  NOT NULL,
    order_id    INT UNSIGNED  NOT NULL,                          -- [FIX] Bắt buộc, không cho NULL
    rating      TINYINT(1)    NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT          NOT NULL,
    is_visible  TINYINT(1)    NOT NULL DEFAULT 1,                -- Admin ẩn được review
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_review_per_order (user_id, order_id),          -- 1 review / order
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    INDEX idx_product_visible (product_id, is_visible),
    INDEX idx_rating (rating)
);


-- ============================================================
-- TABLE 11: ADMIN_EMAILS
-- Email gửi từ Admin tới User (giao tiếp nội bộ)
-- ============================================================
CREATE TABLE admin_emails (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id    INT UNSIGNED  NOT NULL,                         -- Admin/Staff gửi
    recipient_id INT UNSIGNED  DEFAULT NULL,                     -- NULL = broadcast cho tất cả
    subject      VARCHAR(255)  NOT NULL,
    body         TEXT          NOT NULL,
    is_read      TINYINT(1)    NOT NULL DEFAULT 0,
    sent_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (sender_id)    REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_recipient_read (recipient_id, is_read),
    INDEX idx_sent_at (sent_at)
);


-- ============================================================
-- TABLE 12: PAYMENTS [NEW]
-- Lưu các giao dịch thanh toán của đơn hàng
-- Một đơn có thể có nhiều payment (fail rồi retry, hoàn tiền...)
-- ============================================================
CREATE TABLE payments (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id         INT UNSIGNED  NOT NULL,
    amount           DECIMAL(15,2) NOT NULL,
    method           ENUM('bank_transfer','momo','vnpay','zalopay','cash') NOT NULL,
    transaction_code VARCHAR(100)  DEFAULT NULL,                 -- Mã giao dịch từ cổng thanh toán
    status           ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
    note             VARCHAR(255)  DEFAULT NULL,
    paid_at          TIMESTAMP     NULL DEFAULT NULL,            -- Thời điểm thanh toán thành công
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_status (order_id, status),
    INDEX idx_transaction (transaction_code),
    INDEX idx_paid_at (paid_at)
);


-- ============================================================
-- TABLE 13: SERVICES [NEW]
-- VPS instance thực tế được cấp phát cho khách
-- Đây là "hàng" khách nhận sau khi mua thành công
-- ============================================================
CREATE TABLE services (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_item_id   INT UNSIGNED  NOT NULL,
    user_id         INT UNSIGNED  NOT NULL,
    -- Thông tin VPS
    hostname        VARCHAR(100)  DEFAULT NULL,                  -- VD: vps-user123.astral.cloud
    ip_address      VARCHAR(45)   DEFAULT NULL,                  -- Hỗ trợ cả IPv4 và IPv6
    root_password   VARCHAR(255)  DEFAULT NULL,                  -- Nên mã hoá ở tầng app
    os              VARCHAR(50)   DEFAULT NULL,                  -- VD: "Ubuntu 22.04"
    -- Trạng thái và vòng đời
    status          ENUM('provisioning','running','stopped','suspended','terminated')
                    NOT NULL DEFAULT 'provisioning',
    start_date      DATE          NOT NULL,
    expiry_date     DATE          NOT NULL,                      -- Ngày hết hạn dịch vụ
    auto_renew      TINYINT(1)    NOT NULL DEFAULT 0,            -- Có tự động gia hạn không
    last_renewed_at TIMESTAMP     NULL DEFAULT NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)       REFERENCES users(id)       ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_expiry (expiry_date),
    INDEX idx_status (status),
    INDEX idx_ip (ip_address)
);


-- ============================================================
-- TABLE 14: NOTIFICATIONS [NEW]
-- Thông báo hệ thống cho user (khác email - đây là in-app notify)
-- ============================================================
CREATE TABLE notifications (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED  NOT NULL,
    type        ENUM('order','payment','service','voucher','system') NOT NULL,
    title       VARCHAR(255)  NOT NULL,
    message     TEXT          NOT NULL,
    link        VARCHAR(255)  DEFAULT NULL,                      -- URL liên quan (VD: /orders/123)
    is_read     TINYINT(1)    NOT NULL DEFAULT 0,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
);


-- ============================================================
-- TRIGGERS
-- ============================================================

-- ------------------------------------------------------------
-- TRIGGER 1A: Tự động set tier khi INSERT user mới
-- ------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER trg_update_tier_insert
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.total_spent >= 20000000 THEN
        SET NEW.tier = 'diamond';
    ELSEIF NEW.total_spent >= 5000000 THEN
        SET NEW.tier = 'gold';
    ELSE
        SET NEW.tier = 'silver';
    END IF;
END$$
DELIMITER ;

-- ------------------------------------------------------------
-- TRIGGER 1B: Tự động update tier khi UPDATE total_spent
-- ------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER trg_update_tier_update
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.total_spent != OLD.total_spent THEN
        IF NEW.total_spent >= 20000000 THEN
            SET NEW.tier = 'diamond';
        ELSEIF NEW.total_spent >= 5000000 THEN
            SET NEW.tier = 'gold';
        ELSE
            SET NEW.tier = 'silver';
        END IF;
    END IF;
END$$
DELIMITER ;

-- ------------------------------------------------------------
-- TRIGGER 2: Cập nhật total_spent khi đơn hàng đổi trạng thái
-- ------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER trg_order_status_update_spent
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    -- Cộng vào khi chuyển sang success
    IF NEW.status = 'success' AND OLD.status != 'success' THEN
        UPDATE users
        SET total_spent = total_spent + NEW.total_price
        WHERE id = NEW.user_id;
    -- Trừ ra khi từ success chuyển sang cancelled
    ELSEIF OLD.status = 'success' AND NEW.status = 'cancelled' THEN
        UPDATE users
        SET total_spent = GREATEST(total_spent - NEW.total_price, 0)
        WHERE id = NEW.user_id;
    END IF;
END$$
DELIMITER ;

-- ------------------------------------------------------------
-- TRIGGER 3: Tăng used_count của voucher khi được dùng
-- ------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER trg_voucher_used_count
AFTER INSERT ON voucher_usages
FOR EACH ROW
BEGIN
    UPDATE vouchers
    SET used_count = used_count + 1
    WHERE id = NEW.voucher_id;
END$$
DELIMITER ;

-- ------------------------------------------------------------
-- TRIGGER 4: Tự động ghi log khi đơn hàng đổi trạng thái
-- ------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER trg_order_status_history
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status != OLD.status THEN
        INSERT INTO order_status_history (order_id, status, note)
        VALUES (NEW.id, NEW.status, NULL);
    END IF;
END$$
DELIMITER ;

-- ------------------------------------------------------------
-- TRIGGER 5: Khi payment thành công -> tự động chuyển order sang confirmed
-- [NEW] Tự động hoá luồng thanh toán
-- ------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER trg_payment_success_confirm_order
AFTER UPDATE ON payments
FOR EACH ROW
BEGIN
    IF NEW.status = 'success' AND OLD.status != 'success' THEN
        UPDATE orders
        SET status = 'confirmed'
        WHERE id = NEW.order_id AND status = 'pending';
    END IF;
END$$
DELIMITER ;

-- ------------------------------------------------------------
-- TRIGGER 6: Khi tạo notification -> gửi đến user (placeholder hook)
-- ------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER trg_order_status_notify
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status != OLD.status THEN
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (
            NEW.user_id,
            'order',
            CONCAT('Đơn hàng #', NEW.id, ' cập nhật'),
            CONCAT('Trạng thái đơn hàng của bạn đã chuyển sang: ', NEW.status),
            CONCAT('/orders/', NEW.id)
        );
    END IF;
END$$
DELIMITER ;


-- ============================================================
-- SEED DATA
-- ============================================================

-- Default Admin (password: password)
INSERT INTO users (name, email, password, role, total_spent) VALUES
('Super Admin', 'admin@astralcloud.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'admin', 0);

-- Staff demo
INSERT INTO users (name, email, password, role) VALUES
('Nguyen Van A', 'staff@astralcloud.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'staff');

-- User demo
INSERT INTO users (name, email, password, role, total_spent) VALUES
('Tran Thi B', 'user1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1200000),
('Le Van C',   'user2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 8500000),
('Pham Thi D', 'user3@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 25000000);

-- Demo VPS products
INSERT INTO products (name, slug, description, cpu, ram, storage, bandwidth, os_options, price, stock, created_by) VALUES
('VPS Starter',         'vps-starter',         'Ideal for small projects, blogs, and personal websites.',
 '1 vCPU', '1 GB',  '20 GB SSD',       '500 Mbps', 'Ubuntu, Debian, CentOS', 99000,   200, 1),
('VPS Basic',           'vps-basic',           'Suitable for medium-sized web applications and API backends.',
 '2 vCPU', '2 GB',  '40 GB SSD',       '1 Gbps',   'Ubuntu, Debian, CentOS', 199000,  150, 1),
('VPS Pro',             'vps-pro',             'High performance for e-commerce and small game servers.',
 '4 vCPU', '8 GB',  '80 GB SSD NVMe',  '2 Gbps',   'Ubuntu, Debian, CentOS, AlmaLinux', 499000, 100, 1),
('VPS Business',        'vps-business',        'Optimized for enterprise use, large databases, and CI/CD pipelines.',
 '8 vCPU', '16 GB', '160 GB SSD NVMe', '5 Gbps',   'Ubuntu, Debian, CentOS, AlmaLinux', 999000,  50, 1),
('Cloud VM Enterprise', 'cloud-vm-enterprise', 'A comprehensive solution for large-scale enterprise systems.',
 '16 vCPU','64 GB', '500 GB NVMe RAID','10 Gbps',  'Ubuntu, Debian, CentOS, AlmaLinux, Windows Server', 2999000, 20, 1),
('VPS Gaming',          'vps-gaming',          'Optimized for low latency on game servers, Minecraft, and CS:GO.',
 '6 vCPU', '12 GB', '100 GB SSD NVMe', '3 Gbps',   'Ubuntu, Debian, Windows Server', 799000, 80, 1);

-- Promotional demo attached to the product.
INSERT INTO product_promotions (product_id, label, discount_type, discount_value, start_date, end_date) VALUES
(1, 'Launch offer',       'percent', 20.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(3, 'Flash Sale at the end of the month','percent', 15.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7  DAY));

-- Demo voucher
INSERT INTO vouchers (code, description, discount_type, discount_value, min_order_value, max_discount, quantity, usage_limit_per_user, applicable_tier, expiry_date) VALUES
('WELCOME10',  '10% discount on your first order.',         'percent', 10.00, 100000,  50000,  100, 1, 'all',     DATE_ADD(CURDATE(), INTERVAL 90  DAY)),
('ASTRAL2025', 'Get a 50,000 VND discount on orders of 300,000 VND or more.',      'fixed',   50000, 300000,  NULL,   50,  3, 'all',     DATE_ADD(CURDATE(), INTERVAL 60  DAY)),
('DIAMOND20',  'Diamond customers receive a 20% discount',          'percent', 20.00, 500000,  200000, 999, 5, 'diamond', DATE_ADD(CURDATE(), INTERVAL 180 DAY)),
('GOLDUP',     'Gold/Diamond customers receive a 15% discount.','percent', 15.00, 300000,  150000, 500, 3, 'gold',    DATE_ADD(CURDATE(), INTERVAL 120 DAY));

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF SCHEMA
-- Sum: 14 TABLE + 7 triggers
-- ============================================================