<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../data/parking-data-service.php';
require_once __DIR__ . '/../data/ods-client.php';

$t0 = microtime(true);

// Try ODS total first
$odsDataset = defined('ODS_DS_SENSORS') ? ODS_DS_SENSORS : 'on-street-parking-bay-sensors';
$odsTotal = 0;
if ($odsDataset && function_exists('ods_get_total_count')) {
    $odsTotal = ods_get_total_count($odsDataset);
}

// Fetch raw sensors (may paginate internally)
$rawSensors = fetch_real_time_sensors(0);
$rawCount = is_array($rawSensors) ? count($rawSensors) : 0;

// Build display spots (with coord filter)
$spots = build_parking_spots_from_api();
$spotsCount = count($spots);

// Roughly infer source
$source = 'fallback';
if ($rawCount > 0) {
    $source = 'ODS';
} elseif (defined('SODA_RS_SENSORS') && SODA_RS_SENSORS) {
    $source = 'Socrata_or_fallback';
}

$filteredOut = max(0, $rawCount - $spotsCount);

echo json_encode([
    'source' => $source,
    'ods_dataset' => $odsDataset,
    'ods_total_count' => $odsTotal,
    'raw_sensors_count' => $rawCount,
    'spots_count_rendered' => $spotsCount,
    'filtered_out_no_coords' => $filteredOut,
    'sample_raw' => array_slice($rawSensors, 0, 3),
    'sample_spots' => array_slice($spots, 0, 3),
    'duration_ms' => round((microtime(true) - $t0) * 1000),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


