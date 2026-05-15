--  ASTRAL CLOUD — Database Schema

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS astral_cloud
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE astral_cloud;


-- TABLE 1: USERS
-- Save all acounts: user, staff, admin
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password      VARCHAR(255)  NOT NULL,                        -- save hash, use password_hash()
    phone         VARCHAR(20)   DEFAULT NULL,
    avatar        VARCHAR(255)  DEFAULT 'default_avatar.png',
    role          ENUM('user','staff','admin') NOT NULL DEFAULT 'user',
    -- Customer rank - auto update based on total_spent
    tier          ENUM('silver','gold','diamond') NOT NULL DEFAULT 'silver',
    total_spent   DECIMAL(15,2) NOT NULL DEFAULT 0.00,           -- Use to ranking
    is_locked     TINYINT(1)    NOT NULL DEFAULT 0,              -- 0 = active, 1 = locked
    locked_reason VARCHAR(255)  DEFAULT NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- Ranking logic:
--   silver  : total_spent <  5,000,000 VND
--   gold    : total_spent >= 5,000,000 VND
--   diamond : total_spent >= 20,000,000 VND
-- ============================================================


-- TABLE 2: PRODUCTS (Pack VPS / VM)
CREATE TABLE IF NOT EXISTS products (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150)  NOT NULL,                        -- Example: "VPS Starter", "Cloud Pro X2"
    slug          VARCHAR(200)  NOT NULL UNIQUE,                 -- URL: "vps-starter"
    description   TEXT          DEFAULT NULL,
    -- Specifications
    cpu           VARCHAR(50)   NOT NULL,                        -- Example: "2 vCPU"
    ram           VARCHAR(50)   NOT NULL,                        -- Example: "4 GB"
    storage       VARCHAR(50)   NOT NULL,                        -- Example: "80 GB SSD"
    bandwidth     VARCHAR(50)   NOT NULL,                        -- Example: "1 Gbps"
    os_options    VARCHAR(255)  DEFAULT NULL,                    -- Example: "Ubuntu, Debian, CentOS"
    -- Price and stock
    price         DECIMAL(15,2) NOT NULL,                        -- VND / tháng
    stock         INT           NOT NULL DEFAULT 100,
    image         VARCHAR(255)  DEFAULT 'default_product.png',
    -- Status
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,              -- 0 = hidden, 1 = visible
    created_by    INT UNSIGNED  DEFAULT NULL,                    -- admin/staff creates
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);


-- TABLE 3: PRODUCT_PROMOTIONS
CREATE TABLE IF NOT EXISTS product_promotions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      INT UNSIGNED  NOT NULL,
    label           VARCHAR(100)  NOT NULL,                      -- Example: "20% discount in June"
    discount_type   ENUM('percent','fixed') NOT NULL,
    discount_value  DECIMAL(10,2) NOT NULL,                      -- % or VND
    start_date      DATE          NOT NULL,
    end_date        DATE          NOT NULL,
    is_active       TINYINT(1)    NOT NULL DEFAULT 1,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);


-- TABLE 4: VOUCHERS
CREATE TABLE IF NOT EXISTS vouchers (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50)   NOT NULL UNIQUE,               -- Example: "ASTRAL2025"
    description     VARCHAR(255)  DEFAULT NULL,
    discount_type   ENUM('percent','fixed') NOT NULL,
    discount_value  DECIMAL(10,2) NOT NULL,
    min_order_value DECIMAL(15,2) NOT NULL DEFAULT 0.00,         -- Minimum order amount applies
    max_discount    DECIMAL(15,2) DEFAULT NULL,                  -- Maximum reduction ceiling (used for percentage reduction)
    quantity        INT           NOT NULL DEFAULT 1,            -- Total number of uses
    used_count      INT           NOT NULL DEFAULT 0,            -- How many times has it been used
    expiry_date     DATE          NOT NULL,
    is_active       TINYINT(1)    NOT NULL DEFAULT 1,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- TABLE 5: CART (Shopping Cart - save to DB instead of Session)
CREATE TABLE IF NOT EXISTS cart (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED NOT NULL,
    quantity    INT          NOT NULL DEFAULT 1,
    added_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_cart_item (user_id, product_id),               -- Each product has only one line per user.
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);


