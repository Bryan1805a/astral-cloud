<?php

class GuacamoleHelper {
    private static function getBaseUrl(): string {
        return getenv('GUAC_URL') ?: 'http://guacamole:8080/guacamole';
    }

    private static function login(): string {
        $url = self::getBaseUrl() . '/api/tokens';
        $data = [
            'username' => getenv('GUAC_ADMIN_USER') ?: 'guacadmin',
            'password' => getenv('GUAC_ADMIN_PASS') ?: 'guacadmin',
        ];

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($data),
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            throw new Exception('Guacamole: failed to authenticate');
        }

        $result = json_decode($response, true);
        if (!isset($result['authToken'])) {
            throw new Exception('Guacamole: no authToken in response');
        }
        return $result['authToken'];
    }

    public static function createConnection(string $name, string $hostname, int $port = 22, string $username = 'root'): ?int {
        try {
            $token = self::login();
        } catch (Exception $e) {
            error_log("GuacamoleHelper: " . $e->getMessage());
            return null;
        }

        $url = self::getBaseUrl() . '/api/session/data/mysql/connections?token=' . urlencode($token);

        $body = json_encode([
            'name' => $name,
            'protocol' => 'ssh',
            'parameters' => [
                'hostname' => $hostname,
                'port' => (string)$port,
                'username' => $username,
                'color-scheme' => 'gray-black',
                'font-name' => 'monospace',
                'font-size' => '12',
                'scrollback' => '2000',
                'enable-sftp' => 'true',
            ],
        ]);

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/json\r\n",
                'content' => $body,
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            error_log("GuacamoleHelper: failed to create connection '$name'");
            return null;
        }

        return (int) trim($response);
    }

    public static function deleteConnection(int $connectionId): void {
        try {
            $token = self::login();
        } catch (Exception $e) {
            error_log("GuacamoleHelper: " . $e->getMessage());
            return;
        }

        $url = self::getBaseUrl() . '/api/session/data/mysql/connections/' . $connectionId . '?token=' . urlencode($token);

        $ctx = stream_context_create([
            'http' => [
                'method' => 'DELETE',
                'timeout' => 10,
            ],
        ]);

        @file_get_contents($url, false, $ctx);
    }

    public static function generateConsoleUrl(int $connectionId): ?string {
        try {
            $token = self::login();
        } catch (Exception $e) {
            error_log("GuacamoleHelper: " . $e->getMessage());
            return null;
        }

        $guacUrl = getenv('GUAC_EXTERNAL_URL');
        if (!$guacUrl) {
            $appUrl = rtrim(getenv('APP_URL') ?: 'http://localhost:8080', '/');
            $host = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';
            $guacPort = getenv('GUAC_PORT') ?: '8082';
            $guacUrl = "http://{$host}:{$guacPort}/guacamole";
        }

        return rtrim($guacUrl, '/') . "/#/client/{$connectionId}?token={$token}";
    }
}
