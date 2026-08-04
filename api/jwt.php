<?php
require_once __DIR__ . '/config.php';

class JWT {
    private static function base64UrlEncode($text) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }

    private static function base64UrlDecode($text) {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $text));
    }

    public static function generate($payload, $expirySeconds = 86400) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = time() + $expirySeconds;
        $payload['iat'] = time();
        $payloadStr = json_encode($payload);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payloadStr);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function verify($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        list($header, $payload, $signature) = $parts;
        $validSignature = self::base64UrlEncode(hash_hmac('sha256', $header . "." . $payload, JWT_SECRET, true));

        if ($signature !== $validSignature) return false;

        $payloadData = json_decode(self::base64UrlDecode($payload), true);
        if (isset($payloadData['exp']) && $payloadData['exp'] < time()) return false;

        return $payloadData;
    }

    public static function getAuthUser() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            return self::verify($token);
        }
        return false;
    }
}
?>
