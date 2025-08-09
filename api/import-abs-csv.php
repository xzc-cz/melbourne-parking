<?php
// Import ABS CSV into database
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../data/db.php';

$csvPath = __DIR__ . '/../message/Australian Bureau of Statistics.csv';
if (!is_file($csvPath)) {
    echo json_encode(['ok' => false, 'error' => 'CSV not found']);
    exit;
}

$pdo = mp_get_pdo();

// Create table
$pdo->exec('CREATE TABLE IF NOT EXISTS abs_population (
    id INT AUTO_INCREMENT PRIMARY KEY,
    state_code VARCHAR(16) NOT NULL,
    y2016_2017_no INT NULL,
    y2016_2017_pct DECIMAL(5,2) NULL,
    y2017_2018_no INT NULL,
    y2017_2018_pct DECIMAL(5,2) NULL,
    y2018_2019_no INT NULL,
    y2018_2019_pct DECIMAL(5,2) NULL,
    y2019_2020_no INT NULL,
    y2019_2020_pct DECIMAL(5,2) NULL,
    y2020_2021_no INT NULL,
    y2020_2021_pct DECIMAL(5,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

// Truncate old data
$pdo->exec('TRUNCATE TABLE abs_population');

$fp = fopen($csvPath, 'r');
if (!$fp) {
    echo json_encode(['ok' => false, 'error' => 'Cannot open CSV']);
    exit;
}

// Read headers lines (first two lines are labels)
fgets($fp); // skip line 1
fgets($fp); // skip line 2

$stmt = $pdo->prepare('INSERT INTO abs_population
    (state_code, y2016_2017_no, y2016_2017_pct, y2017_2018_no, y2017_2018_pct, y2018_2019_no, y2018_2019_pct, y2019_2020_no, y2019_2020_pct, y2020_2021_no, y2020_2021_pct)
    VALUES (?,?,?,?,?,?,?,?,?,?,?)');

while (($row = fgetcsv($fp)) !== false) {
    if (count($row) < 11) continue;
    $state = trim($row[0], "\xEF\xBB\xBF\" ");
    if ($state === '' || stripos($state, 'a.') === 0 || stripos($state, 'Source:') === 0) continue;
    // Convert numbers with commas
    $toInt = function ($v) { $v = str_replace([',', '"'], '', $v); return is_numeric($v) ? (int)$v : null; };
    $toDec = function ($v) { $v = str_replace([',', '"'], '', $v); return is_numeric($v) ? (float)$v : null; };

    $stmt->execute([
        $state,
        $toInt($row[1]), $toDec($row[2]),
        $toInt($row[3]), $toDec($row[4]),
        $toInt($row[5]), $toDec($row[6]),
        $toInt($row[7]), $toDec($row[8]),
        $toInt($row[9]), $toDec($row[10]),
    ]);
}

fclose($fp);

echo json_encode(['ok' => true, 'rows_imported' => $pdo->query('SELECT COUNT(*) c FROM abs_population')->fetch()['c']]);


