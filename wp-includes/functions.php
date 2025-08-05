<?php
/**
 * WordPress核心函数文件
 * 墨尔本停车管理系统
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 初始化WordPress常量
 */
function wp_initial_constants() {
    global $wp_version;
    
    if (!defined('WP_VERSION')) {
        define('WP_VERSION', $wp_version);
    }
    
    if (!defined('WP_CONTENT_DIR')) {
        define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
    }
    
    if (!defined('WP_CONTENT_URL')) {
        define('WP_CONTENT_URL', WP_SITEURL . '/wp-content');
    }
    
    if (!defined('WP_PLUGIN_DIR')) {
        define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
    }
    
    if (!defined('WP_PLUGIN_URL')) {
        define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
    }
}

/**
 * 插件目录常量
 */
function wp_plugin_directory_constants() {
    if (!defined('WP_PLUGIN_DIR')) {
        define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
    }
    
    if (!defined('WP_PLUGIN_URL')) {
        define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
    }
}

/**
 * 插件表常量
 */
function wp_plugin_table_constants() {
    global $wpdb;
    
    if (!isset($wpdb)) {
        return;
    }
    
    // 设置表前缀
    $wpdb->prefix = 'wp_';
}

/**
 * 获取模板
 */
function get_template() {
    $template = get_template_directory() . '/index.php';
    
    if (file_exists($template)) {
        include($template);
    } else {
        // 显示默认页面
        echo '<!DOCTYPE html>';
        echo '<html><head><title>墨尔本停车管理系统</title>';
        echo '<meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '</head><body>';
        echo '<div class="container mt-5">';
        echo '<h1>墨尔本停车管理系统</h1>';
        echo '<p>欢迎使用墨尔本停车管理系统！</p>';
        echo '<div class="row">';
        echo '<div class="col-md-4"><a href="dashboard.php" class="btn btn-primary">数据洞察</a></div>';
        echo '<div class="col-md-4"><a href="parking-map.php" class="btn btn-success">实时停车</a></div>';
        echo '<div class="col-md-4"><a href="eco-travel.php" class="btn btn-info">环保出行</a></div>';
        echo '</div>';
        echo '</div>';
        echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>';
        echo '</body></html>';
    }
}

/**
 * 获取模板目录
 */
function get_template_directory() {
    return WP_CONTENT_DIR . '/themes/melbourne-parking-theme';
}

/**
 * 获取头部模板
 */
function get_header() {
    $header_file = get_template_directory() . '/header.php';
    if (file_exists($header_file)) {
        include($header_file);
    }
}

/**
 * 获取底部模板
 */
function get_footer() {
    $footer_file = get_template_directory() . '/footer.php';
    if (file_exists($footer_file)) {
        include($footer_file);
    }
}

/**
 * 获取侧边栏模板
 */
function get_sidebar() {
    $sidebar_file = get_template_directory() . '/sidebar.php';
    if (file_exists($sidebar_file)) {
        include($sidebar_file);
    }
}

/**
 * 获取搜索表单
 */
function get_search_form() {
    $search_file = get_template_directory() . '/searchform.php';
    if (file_exists($search_file)) {
        include($search_file);
    } else {
        echo '<form role="search" method="get" class="search-form" action="' . home_url('/') . '">';
        echo '<input type="search" class="search-field" placeholder="搜索..." value="" name="s" />';
        echo '<button type="submit" class="search-submit">搜索</button>';
        echo '</form>';
    }
}

/**
 * 获取站点URL
 */
function home_url($path = '') {
    return WP_HOME . $path;
}

/**
 * 获取站点标题
 */
function get_bloginfo($show = '') {
    switch ($show) {
        case 'name':
            return 'Melbourne Parking Management System';
        case 'description':
            return 'Real-time parking information and eco-friendly travel solutions for Melbourne commuters';
        case 'url':
            return WP_HOME;
        default:
            return 'Melbourne Parking Management System';
    }
}

/**
 * 输出站点信息
 */
function bloginfo($show = '') {
    echo get_bloginfo($show);
}

/**
 * 语言属性
 */
function language_attributes() {
    echo 'lang="en"';
}

/**
 * 页面标题
 */
function wp_title($sep = '|', $display = true, $seplocation = '') {
    $title = get_bloginfo('name');
    if ($display) {
        echo $title;
    }
    return $title;
}

/**
 * 页面标题
 */
function wp_head() {
    // 可以在这里添加额外的头部内容
}

/**
 * 页面底部
 */
function wp_footer() {
    // 可以在这里添加额外的底部内容
}

/**
 * 页面类
 */
function body_class($class = '') {
    $classes = ['melbourne-parking-theme'];
    if ($class) {
        $classes[] = $class;
    }
    echo 'class="' . implode(' ', $classes) . '"';
}

/**
 * 数据库类
 */
class wpdb {
    public $prefix;
    private $dbh;
    
    public function __construct($user, $password, $database, $host) {
        $this->prefix = 'wp_';
        
        try {
            $this->dbh = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("数据库连接失败: " . $e->getMessage());
        }
    }
    
    public function query($query) {
        try {
            return $this->dbh->query($query);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function get_results($query) {
        $stmt = $this->query($query);
        if ($stmt) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return array();
    }
    
    public function get_row($query) {
        $stmt = $this->query($query);
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }
} 