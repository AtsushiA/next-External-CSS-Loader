<?php
/**
 * Frontend CSS Loader for NExT-External CSS Loader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load external CSS on frontend
add_action( 'wp_enqueue_scripts', 'next_external_css_loader_enqueue_css' );

function next_external_css_loader_enqueue_css() {
	$css_url = get_option( 'next_external_css_url' );
	$loaded_flag = get_option( 'next_external_css_loaded' );

	// Only load if URL is set and loaded flag is not set
	if ( empty( $css_url ) ) {
		return;
	}

	// If loaded flag is 0, don't load
	if ( ! $loaded_flag ) {
		return;
	}

	// Validate URL
	if ( ! filter_var( $css_url, FILTER_VALIDATE_URL ) ) {
		return;
	}

	// Enqueue the external CSS
	wp_enqueue_style(
		'next-external-css',
		esc_url( $css_url ),
		array(),
		NEXT_EXTERNAL_CSS_LOADER_VERSION
	);
}

// Set loaded flag on first page load after force
add_action( 'wp_footer', 'next_external_css_loader_set_loaded_flag' );

function next_external_css_loader_set_loaded_flag() {
	if ( is_admin() ) {
		return;
	}

	$loaded_flag = (int) get_option( 'next_external_css_loaded' );

	// If flag is 0, set it to current timestamp
	if ( ! $loaded_flag ) {
		update_option( 'next_external_css_loaded', time() );
	}
}