-- TABLE 6: ORDERS
CREATE TABLE IF NOT EXISTS orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED  NOT NULL,
    voucher_id      INT UNSIGNED  DEFAULT NULL,
    voucher_code    VARCHAR(50)   DEFAULT NULL,                  -- Save the code at the time of booking.
    subtotal        DECIMAL(15,2) NOT NULL,                      -- Total before reduction
    discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,         -- The amount has been reduced.
    total_price     DECIMAL(15,2) NOT NULL,                      -- Total after discount (actual payment)
    -- Order status
    status          ENUM(
                        'pending',
                        'confirmed',
                        'provisioning',
                        'active',
                        'success',
                        'cancelled'
                    ) NOT NULL DEFAULT 'pending',
    note            TEXT          DEFAULT NULL,                  -- Customer's notes
    cancel_reason   VARCHAR(255)  DEFAULT NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)   REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE SET NULL
);


-- TABLE 7: ORDER_ITEMS
CREATE TABLE IF NOT EXISTS order_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED  NOT NULL,
    product_id  INT UNSIGNED  DEFAULT NULL,                      -- NULL if the product is deleted later
    -- Snapshot of information at the time of ordering.
    product_name  VARCHAR(150) NOT NULL,
    product_cpu   VARCHAR(50)  NOT NULL,
    product_ram   VARCHAR(50)  NOT NULL,
    product_storage VARCHAR(50) NOT NULL,
    unit_price    DECIMAL(15,2) NOT NULL,
    quantity      INT           NOT NULL DEFAULT 1,
    subtotal      DECIMAL(15,2) NOT NULL,                        -- unit_price * quantity

    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);


-- TABLE 8: ORDER_STATUS_HISTORY
-- Used to display the timeline on the "View Delivery Process" page.
CREATE TABLE IF NOT EXISTS order_status_history (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED  NOT NULL,
    status      ENUM('pending','confirmed','provisioning','active','success','cancelled') NOT NULL,
    note        VARCHAR(255)  DEFAULT NULL,                      -- Example: "The technician is configuring the server."
    changed_by  INT UNSIGNED  DEFAULT NULL,                      -- Staff/admin change
    changed_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (order_id)  REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
);


-- TABLE 9: VOUCHER_USAGES
CREATE TABLE IF NOT EXISTS voucher_usages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    voucher_id  INT UNSIGNED  NOT NULL,
    user_id     INT UNSIGNED  NOT NULL,
    order_id    INT UNSIGNED  NOT NULL,
    used_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_voucher_user_order (voucher_id, user_id, order_id),
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id)  ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)     ON DELETE CASCADE,
    FOREIGN KEY (order_id)   REFERENCES orders(id)    ON DELETE CASCADE
);


-- TABLE 10: REVIEWS
CREATE TABLE IF NOT EXISTS reviews (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED  NOT NULL,
    product_id  INT UNSIGNED  NOT NULL,
    order_id    INT UNSIGNED  DEFAULT NULL,                      -- Only leave a review if you have already purchased.
    rating      TINYINT(1)    NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT          NOT NULL,
    is_visible  TINYINT(1)    NOT NULL DEFAULT 1,                -- Admin can hide review
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_review_per_order (user_id, order_id),         -- 1 review / order
    FOREIGN KEY (user_id)   REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id)  REFERENCES orders(id)   ON DELETE SET NULL
);


-- TABLE 11: ADMIN_EMAILS (Email from Admin to User)
CREATE TABLE IF NOT EXISTS admin_emails (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT UNSIGNED  NOT NULL,                          -- Admin/Staff sent
    -- Send to: can be sent to a specific user or broadcast
    recipient_id INT UNSIGNED DEFAULT NULL,                      -- NULL = Send All
    subject     VARCHAR(255)  NOT NULL,
    body        TEXT          NOT NULL,
    is_read     TINYINT(1)    NOT NULL DEFAULT 0,
    sent_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (sender_id)    REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
);


-- TRIGGERS

-- TRIGGER 1: Automatically update the tier after total_spent changes.
DELIMITER $$
CREATE TRIGGER trg_update_tier
BEFORE UPDATE ON users
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

