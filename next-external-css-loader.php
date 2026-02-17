<?php
/**
 * Plugin Name: NExT External CSS Loader
 * Plugin URI: https://example.com
 * Description: 外部URLからCSSを読み込むプラグイン。オプション画面でURLを指定でき、強制ロードボタンで即座に読み込みできます。
 * Version: 1.0.0
 * Author: Author Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: next-external-css-loader
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NEXT_EXTERNAL_CSS_LOADER_VERSION', '1.0.0' );
define( 'NEXT_EXTERNAL_CSS_LOADER_PATH', plugin_dir_path( __FILE__ ) );
define( 'NEXT_EXTERNAL_CSS_LOADER_URL', plugin_dir_url( __FILE__ ) );

// Admin pages
if ( is_admin() ) {
	require_once NEXT_EXTERNAL_CSS_LOADER_PATH . 'includes/admin.php';
}

// Frontend CSS loading
require_once NEXT_EXTERNAL_CSS_LOADER_PATH . 'includes/loader.php';

// Register activation hook
register_activation_hook( __FILE__, 'next_external_css_loader_activate' );

function next_external_css_loader_activate() {
	// Initialize options
	if ( ! get_option( 'next_external_css_url' ) ) {
		add_option( 'next_external_css_url', '' );
	}
	if ( ! get_option( 'next_external_css_loaded' ) ) {
		add_option( 'next_external_css_loaded', 0 );
	}
}

// Register deactivation hook
register_deactivation_hook( __FILE__, 'next_external_css_loader_deactivate' );

function next_external_css_loader_deactivate() {
	// Cleanup can be done here if needed
}

// Uninstall is handled by uninstall.php for better reliability
