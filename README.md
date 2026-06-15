# Astral Cloud

**VPS hosting e-commerce platform** — browse, purchase, and manage virtual private servers entirely through a web browser.

---

## Overview

Astral Cloud is a full-stack VPS rental platform that automates server provisioning. Customers browse plans, pay through VNPay, and the system automatically clones and deploys Ubuntu virtual machines via VMware Workstation. Provisioned servers are managed through a web dashboard with a browser-based SSH terminal.

```
User Browser → astralcloud.shop (DigitalOcean)
                 ├── PHP 8.4 + Apache (custom MVC)
                 ├── MySQL 8.0 (17 tables, 7 triggers)
                 └── Cron (automated maintenance every 2 min)
                             │
                    ngrok tunnel
                             │
                 VM Bridge (Node.js on Windows)
                 └── VMware Workstation (vm provisioning)
```

---

## Features

| Feature | Description |
|---------|-------------|
| **Product catalog** | VPS plans with specs, pricing, reviews, and star ratings |
| **Shopping cart** | Server-side persistent cart stored in database |
| **Discount vouchers** | Tier-gated (silver/gold/diamond), usage limits, expiry dates |
| **VNPay integration** | Real payment gateway (sandbox) with HMAC-SHA512 signature verification |
| **Account system** | Registration with OTP email verification, MFA (TOTP), password reset |
| **Auto-provisioning** | VM cloning triggered on payment, IP discovery, SSH console registration |
| **VM management** | Start/stop/restart/rebuild from web dashboard |
| **Web SSH terminal** | xterm.js + WebSocket → ssh2 library → browser-based console |
| **Resource monitoring** | CPU, RAM, disk, network metrics collected from provisioned VMs |
| **Admin dashboard** | Revenue charts, user management, order control, audit logs |
| **PDF invoices** | DOM-based PDF generation for admin records |
| **Automated maintenance** | Cron handles expiry checks, provisioning retries, reminders, cleanup |

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Web server | Apache 2.4 with mod_rewrite, mod_proxy, mod_proxy_wstunnel |
| Backend | PHP 8.4 — custom MVC, no framework |
| Database | MySQL 8.0 (InnoDB) with triggers for business logic automation |
| VM Bridge | Node.js + Express + ssh2 + ws (WebSocket) |
| Payment | VNPay sandbox API v2.1 |
| Email | PHPMailer (Gmail SMTP or SendGrid) |
| PDF | Dompdf |
| Containerization | Docker Compose (dev), bare-metal (production) |
| SSL | Let's Encrypt via Certbot |

---

## Architecture

### Custom MVC

```
Browser Request
  → .htaccess (rewrite to index.php)
  → index.php (load .env, session, autoloader)
  → Router.php (exact path matching)
  → Controller (business logic)
  → Model (database queries with PDO prepared statements)
  → view() helper renders Views/layouts/header.php + view file + footer.php
```

### Order Lifecycle

```
pending → confirmed → provisioning → active → success
                ↘         ↙
                cancelled
```

- `pending` — order placed, awaiting payment
- `confirmed` — payment verified (trigger auto-confirms)
- `provisioning` — VM being cloned and booted
- `active` — VM running, user can manage
- `success` — order complete, user spending updated

### VM Provisioning

1. Payment confirmed → `Service::provisionForOrder()` calls VM Bridge
2. VM Bridge runs `vmrun clone` (linked clone from base template)
3. VM boots — user doesn't wait (async via cron)
4. Cron polls `/status` for guest IP address
5. SSH console registered via `/ttyd/start`
6. Service marked as "running" with web terminal URL

## Database Schema

17 tables organized into five groups:

| Group | Tables |
|-------|--------|
| **Users & Auth** | `users`, `rate_limits` |
| **Catalog** | `products`, `product_promotions`, `reviews` |
| **Commerce** | `cart`, `orders`, `order_items`, `vouchers`, `voucher_usages`, `payments` |
| **Services** | `services`, `resource_metrics` |
| **Operations** | `audit_logs`, `admin_emails`, `notifications`, `order_status_history` |

7 triggers handle automation:
- Auto-tier calculation based on total spending
- Auto-confirm order on payment success
- Auto-log order status history
- Auto-notify on status changes
- Auto-increment voucher usage counter

---

## Security

- **CSRF protection** — random token on every form, verified server-side
- **Rate limiting** — IP-based: 5 login attempts/15 min, 3 registration attempts/60 min
- **Password hashing** — bcrypt via `password_hash()`
- **MFA** — RFC 6238 TOTP (Google Authenticator compatible), pure PHP implementation
- **Payment verification** — HMAC-SHA512 signature on all VNPay callbacks
- **Audit logging** — all admin actions logged with old/new values (JSON)
- **Session security** — `cookie_secure` and `cookie_httponly` in production
- **Account lockout** — admin-controlled user account locking

---

## Getting Started

### Prerequisites

- Docker and Docker Compose
- Node.js 18+ (for VM Bridge)
- VMware Workstation (for VM provisioning)
- ngrok account (for tunneling)

### Quick Start (Docker)

```bash
# Clone the repository
git clone https://github.com/your-username/astral-cloud.git
cd astral-cloud

# Copy and configure environment
cp .env.example .env
nano .env  # fill in your values

# Start the stack
docker compose up -d

# Initialize database
docker exec -i astral_db mysql -u root -p < sql/init.sql
```

The app will be available at `http://localhost:8080` and phpMyAdmin at `http://localhost:8081`.

### VM Bridge Setup

```bash
# On Windows with VMware installed
cd vm-bridge
npm install
node server.js
```

