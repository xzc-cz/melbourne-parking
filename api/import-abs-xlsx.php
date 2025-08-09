<?php
// Import ABS Regional population Excel into database
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../data/db.php';
require_once __DIR__ . '/../data/xlsx_reader.php';

$xlsxPath = __DIR__ . '/../message/Australian Bureau of Statistics (ABS) Regional population 2021.xlsx';
if (!is_file($xlsxPath)) {
    echo json_encode(['ok' => false, 'error' => 'XLSX not found']);
    exit;
}

$rows = xlsx_read_rows($xlsxPath, 0);
if (empty($rows)) {
    echo json_encode(['ok' => false, 'error' => 'Failed to read XLSX']);
    exit;
}

// Heuristic: find header row by detecting known column fragments
$headerIdx = -1;
foreach ($rows as $i => $r) {
    $line = strtolower(implode(' ', $r));
    if (strpos($line, 'region') !== false || strpos($line, 'gccsa') !== false || strpos($line, '2016') !== false) {
        $headerIdx = $i; break;
    }
}
if ($headerIdx < 0) $headerIdx = 0;

$headers = $rows[$headerIdx];

$pdo = mp_get_pdo();
$pdo->exec('CREATE TABLE IF NOT EXISTS abs_regional_population (
    id INT AUTO_INCREMENT PRIMARY KEY,
    c0 VARCHAR(255) NULL,
    c1 VARCHAR(255) NULL,
    c2 VARCHAR(255) NULL,
    c3 VARCHAR(255) NULL,
    c4 VARCHAR(255) NULL,
    c5 VARCHAR(255) NULL,
    c6 VARCHAR(255) NULL,
    c7 VARCHAR(255) NULL,
    c8 VARCHAR(255) NULL,
    c9 VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

$pdo->exec('TRUNCATE TABLE abs_regional_population');

$insert = $pdo->prepare('INSERT INTO abs_regional_population (c0,c1,c2,c3,c4,c5,c6,c7,c8,c9) VALUES (?,?,?,?,?,?,?,?,?,?)');

$imported = 0;
for ($i = $headerIdx + 1; $i < count($rows); $i++) {
    $r = $rows[$i];
    if (count($r) === 0) continue;
    // Normalize to 10 cols max
    $vals = array_slice(array_map(function($v){ return trim((string)$v); }, $r), 0, 10);
    while (count($vals) < 10) $vals[] = null;
    $insert->execute($vals);
    $imported++;
}

echo json_encode([
    'ok' => true,
    'headers_detected' => $headers,
    'rows_imported' => $imported,
]);


