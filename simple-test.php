<?php
/**
 * 简单测试页面
 */

echo '<!DOCTYPE html>';
echo '<html><head><title>墨尔本停车管理系统 - 测试</title>';
echo '<meta charset="utf-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">';
echo '</head><body>';

echo '<div class="container mt-5">';
echo '<h1>Melbourne Parking Management System - Test Page</h1>';

// Test database connection
try {
    $pdo = new PDO("mysql:host=localhost:3307;dbname=melbourne_parking;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo '<div class="alert alert-success">✅ Database connection successful!</div>';
} catch(PDOException $e) {
    echo '<div class="alert alert-danger">❌ Database connection failed: ' . $e->getMessage() . '</div>';
}

// Test file existence
$files_to_test = [
    'dashboard.php' => 'Data Insights Page',
    'parking-map.php' => 'Real-time Parking Page', 
    'eco-travel.php' => 'Eco Travel Page',
    'test-db.php' => 'Database Test Page'
];

echo '<h3>File Check:</h3>';
foreach ($files_to_test as $file => $description) {
    if (file_exists($file)) {
        echo '<div class="text-success">✅ ' . $description . ' (' . $file . ') exists</div>';
    } else {
        echo '<div class="text-danger">❌ ' . $description . ' (' . $file . ') missing</div>';
    }
}

echo '<h3>Quick Navigation:</h3>';
echo '<div class="row">';
echo '<div class="col-md-3"><a href="dashboard.php" class="btn btn-primary">Data Insights</a></div>';
echo '<div class="col-md-3"><a href="parking-map.php" class="btn btn-success">Real-time Parking</a></div>';
echo '<div class="col-md-3"><a href="eco-travel.php" class="btn btn-info">Eco Travel</a></div>';
echo '<div class="col-md-3"><a href="data-sources.php" class="btn btn-secondary">Data Sources</a></div>';
echo '<div class="col-md-3"><a href="test-db.php" class="btn btn-warning">Database Test</a></div>';
echo '</div>';

echo '</div>';

echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>';
echo '</body></html>';
?> 