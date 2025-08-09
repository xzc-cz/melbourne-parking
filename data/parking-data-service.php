<?php
require_once __DIR__ . '/socrata-client.php';
require_once __DIR__ . '/ods-client.php';

/**
 * Aggregates real data from City of Melbourne SODA API, with graceful fallback to local demo dataset
 */

/**
 * Fetch real-time sensor status list
 * @param int $limit
 * @return array<int,array>
 */
function fetch_real_time_sensors(int $limit = 2000): array {
    // 1) Try ODS Explore API
    $odsDataset = defined('ODS_DS_SENSORS') ? ODS_DS_SENSORS : 'on-street-parking-bay-sensors';
    if ($odsDataset) {
        // Page manually with conservative page size to avoid platform caps/limits
        $all = [];
        $offset = 0;
        $pageSize = 100; // ODS commonly caps at 100 per page
        while (true) {
            $chunk = ods_get_records($odsDataset, [
                'limit' => $pageSize,
                'offset' => $offset,
                'timezone' => 'Australia/Sydney',
            ]);
            if (!is_array($chunk) || count($chunk) === 0) {
                break;
            }
            foreach ($chunk as $row) { $all[] = $row; }
            $offset += count($chunk);
            if ($offset >= 20000) { // hard safety cap
                break;
            }
            // be nice to API
            usleep(150000);
        }
        if (count($all) > 0) return $all;
        // Fallback: probe small sample
        $probe = ods_get_records($odsDataset, ['limit' => 20, 'timezone' => 'Australia/Sydney']);
        if (is_array($probe) && count($probe) > 0) return $probe;
    }

    // 2) Try Socrata (if configured)
    $resourceId = defined('SODA_RS_SENSORS') ? SODA_RS_SENSORS : '';
    if ($resourceId) {
        $rows = socrata_get($resourceId, [
            '$limit' => $limit,
        ]);
        if (is_array($rows) && count($rows) > 0) return $rows;
    }

    return [];
}

/**
 * Fetch parking bays static info (rates, restrictions, etc.)
 * @param int $limit
 * @return array<int,array>
 */
function fetch_parking_bays(int $limit = 5000): array {
    // 1) Try ODS Explore API
    $odsDataset = defined('ODS_DS_BAYS') ? ODS_DS_BAYS : 'on-street-parking-bays';
    if ($odsDataset) {
        $rows = ods_get_all_records($odsDataset, min(1000, $limit > 0 ? $limit : 1000), 50000);
        if (is_array($rows) && count($rows) > 0) return $rows;
    }

    // 2) Try Socrata (if configured)
    $resourceId = defined('SODA_RS_BAYS') ? SODA_RS_BAYS : '';
    if ($resourceId) {
        $rows = socrata_get($resourceId, [
            '$limit' => $limit,
        ]);
        if (is_array($rows) && count($rows) > 0) return $rows;
    }

    return [];
}

/**
 * Merge sensors with bays info for UI
 * Will try to map by bay_id or closest match available in dataset
 */
function build_parking_spots_from_api(): array {
    $sensors = fetch_real_time_sensors();
    $bays = fetch_parking_bays();

    $bayIndexById = [];
    foreach ($bays as $bay) {
        $key = isset($bay['bay_id']) ? $bay['bay_id'] : (isset($bay['bayid']) ? $bay['bayid'] : null);
        if ($key) {
            $bayIndexById[$key] = $bay;
        }
    }

    $spots = [];
    foreach ($sensors as $row) {
        // Normalize fields from either ODS or Socrata
        $sensorId = $row['sensor_id'] ?? $row['device_id'] ?? $row['kerbsideid'] ?? null;
        $bayId = $row['bay_id'] ?? $row['bayid'] ?? null;
        $streetName = $row['street_name'] ?? $row['road_name'] ?? ($row['street'] ?? '');

        // Status mapping
        $statusRaw = $row['status'] ?? $row['occupancystatus'] ?? $row['status_description'] ?? 'unknown';
        $statusLower = strtolower((string)$statusRaw);
        $status = $statusLower;
        if ($statusLower === 'unoccupied') { $status = 'vacant'; }
        if ($statusLower === 'present') { $status = 'occupied'; }

        // Coordinates from either client helpers
        $coord = ods_extract_lat_lng($row);
        if (!$coord['lat'] || !$coord['lng']) {
            $coord = socrata_extract_lat_lng($row);
        }
        $lat = $coord['lat'];
        $lng = $coord['lng'];

        // If lat/lng missing on sensor, try from matched bay
        if ((!$lat || !$lng) && $bayId && isset($bayIndexById[$bayId])) {
            $coordBay = socrata_extract_lat_lng($bayIndexById[$bayId]);
            $lat = $lat ?: $coordBay['lat'];
            $lng = $lng ?: $coordBay['lng'];
        }

        $matchedBay = $bayId && isset($bayIndexById[$bayId]) ? $bayIndexById[$bayId] : null;
        $rate = $matchedBay['rate'] ?? ($matchedBay['price'] ?? ($matchedBay['fee'] ?? 'N/A'));
        $zone = $row['zone'] ?? ($row['zone_number'] ?? ($matchedBay['zone_name'] ?? ($matchedBay['zone'] ?? 'Unknown')));

        $spots[] = [
            'id' => (string)($sensorId ?? $bayId ?? ''),
            'name' => trim(($streetName ?: 'Parking Bay') . ' Bay'),
            'lat' => $lat,
            'lng' => $lng,
            'available' => ($status === 'vacant' || $status === 'free' || $status === 'available') ? 1 : 0,
            'total' => 1,
            'price' => is_string($rate) ? $rate : (is_numeric($rate) ? ('$' . number_format((float)$rate, 2) . '/hour') : 'N/A'),
            'zone' => $zone,
            'sensor_id' => (string)($sensorId ?? ''),
            'status' => $status,
            'last_updated' => $row['last_updated'] ?? ($row['lastupdated'] ?? ($row['last_update'] ?? ($row['status_timestamp'] ?? ''))),
        ];
    }

    // Filter entries without coordinates
    $spots = array_values(array_filter($spots, function ($s) {
        return isset($s['lat'], $s['lng']) && $s['lat'] && $s['lng'];
    }));

    return $spots;
}

