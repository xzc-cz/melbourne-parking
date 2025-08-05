<?php
/**
 * Database Connection Test Script
 */

// 数据库配置
$host = 'localhost:3307';
$dbname = 'melbourne_parking';
$username = 'root';
$password = '';

try {
    // 创建 PDO 连接
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Database connection successful!</h2>";
    echo "<p>Host: $host</p>";
    echo "<p>Database: $dbname</p>";
    echo "<p>User: $username</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>MySQL Version: " . $result['version'] . "</p>";
    
} catch(PDOException $e) {
    echo "<h2>❌ Database connection failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<h3>Please check:</h3>";
    echo "<ul>";
    echo "<li>MySQL service is running</li>";
    echo "<li>Port number is correct (currently set to 3307)</li>";
    echo "<li>Database 'melbourne_parking' exists</li>";
    echo "<li>Username and password are correct</li>";
    echo "</ul>";
}
?> 