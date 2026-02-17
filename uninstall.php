<?php
/**
 * Uninstall script for NExT-External CSS Loader
 *
 * This file is called when the plugin is deleted via WordPress admin.
 */

// Exit if not called by WordPress uninstall process
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options
delete_option( 'next_external_css_url' );
delete_option( 'next_external_css_loaded' );
