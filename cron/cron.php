<?php
/**
 * Astral Cloud — Cron Job Runner
 *
 * Runs every 2 minutes via docker-compose cron container.
 * Tasks are idempotent — safe to run repeatedly.
 *
 * Tasks:
 *   service-expiry        Suspend services past their expiry_date
 *   pending-orders        Cancel orders stuck in "pending" > 24 hours
 *   service-reminders     Notify users 7/3/1 day(s) before service expiry
 *   complete-provisioning Retry VM provisioning (poll for IP, register console)
 *   prune-rate-limits     Delete expired rate-limit rows
 *   all                   Run everything above
 *
 * Usage: php cron/cron.php <task>
 */

if (PHP_SAPI !== "cli") {
    die("This script can only be run from the command line.\n");
}

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/config/db.php";

// ---- Bootstrap ----
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . "/../src/Controllers/" . $class . ".php",
        __DIR__ . "/../src/Models/" . $class . ".php",
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// ---- Helpers ----
function cronLog(string $message): void {
    $ts = date("Y-m-d H:i:s");
    echo "[{$ts}] {$message}\n";
}

function runTask(string $name, callable $fn): void {
    cronLog("Starting task: {$name}");
    try {
        $fn();
        cronLog("Completed task: {$name}");
    } catch (Throwable $e) {
        cronLog("ERROR in task {$name}: " . $e->getMessage());
    }
}

// ---- Task: Service Expiry ----
function taskServiceExpiry(): void {
    $pdo = Database::getConnection();

    // Suspend services past expiry (not already suspended/terminated)
    $stmt = $pdo->prepare("
        SELECT id, user_id, hostname
        FROM services
        WHERE expiry_date < CURDATE()
          AND status NOT IN ('suspended', 'terminated')
    ");
    $stmt->execute();
    $expired = $stmt->fetchAll();

    foreach ($expired as $s) {
        $update = $pdo->prepare("UPDATE services SET status = 'suspended' WHERE id = ?");
        $update->execute([$s["id"]]);
        AuditLog::logSystem("cron.service_suspend", "service", $s["id"],
            "Cron: Suspended service {$s["hostname"]} (past expiry date)"
        );
        cronLog("  Suspended service #{$s["id"]} ({$s["hostname"]})");
    }

    if (empty($expired)) {
        cronLog("  No expired services found.");
    }
}

// ---- Task: Pending Orders Cleanup ----
function taskPendingOrders(): void {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("
        SELECT id, user_id
        FROM orders
        WHERE status = 'pending'
          AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute();
    $stale = $stmt->fetchAll();

    foreach ($stale as $o) {
        $pdo->prepare("UPDATE orders SET status = 'cancelled', cancel_reason = 'Auto-cancelled (pending > 24h)' WHERE id = ?")
            ->execute([$o["id"]]);
        AuditLog::logSystem("cron.cancel_pending_order", "order", $o["id"],
            "Cron: Auto-cancelled stale pending order #{$o["id"]}"
        );
        cronLog("  Cancelled stale order #{$o["id"]}");
    }

    if (empty($stale)) {
        cronLog("  No stale pending orders found.");
    }
}

// ---- Task: Service Expiry Reminders ----
function taskServiceReminders(): void {
    $pdo = Database::getConnection();

    // Notify for services expiring in 7, 3, or 1 day
    $intervals = [7, 3, 1];
    foreach ($intervals as $days) {
        $stmt = $pdo->prepare("
            SELECT s.id, s.user_id, s.hostname, s.expiry_date, oi.product_name
            FROM services s
            JOIN order_items oi ON s.order_item_id = oi.id
            WHERE s.expiry_date = DATE_ADD(CURDATE(), INTERVAL ? DAY)
              AND s.status = 'running'
        ");
        $stmt->execute([$days]);
        $due = $stmt->fetchAll();

        foreach ($due as $s) {
            // Check if notification already sent today to avoid duplicates
            $check = $pdo->prepare("
                SELECT id FROM notifications
                WHERE user_id = ? AND type = 'service'
                  AND title LIKE ?
                  AND DATE(created_at) = CURDATE()
            ");
            $check->execute([$s["user_id"], "%Service #{$s["id"]}%"]);
            if ($check->fetch()) continue;

            $pdo->prepare("
                INSERT INTO notifications (user_id, type, title, message, link)
                VALUES (?, 'service', ?, ?, ?)
            ")->execute([
                $s["user_id"],
                "Service #{$s["id"]} expiring in {$days} day(s)",
                "Your {$s["product_name"]} ({$s["hostname"]}) will expire on {$s["expiry_date"]}. Please renew to avoid suspension.",
                "/orders",
            ]);

            cronLog("  Reminder sent for service #{$s["id"]} ({$s["hostname"]}) - expires in {$days} day(s)");
        }

        if (empty($due)) {
            cronLog("  No services expiring in {$days} day(s).");
        }
    }
}

// ---- Task: Complete Pending VM Provisionings ----
function taskCompleteProvisioning(): void {
    cronLog("Checking for pending VM provisionings...");
    Service::completePendingProvisionings();
}

// ---- Task: Prune Expired Rate Limits ----
function taskPruneRateLimits(): void {
    $count = RateLimiter::pruneExpired();
    cronLog("  Pruned {$count} expired rate limit row(s).");
}

// ---- Dispatch ----
$task = $argv[1] ?? "all";

$tasks = [
    "service-expiry"           => "taskServiceExpiry",
    "pending-orders"           => "taskPendingOrders",
    "service-reminders"        => "taskServiceReminders",
    "complete-provisioning"    => "taskCompleteProvisioning",
    "prune-rate-limits"        => "taskPruneRateLimits",
];

if ($task === "all") {
    cronLog("=== Astral Cloud Cron: Running all tasks ===");
    foreach ($tasks as $name => $fn) {
        runTask($name, $fn);
    }
    cronLog("=== All tasks complete ===");
} elseif (isset($tasks[$task])) {
    runTask($task, $tasks[$task]);
} else {
    echo "Unknown task: {$task}\n";
    echo "Available tasks: " . implode(", ", array_keys($tasks)) . ", all\n";
    exit(1);
}
