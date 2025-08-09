<?php
// Simple search endpoint for parking spots
// Query params: location, price (low|medium|high), availability (high|medium|low)

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../data/parking-data-service.php';

$location = isset($_GET['location']) ? trim((string)$_GET['location']) : '';
$price = isset($_GET['price']) ? trim((string)$_GET['price']) : '';
$availability = isset($_GET['availability']) ? trim((string)$_GET['availability']) : '';

$mode = isset($_GET['mode']) && $_GET['mode'] === 'bays' ? 'bays' : 'sensors';
$dataset = get_parking_dataset($mode);
$spots = $dataset['spots'];

// Helpers
$normalize_price = function ($priceText) {
    if (!is_string($priceText) || $priceText === '' || strtolower($priceText) === 'n/a') return null;
    if (preg_match('/([0-9]+(?:\.[0-9]+)?)\s*\/?\s*hour/i', $priceText, $m)) {
        return (float)$m[1];
    }
    if (preg_match('/\$\s*([0-9]+(?:\.[0-9]+)?)/', $priceText, $m)) {
        return (float)$m[1];
    }
    return null;
};

// Apply filters
$filtered = array_filter($spots, function ($s) use ($location, $price, $availability, $normalize_price) {
    // location: naive contains on name/zone
    if ($location !== '') {
        $hay = strtolower(($s['name'] ?? '') . ' ' . ($s['zone'] ?? ''));
        if (strpos($hay, strtolower($location)) === false) {
            return false;
        }
    }

    // price range: under 4, 4-5, over 5
    if ($price !== '') {
        $v = $normalize_price($s['price'] ?? '');
        if ($price === 'low') {
            if (!is_numeric($v) || $v >= 4.0) return false;
        } elseif ($price === 'medium') {
            if (!is_numeric($v) || $v < 4.0 || $v > 5.0) return false;
        } elseif ($price === 'high') {
            if (!is_numeric($v) || $v <= 5.0) return false;
        }
    }

    // availability: map to spot-level
    // high => available == 1, low => available == 0, medium => keep all
    if ($availability === 'high' && empty($s['available'])) return false;
    if ($availability === 'low' && !empty($s['available'])) return false;

    return true;
});

$filtered = array_values($filtered);

// Recompute stats
$total = count($filtered);
$vacant = 0; $occupied = 0; $fault = 0;
foreach ($filtered as $s) {
    if (($s['status'] ?? '') === 'fault') $fault++;
    elseif (!empty($s['available'])) $vacant++; else $occupied++;
}

echo json_encode([
    'spots' => $filtered,
    'stats' => [
        'total' => $total,
        'vacant' => $vacant,
        'occupied' => $occupied,
        'fault' => $fault,
        'availability_percentage' => $total ? round($vacant / $total * 100, 1) : 0,
    ],
]);