-- TRIGGER 2: When the order changes to 'success' -> add total_spent to the user's account.
DELIMITER $$
CREATE TRIGGER trg_order_success_update_spent
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status = 'success' AND OLD.status != 'success' THEN
        UPDATE users
        SET total_spent = total_spent + NEW.total_price
        WHERE id = NEW.user_id;
    END IF;
END$$
DELIMITER ;

-- TRIGGER 3: After placing your order -> increase the used_count of the voucher (if any)
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

-- TRIGGER 4: Automatically record order history when order status changes.
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


-- Seed Data

-- Default Admin (password: Admin@123)
INSERT IGNORE INTO users (name, email, password, role, tier) VALUES
('Super Admin', 'admin@astralcloud.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
 'admin', 'diamond');

-- Staff demo (password: Staff@123)
INSERT IGNORE INTO users (name, email, password, role) VALUES
('Nguyen Van A', 'staff@astralcloud.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'staff');

-- User demo (password: User@123)
INSERT IGNORE INTO users (name, email, password, role, total_spent, tier) VALUES
('Tran Thi B',     'user1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1200000,  'silver'),
('Le Van C',       'user2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 8500000,  'gold'),
('Pham Thi D',     'user3@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 25000000, 'diamond');

-- Demo VPS products
INSERT IGNORE INTO products (name, slug, description, cpu, ram, storage, bandwidth, price, stock, created_by) VALUES
('VPS Starter',
 'vps-starter',
 'Lý tưởng cho các dự án nhỏ, blog, website cá nhân.',
 '1 vCPU', '1 GB', '20 GB SSD', '500 Mbps', 99000, 200, 1),

('VPS Basic',
 'vps-basic',
 'Phù hợp cho các ứng dụng web vừa, API backend.',
 '2 vCPU', '2 GB', '40 GB SSD', '1 Gbps', 199000, 150, 1),

('VPS Pro',
 'vps-pro',
 'Hiệu năng cao cho e-commerce, game server nhỏ.',
 '4 vCPU', '8 GB', '80 GB SSD NVMe', '2 Gbps', 499000, 100, 1),

('VPS Business',
 'vps-business',
 'Tối ưu cho doanh nghiệp, database lớn, CI/CD pipeline.',
 '8 vCPU', '16 GB', '160 GB SSD NVMe', '5 Gbps', 999000, 50, 1),

('Cloud VM Enterprise',
 'cloud-vm-enterprise',
 'Giải pháp toàn diện cho hệ thống doanh nghiệp quy mô lớn.',
 '16 vCPU', '64 GB', '500 GB NVMe RAID', '10 Gbps', 2999000, 20, 1),

('VPS Gaming',
 'vps-gaming',
 'Tối ưu độ trễ thấp cho game server, Minecraft, CS:GO.',
 '6 vCPU', '12 GB', '100 GB SSD NVMe', '3 Gbps', 799000, 80, 1);

-- Khuyến mãi mẫu
INSERT IGNORE INTO product_promotions (product_id, label, discount_type, discount_value, start_date, end_date) VALUES
(1, 'Ưu đãi ra mắt', 'percent', 20.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(3, 'Flash Sale cuối tháng', 'percent', 15.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY));

-- Voucher mẫu
INSERT IGNORE INTO vouchers (code, description, discount_type, discount_value, min_order_value, max_discount, quantity, expiry_date) VALUES
('WELCOME10',  'Giảm 10% cho đơn đầu tiên',   'percent', 10.00, 100000,  50000,  100, DATE_ADD(CURDATE(), INTERVAL 90 DAY)),
('ASTRAL2025', 'Giảm 50,000đ cho đơn >= 300k', 'fixed',   50000, 300000,  NULL,   50,  DATE_ADD(CURDATE(), INTERVAL 60 DAY)),
('DIAMOND20',  'Ưu đãi khách Diamond 20%',     'percent', 20.00, 500000,  200000, 999, DATE_ADD(CURDATE(), INTERVAL 180 DAY));

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF SCHEMA
-- Tổng: 11 TABLE + 4 triggers + seed data
-- ============================================================