<?php

namespace App\Providers;

/**
 * Thin curl wrapper shared by every provider. Kept dependency-free (no Composer)
 * so it works unmodified on any cPanel shared-hosting PHP build.
 */
class HttpClient
{
    /**
     * @param array<string, string> $headers
     * @return array{ok: bool, status: int, body: array|null, error: string|null}
     */
    public static function getJson(string $url, array $headers = [], int $timeoutSeconds = 10): array
    {
        $ch = curl_init($url);
        $headerLines = [];
        foreach ($headers as $key => $value) {
            $headerLines[] = "{$key}: {$value}";
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_USERAGENT => 'GoldPriceTodaySriLanka/1.0 (+https://example.lk)',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch) ?: null;
        curl_close($ch);

        if ($body === false) {
            return ['ok' => false, 'status' => $status, 'body' => null, 'error' => $error ?: 'request_failed'];
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'status' => $status, 'body' => null, 'error' => 'invalid_json'];
        }

        return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'body' => $decoded, 'error' => null];
    }
}
