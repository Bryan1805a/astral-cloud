<?php

/**
 * HealthController — system health checks
 *
 * GET /api/health returns JSON with:
 *   - database: { ok, error? }
 *   - vm_bridge: { ok, url, error? }
 *   - timestamp: ISO 8601
 */
class HealthController {
    public function index(): void {
        $checks = [
            'database'  => $this->checkDatabase(),
            'vm_bridge' => $this->checkVmBridge(),
            'timestamp' => date('c'),
        ];

        $allOk = $checks['database']['ok'] && $checks['vm_bridge']['ok'];
        $status = $allOk ? 200 : 503;

        jsonResponse($checks, $status);
    }

    private function checkDatabase(): array {
        try {
            $pdo = Database::getConnection();
            $pdo->query("SELECT 1");
            return ['ok' => true];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function checkVmBridge(): array {
        $url = getenv('VM_BRIDGE_URL') ?: 'http://host.docker.internal:10001';

        $ctx = stream_context_create(['http' => ['timeout' => 5]]);
        $result = @file_get_contents($url . '/ttyd/status?service_id=0', false, $ctx);

        if ($result !== false) {
            return ['ok' => true, 'url' => $url];
        }

        return ['ok' => false, 'url' => $url, 'error' => 'unreachable'];
    }
}
