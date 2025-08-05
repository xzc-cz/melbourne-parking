<?php
/**
 * 墨尔本停车管理系统安装脚本
 */

// 检查PHP版本
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    die('错误：需要PHP 8.0或更高版本。当前版本：' . PHP_VERSION);
}

// 检查必要的PHP扩展
$required_extensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die('错误：缺少必要的PHP扩展：' . implode(', ', $missing_extensions));
}

// 数据库配置
$db_config = [
    'host' => 'localhost',
    'name' => 'melbourne_parking',
    'user' => 'root',
    'password' => ''
];

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_config['host'] = $_POST['db_host'] ?? 'localhost';
    $db_config['name'] = $_POST['db_name'] ?? 'melbourne_parking';
    $db_config['user'] = $_POST['db_user'] ?? 'root';
    $db_config['password'] = $_POST['db_password'] ?? '';
    
    try {
        // 测试数据库连接
        $pdo = new PDO(
            "mysql:host={$db_config['host']};charset=utf8mb4",
            $db_config['user'],
            $db_config['password']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 创建数据库
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_config['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // 更新配置文件
        $config_content = file_get_contents('wp-config.php');
        $config_content = str_replace(
            ["'melbourne_parking'", "'root'", "''"],
            ["'{$db_config['name']}'", "'{$db_config['user']}'", "'{$db_config['password']}'"],
            $config_content
        );
        file_put_contents('wp-config.php', $config_content);
        
        $success_message = "安装成功！数据库已创建，配置文件已更新。";
        
    } catch (PDOException $e) {
        $error_message = "数据库连接失败：" . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>墨尔本停车管理系统 - 安装向导</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3><i class="fas fa-car"></i> 墨尔本停车管理系统</h3>
                        <p class="mb-0">安装向导</p>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                            </div>
                            <div class="text-center">
                                <a href="index.php" class="btn btn-primary">
                                    <i class="fas fa-home"></i> 访问首页
                                </a>
                            </div>
                        <?php elseif (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!isset($success_message)): ?>
                            <form method="POST">
                                <h5 class="mb-3">数据库配置</h5>
                                
                                <div class="mb-3">
                                    <label for="db_host" class="form-label">数据库主机</label>
                                    <input type="text" class="form-control" id="db_host" name="db_host" 
                                           value="<?php echo htmlspecialchars($db_config['host']); ?>" required>
                                    <div class="form-text">通常是 localhost</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="db_name" class="form-label">数据库名称</label>
                                    <input type="text" class="form-control" id="db_name" name="db_name" 
                                           value="<?php echo htmlspecialchars($db_config['name']); ?>" required>
                                    <div class="form-text">如果不存在将自动创建</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="db_user" class="form-label">数据库用户名</label>
                                    <input type="text" class="form-control" id="db_user" name="db_user" 
                                           value="<?php echo htmlspecialchars($db_config['user']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="db_password" class="form-label">数据库密码</label>
                                    <input type="password" class="form-control" id="db_password" name="db_password" 
                                           value="<?php echo htmlspecialchars($db_config['password']); ?>">
                                    <div class="form-text">如果没有密码请留空</div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> 系统要求检查</h6>
                                    <ul class="mb-0">
                                        <li>PHP版本: <?php echo PHP_VERSION; ?> ✓</li>
                                        <li>PDO扩展: <?php echo extension_loaded('pdo') ? '✓' : '✗'; ?></li>
                                        <li>PDO MySQL扩展: <?php echo extension_loaded('pdo_mysql') ? '✓' : '✗'; ?></li>
                                        <li>cURL扩展: <?php echo extension_loaded('curl') ? '✓' : '✗'; ?></li>
                                        <li>JSON扩展: <?php echo extension_loaded('json') ? '✓' : '✗'; ?></li>
                                        <li>mbstring扩展: <?php echo extension_loaded('mbstring') ? '✓' : '✗'; ?></li>
                                    </ul>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-download"></i> 开始安装
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        安装完成后，请删除 install.php 文件以确保安全
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 