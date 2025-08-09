<?php
/**
 * Simple Socrata (SODA) API client with filesystem caching
 */

// Optional local config override
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

if (!defined('SODA_DOMAIN')) {
    define('SODA_DOMAIN', 'data.melbourne.vic.gov.au');
}

if (!defined('SODA_APP_TOKEN')) {
    // Leave empty by default; you can set in data/config.php for higher rate limits
    define('SODA_APP_TOKEN', '');
}

if (!defined('SODA_CACHE_TTL')) {
    // 5 minutes default cache
    define('SODA_CACHE_TTL', 300);
}

/**
 * Perform a GET to Socrata API resource and return decoded JSON array
 * @param string $resourceId Socrata 4x4 ID, e.g. "vh2v-4nfs"
 * @param array $params e.g. ['$limit' => 1000, '$select' => '...']
 * @return array
 */
function socrata_get(string $resourceId, array $params = []): array {
    $baseUrl = 'https://' . SODA_DOMAIN . '/resource/' . rawurlencode($resourceId) . '.json';

    // Normalize params and build cache key
    ksort($params);
    $query = http_build_query($params);
    $cacheKey = sha1($resourceId . '?' . $query);
    $cacheDir = __DIR__ . '/cache';
    $cacheFile = $cacheDir . '/' . $cacheKey . '.json';

    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0775, true);
    }

    // Serve from cache if fresh
    if (is_file($cacheFile) && (time() - filemtime($cacheFile) < SODA_CACHE_TTL)) {
        $cached = file_get_contents($cacheFile);
        $decoded = json_decode($cached, true);
        return is_array($decoded) ? $decoded : [];
    }

    // Build URL
    $url = $baseUrl . ($query ? ('?' . $query) : '');

    // Prefer cURL if available
    $headers = [];
    if (SODA_APP_TOKEN) {
        $headers[] = 'X-App-Token: ' . SODA_APP_TOKEN;
    }

    $body = null;
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $resp = curl_exec($ch);
        if ($resp === false) {
            curl_close($ch);
            return [];
        }
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status >= 200 && $status < 300) {
            $body = $resp;
        } else {
            return [];
        }
    } else {
        $context = null;
        if (!empty($headers)) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => implode("\r\n", $headers)
                ]
            ]);
        }
        $resp = @file_get_contents($url, false, $context);
        if ($resp === false) {
            return [];
        }
        $body = $resp;
    }

    $data = json_decode($body, true);
    if (!is_array($data)) {
        return [];
    }

    // Write cache best-effort
    @file_put_contents($cacheFile, $body);

    return $data;
}

/**
 * Helper: safely extract latitude/longitude from common Socrata shapes
 * @param array $row
 * @return array{lat: float|null, lng: float|null}
 */
function socrata_extract_lat_lng(array $row): array {
    $lat = null; $lng = null;
    if (isset($row['latitude']) && isset($row['longitude'])) {
        $lat = (float)$row['latitude'];
        $lng = (float)$row['longitude'];
    } elseif (isset($row['lat']) && isset($row['lon'])) {
        $lat = (float)$row['lat'];
        $lng = (float)$row['lon'];
    } elseif (isset($row['location']) && is_array($row['location']) && isset($row['location']['coordinates'])) {
        // GeoJSON [lng, lat]
        $coords = $row['location']['coordinates'];
        if (is_array($coords) && count($coords) >= 2) {
            $lng = (float)$coords[0];
            $lat = (float)$coords[1];
        }
    }
    return ['lat' => $lat, 'lng' => $lng];
}

?>