The bridge listens on port 10001 and provides:
- `GET /provision?order_id=&item_id=&name=&password=` — clone a VM
- `GET /status?name=` — get guest IP address
- `GET /start?name=` — start a VM
- `GET /stop?name=` — stop a VM
- `GET /restart?name=` — reset a VM
- `GET /rebuild?name=&password=` — destroy and re-clone
- `GET /ttyd/start?service_id=&ip=&name=&password=` — register web console
- `GET /ttyd/stop?service_id=` — remove console
- `GET /resources?ip=&password=` — SSH into VM for metrics

### Expose VM Bridge via ngrok

```bash
ngrok http http://127.0.0.1:10001
```

Update `.env` with the ngrok URL:
```
VM_BRIDGE_URL=https://xxxx.ngrok-free.dev
TTYD_EXTERNAL_URL=https://xxxx.ngrok-free.dev
```

### Default Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@astralcloud.com | password |
| Staff | staff@astralcloud.com | password |
| User | user1@gmail.com | password |

---

## Production Deployment

The project is deployed on a DigitalOcean VPS ($6/month) at [astralcloud.shop](https://astralcloud.shop).

### Deployment Steps

```bash
# 1. Install LAMP stack
sudo apt update && sudo apt upgrade -y
sudo apt install -y apache2 mysql-server php8.4 php8.4-cli \
  php8.4-mysql php8.4-zip php8.4-dom php8.4-mbstring \
  php8.4-curl php8.4-xml libapache2-mod-php unzip curl git

# 2. Enable Apache modules
sudo a2enmod rewrite proxy proxy_http proxy_wstunnel
sudo systemctl restart apache2

# 3. Secure MySQL and create database
sudo mysql_secure_installation
sudo mysql -e "CREATE DATABASE astral_cloud CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'astral_user'@'localhost' IDENTIFIED BY 'your_password';"
sudo mysql -e "GRANT ALL PRIVILEGES ON astral_cloud.* TO 'astral_user'@'localhost'; FLUSH PRIVILEGES;"

# 4. Deploy code
sudo mkdir -p /var/www/astralcloud
sudo chown -R $USER:$USER /var/www/astralcloud
rsync -avz --exclude='vendor/' --exclude='.git/' --exclude='.env' \
  --exclude='vm-bridge/' ./ root@YOUR_VPS_IP:/var/www/astralcloud/

# 5. Install dependencies
cd /var/www/astralcloud
composer install --no-dev --optimize-autoloader

# 6. Configure environment
cp .env.example .env
nano .env  # set DB credentials, APP_URL, VNPay keys, etc.

# 7. Initialize database
sudo mysql astral_cloud < sql/init.sql

# 8. Configure Apache virtual host
sudo nano /etc/apache2/sites-available/astralcloud.conf
sudo a2dissite 000-default.conf && sudo a2ensite astralcloud.conf
sudo systemctl reload apache2

# 9. Set permissions
sudo chown -R www-data:www-data /var/www/astralcloud
sudo chmod -R 755 /var/www/astralcloud

# 10. SSL
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# 11. Cron
(crontab -l 2>/dev/null; echo "*/2 * * * * /usr/bin/php /var/www/astralcloud/cron/cron.php all >> /var/log/astral-cron.log 2>&1") | crontab -
```

---

## Project Structure

```
astral-cloud/
├── src/
│   ├── index.php              # Entry point, routing, helpers
│   ├── Router.php             # Minimalist URL router
│   ├── .htaccess              # Apache rewrite rules
│   ├── config/
│   │   ├── db.php             # .env loader + PDO singleton
│   │   └── vnpay.php          # VNPay payment URL builder + verifier
│   ├── Controllers/           # 17 controllers
│   │   ├── AuthController.php
│   │   ├── CheckoutController.php
│   │   ├── PaymentController.php
│   │   ├── ServiceController.php
│   │   ├── ConsoleController.php
│   │   └── Admin/
│   ├── Models/                # 14 model classes
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Cart.php
│   │   ├── Order.php
│   │   ├── Payment.php
│   │   ├── Service.php
│   │   ├── Voucher.php
│   │   ├── Review.php
│   │   ├── Report.php
│   │   ├── AuditLog.php
│   │   ├── RateLimiter.php
│   │   ├── MfaHelper.php      # RFC 6238 TOTP
│   │   ├── TtydHelper.php      # Console lifecycle
│   │   └── MailHelper.php     # SMTP with PHPMailer
│   ├── Views/                 # PHP templates
│   │   ├── layouts/
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── auth/
│   │   ├── products/
│   │   ├── cart/
│   │   ├── checkout/
│   │   ├── orders/
│   │   ├── console/
│   │   ├── inbox/
│   │   ├── profile/
│   │   └── admin/
│   ├── css/
│   └── js/
│       ├── app.js             # Main interactions + AJAX
│       └── cart.js            # Cart operations
├── vm-bridge/
│   ├── server.js              # VMware provisioning + WebSocket SSH
│   ├── package.json
│   └── .env.example
├── sql/
│   ├── init.sql               # Full schema + seed data
│   └── migration_*.sql        # Incremental schema changes
├── cron/
│   └── cron.php               # Maintenance tasks runner
├── docker-compose.yml
├── Dockerfile
└── .env.example
```

---

## Cron Tasks

Run every 2 minutes via `cron.php`:

| Task | Description |
|------|-------------|
| `service-expiry` | Suspend services past their expiry date |
| `pending-orders` | Cancel orders stuck in "pending" over 24 hours |
| `service-reminders` | Notify users 7/3/1 days before service expiry |
| `complete-provisioning` | Poll for VM IPs, register SSH consoles for new VMs |
| `prune-rate-limits` | Clean up expired rate limit entries |
| `collect-metrics` | SSH into running VMs, collect CPU/RAM/Disk/Network stats |

---

## License

This project is developed for educational purposes as a university project.
