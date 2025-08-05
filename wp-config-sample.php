<?php
/**
 * Melbourne Parking Management System - Sample Configuration
 * 
 * Copy this file to wp-config.php and update the values below
 */

// Database Configuration
define( 'DB_NAME', 'melbourne_parking' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost:3307' );

// WordPress Configuration
define( 'WP_CONTENT_URL', 'http://localhost/melbourne-parking/wp-content' );

// Security Keys (generate your own at https://api.wordpress.org/secret-key/1.1/salt/)
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

// WordPress Database Table prefix
$table_prefix = 'wp_';

// For developers: WordPress debugging mode
define( 'WP_DEBUG', false );

// Absolute path to the WordPress directory
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

// Sets up WordPress vars and included files
require_once ABSPATH . 'wp-settings.php'; 