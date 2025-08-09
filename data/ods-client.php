<?php
/**
 * Simple Opendatasoft Explore v2.1 API client with filesystem caching
 * Docs: /api/explore/v2.1/catalog/datasets/{dataset}/records
 */

// Optional local config override
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

if (!defined('ODS_DOMAIN')) {
    define('ODS_DOMAIN', 'data.melbourne.vic.gov.au');
}

if (!defined('ODS_CACHE_TTL')) {
    define('ODS_CACHE_TTL', 300);
}

/**
 * GET records from ODS Explore API
 * @param string $dataset Dataset slug, e.g. 'on-street-parking-bays'
 * @param array $params Query params: 'limit','offset','select','where','order_by','group_by','refine','exclude','lang','timezone'
 * @return array<int,array> results array (flattened)
 */
function ods_get_records(string $dataset, array $params = []): array {
    $baseUrl = 'https://' . ODS_DOMAIN . '/api/explore/v2.1/catalog/datasets/' . rawurlencode($dataset) . '/records';
    ksort($params);
    $query = http_build_query($params);
    $cacheKey = sha1($dataset . '?' . $query);
    $cacheDir = __DIR__ . '/cache';
    $cacheFile = $cacheDir . '/ods_' . $cacheKey . '.json';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0775, true);
    }

    if (is_file($cacheFile) && (time() - filemtime($cacheFile) < ODS_CACHE_TTL)) {
        $cached = file_get_contents($cacheFile);
        $decoded = json_decode($cached, true);
        if (isset($decoded['results']) && is_array($decoded['results'])) {
            return $decoded['results'];
        }
        return [];
    }

    $url = $baseUrl . ($query ? ('?' . $query) : '');
    $resp = null;
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // In dev on Windows, SSL CA may be missing; relax verification to avoid failures
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'User-Agent: MelbourneParking/1.0 (+http://localhost/melbourne-parking)'
        ]);
        $resp = curl_exec($ch);
        if ($resp === false) {
            curl_close($ch);
            return [];
        }
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status < 200 || $status >= 300) {
            return [];
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\nUser-Agent: MelbourneParking/1.0 (+http://localhost/melbourne-parking)",
                'timeout' => 15,
            ]
        ]);
        $resp = @file_get_contents($url, false, $context);
        if ($resp === false) return [];
    }

    @file_put_contents($cacheFile, $resp);

    $decoded = json_decode($resp, true);
    if (isset($decoded['results']) && is_array($decoded['results'])) {
        return $decoded['results'];
    }
    return [];
}

/**
 * Fetch all records with basic pagination
 * @param string $dataset
 * @param int $pageLimit Number of rows per page (server may cap; 100-1000 reasonable)
 * @param int $maxRows Safety cap to avoid runaway
 * @return array<int,array>
 */
function ods_get_all_records(string $dataset, int $pageLimit = 1000, int $maxRows = 200000): array {
    $all = [];
    $offset = 0;
    while (true) {
        $rows = ods_get_records($dataset, [
            'limit' => $pageLimit,
            'offset' => $offset,
            'timezone' => 'Australia/Sydney',
        ]);
        if (empty($rows)) break;
        foreach ($rows as $r) { $all[] = $r; }
        $offset += count($rows);
        if ($offset >= $maxRows) break;
    }
    return $all;
}

/**
 * Get total_count using select=count(1)
 * @param string $dataset
 * @return int
 */
function ods_get_total_count(string $dataset): int {
    $rows = ods_get_records($dataset, [
        'select' => 'count(1) as c',
        'limit' => 1,
    ]);
    if (is_array($rows) && !empty($rows) && isset($rows[0]['c'])) {
        return (int)$rows[0]['c'];
    }
    return 0;
}

/**
 * Helper: extract lat/lng from ODS record
 */
function ods_extract_lat_lng(array $row): array {
    $lat = null; $lng = null;
    if (isset($row['latitude']) && isset($row['longitude'])) {
        $lat = (float)$row['latitude'];
        $lng = (float)$row['longitude'];
    } elseif (isset($row['location']) && is_array($row['location'])) {
        $loc = $row['location'];
        if (isset($loc['lat']) && isset($loc['lon'])) {
            $lat = (float)$loc['lat'];
            $lng = (float)$loc['lon'];
        }
    }
    return ['lat' => $lat, 'lng' => $lng];
}

?>


