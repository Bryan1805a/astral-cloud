<?php

class TtydHelper {
    private static function getBridgeUrl(): string {
        return getenv('VM_BRIDGE_URL') ?: 'http://host.docker.internal:10001';
    }

    /**
     * Get the external base URL for the console.
     *
     * The console is proxied through the PHP app's Apache
     * ( /console/{id}/ → host.docker.internal:10001 ),
     * so we use the APP_URL directly — no separate tunnel needed.
     *
     * Order of precedence:
     * 1. TTYD_EXTERNAL_URL (explicit override, e.g. separate ngrok tunnel)
     * 2. APP_URL (console proxied through Apache)
     */
    private static function getExternalBridgeUrl(): string {
        $override = getenv('TTYD_EXTERNAL_URL');
        if ($override) {
            return rtrim($override, '/');
        }

        return rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/');
    }

    // ── Console lifecycle ─────────────────────────────────────

    public static function startConsole(int $serviceId, string $ip, string $hostname): ?int {
        $bridgeUrl = self::getBridgeUrl();
        $url = $bridgeUrl . '/ttyd/start'
            . '?service_id=' . $serviceId
            . '&ip=' . urlencode($ip)
            . '&name=' . urlencode($hostname);

        $ctx = stream_context_create(['http' => ['timeout' => 30]]);
        $response = @file_get_contents($url, false, $ctx);

        if ($response === false) {
            error_log("TtydHelper: failed to start console for service #{$serviceId}");
            return null;
        }

        $data = json_decode($response, true);
        if ($data && !empty($data['success']) && !empty($data['port'])) {
            return (int) $data['port'];
        }

        error_log("TtydHelper: unexpected response for service #{$serviceId}: " . ($response ?? 'null'));
        return null;
    }

    public static function stopConsole(int $serviceId): void {
        $bridgeUrl = self::getBridgeUrl();
        $url = $bridgeUrl . '/ttyd/stop?service_id=' . $serviceId;
        $ctx = stream_context_create(['http' => ['timeout' => 10]]);
        @file_get_contents($url, false, $ctx);
    }

    /**
     * Generate the external console URL.
     * Traffic goes through the VM Bridge proxy at /console/{serviceId}/
     * which forwards HTTP + WebSocket to the actual ttyd instance.
     */
    public static function generateConsoleUrl(int $serviceId): string {
        $base = self::getExternalBridgeUrl();
        return "{$base}/console/{$serviceId}/";
    }

    // ── DB helpers ────────────────────────────────────────────

    public static function getConsolePort(int $serviceId): ?int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT console_port FROM services WHERE id = ?");
        $stmt->execute([$serviceId]);
        $row = $stmt->fetch();
        if ($row && $row['console_port'] > 0) {
            return (int) $row['console_port'];
        }
        return null;
    }

    public static function getProvisioningStatus(int $serviceId): string {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT provisioning_status FROM services WHERE id = ?");
        $stmt->execute([$serviceId]);
        $row = $stmt->fetch();
        return $row['provisioning_status'] ?? 'unknown';
    }
}