/**
 * Build spots directly from ODS Bays dataset (no live status)
 */
function build_parking_spots_from_bays_api(): array {
    $bays = fetch_parking_bays();
    $spots = [];
    foreach ($bays as $row) {
        $coord = ods_extract_lat_lng($row);
        $lat = $coord['lat'];
        $lng = $coord['lng'];
        if (!$lat || !$lng) continue;

        $name = $row['roadsegmentdescription'] ?? ($row['street_name'] ?? 'Parking Bay');
        $lastUpdated = $row['lastupdated'] ?? '';

        $spots[] = [
            'id' => (string)($row['roadsegmentid'] ?? uniqid('bay_')),
            'name' => (string)$name,
            'lat' => $lat,
            'lng' => $lng,
            'available' => 0,
            'total' => 1,
            'price' => 'N/A',
            'zone' => (string)($row['zone_name'] ?? ($row['zone'] ?? '')),
            'sensor_id' => '',
            'status' => 'unknown',
            'last_updated' => $lastUpdated,
        ];
    }
    return $spots;
}

/**
 * Unified entry: sensors (default) or bays
 * @param string $mode 'sensors'|'bays'
 * @return array{spots: array, stats: array}
 */
function get_parking_dataset(string $mode = 'sensors'): array {
    if ($mode === 'bays') {
        $spots = build_parking_spots_from_bays_api();
        $total = count($spots);
        return [
            'spots' => $spots,
            'stats' => [
                'total' => $total,
                'vacant' => 0,
                'occupied' => 0,
                'fault' => 0,
                'availability_percentage' => 0,
            ],
        ];
    }

    // default sensors
    return get_parking_real_time_dataset();
}

/**
 * High-level aggregator used by UI (with fallback)
 * @return array{spots: array, stats: array}
 */
function get_parking_real_time_dataset(): array {
    $spots = build_parking_spots_from_api();
    if (empty($spots)) {
        // Fallback to local demo dataset
        global $parking_sensors, $parking_bays, $historical_patterns, $zone_statistics;
        if (!isset($GLOBALS['parking_sensors']) || !is_array($GLOBALS['parking_sensors'])) {
            // Only include if not already loaded in global scope
            require_once __DIR__ . '/melbourne-parking-data.php';
        }
        $parking_sensors = $GLOBALS['parking_sensors'] ?? [];

        $spots = [];
        foreach ($parking_sensors as $sensor) {
            $spots[] = [
                'id' => $sensor['sensor_id'],
                'name' => $sensor['street_name'] . ' Parking Bay',
                'lat' => $sensor['lat'],
                'lng' => $sensor['lng'],
                'available' => $sensor['status'] === 'vacant' ? 1 : 0,
                'total' => 1,
                'price' => 'N/A',
                'zone' => $sensor['zone'],
                'sensor_id' => $sensor['sensor_id'],
                'status' => $sensor['status'],
                'last_updated' => $sensor['last_updated'],
            ];
        }
    }

    // Prefer authoritative total from ODS count(1)
    $total = 0;
    if (defined('ODS_DS_SENSORS')) {
        $total = function_exists('ods_get_total_count') ? ods_get_total_count(ODS_DS_SENSORS) : 0;
    }
    if (!$total) {
        $total = count($spots);
    }
    $vacant = 0; $occupied = 0; $fault = 0;
    foreach ($spots as $s) {
        if ($s['status'] === 'fault') $fault++;
        elseif (!empty($s['available'])) $vacant++; else $occupied++;
    }

    return [
        'spots' => $spots,
        'stats' => [
            'total' => $total,
            'vacant' => $vacant,
            'occupied' => $occupied,
            'fault' => $fault,
            'availability_percentage' => $total ? round($vacant / $total * 100, 1) : 0,
        ],
    ];
}

?>


