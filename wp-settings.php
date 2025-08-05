<?php
/**
 * WordPress基础设置文件
 * 墨尔本停车管理系统
 */

// 设置WordPress版本
$wp_version = '6.2';

// 设置站点URL
if (!defined('WP_HOME')) {
    define('WP_HOME', 'http://localhost/melbourne-parking');
}
if (!defined('WP_SITEURL')) {
    define('WP_SITEURL', 'http://localhost/melbourne-parking');
}

// 加载核心函数
require_once(ABSPATH . 'wp-includes/functions.php');

// 设置数据库连接
global $wpdb;
$wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

// 初始化WordPress
wp_initial_constants();
wp_plugin_directory_constants();

// 加载插件
wp_plugin_table_constants();

// 设置默认主题
if (!defined('WP_DEFAULT_THEME')) {
    define('WP_DEFAULT_THEME', 'melbourne-parking-theme');
}

// 加载主题
get_template(); 