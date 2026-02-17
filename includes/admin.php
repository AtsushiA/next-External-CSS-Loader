<?php
/**
 * Admin page for NExT-External CSS Loader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add admin menu
add_action( 'admin_menu', 'next_external_css_loader_add_admin_menu' );

function next_external_css_loader_add_admin_menu() {
	add_options_page(
		'NExT-External CSS Loader',
		'NExT CSS Loader',
		'manage_options',
		'next-external-css-loader',
		'next_external_css_loader_options_page'
	);
}

// Register settings
add_action( 'admin_init', 'next_external_css_loader_register_settings' );

function next_external_css_loader_register_settings() {
	register_setting(
		'next_external_css_loader_group',
		'next_external_css_url',
		array(
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest' => false,
		)
	);

	register_setting(
		'next_external_css_loader_group',
		'next_external_css_loaded',
		array(
			'type' => 'integer',
			'sanitize_callback' => 'intval',
			'show_in_rest' => false,
		)
	);

	add_settings_section(
		'next_external_css_loader_section',
		'NExT-External CSS Loader Settings',
		'next_external_css_loader_section_callback',
		'next-external-css-loader'
	);

	add_settings_field(
		'next_external_css_url',
		'External CSS URL',
		'next_external_css_loader_url_field_callback',
		'next-external-css-loader',
		'next_external_css_loader_section'
	);
}

function next_external_css_loader_section_callback() {
	echo '<p>外部URLからCSSを読み込みます。</p>';
}

function next_external_css_loader_url_field_callback() {
	$url = get_option( 'next_external_css_url' );
	echo '<input type="url" name="next_external_css_url" value="' . esc_attr( $url ) . '" style="width: 100%; max-width: 500px;" />';
}

// Handle force load button
add_action( 'admin_init', 'next_external_css_loader_handle_force_load' );

function next_external_css_loader_handle_force_load() {
	if ( ! isset( $_POST['next_external_css_force_load_nonce'] ) ) {
		return;
	}

	// Sanitize nonce before verification
	$nonce = sanitize_text_field( wp_unslash( $_POST['next_external_css_force_load_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'next_external_css_force_load' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Set the loaded flag to 0 to force reload on next page load
	update_option( 'next_external_css_loaded', 0 );

	// Redirect to settings page with message
	wp_redirect( admin_url( 'options-general.php?page=next-external-css-loader&next_css_forced=1' ) );
	exit;
}

// Admin page HTML
function next_external_css_loader_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$forced = isset( $_GET['next_css_forced'] ) ? intval( $_GET['next_css_forced'] ) : 0;
	$loaded_timestamp = get_option( 'next_external_css_loaded' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<?php if ( $forced ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( '次回の読み込みで外部CSSが読み込まれます。', 'next-external-css-loader' ); ?></p>
			</div>
		<?php endif; ?>

		<form action="options.php" method="post">
			<?php
			settings_fields( 'next_external_css_loader_group' );
			do_settings_sections( 'next-external-css-loader' );
			submit_button( 'Save Settings' );
			?>
		</form>

		<hr />

		<h2><?php esc_html_e( 'Force Load', 'next-external-css-loader' ); ?></h2>
		<p><?php esc_html_e( '強制ロードボタンをクリックすると、次回の読み込みで外部CSSが読み込まれます。', 'next-external-css-loader' ); ?></p>

		<?php if ( $loaded_timestamp ) : ?>
			<p>
				<?php
					echo esc_html__( 'Last loaded: ', 'next-external-css-loader' );
					echo esc_html( wp_date( 'Y-m-d H:i:s', $loaded_timestamp ) );
				?>
			</p>
		<?php endif; ?>

		<form method="post" style="display: inline;">
			<?php wp_nonce_field( 'next_external_css_force_load', 'next_external_css_force_load_nonce' ); ?>
			<?php submit_button( 'Force Load', 'secondary', 'next_external_css_force_load' ); ?>
		</form>

		<hr />

		<h2><?php esc_html_e( 'CSS Preview', 'next-external-css-loader' ); ?></h2>
		<p><?php esc_html_e( '現在設定されている外部CSSの内容：', 'next-external-css-loader' ); ?></p>

		<?php
		$css_url = get_option( 'next_external_css_url' );
		if ( ! empty( $css_url ) && filter_var( $css_url, FILTER_VALIDATE_URL ) ) :
			$css_content = next_external_css_loader_fetch_css_content( $css_url );
			if ( is_wp_error( $css_content ) ) :
				?>
				<div class="notice notice-error">
					<p><?php echo esc_html( $css_content->get_error_message() ); ?></p>
				</div>
				<?php
			else :
				?>
				<textarea readonly style="width: 100%; max-width: 600px; height: 300px; font-family: monospace; background-color: #f5f5f5;"><?php echo esc_textarea( $css_content ); ?></textarea>
				<?php
			endif;
		else :
			?>
			<p><em><?php esc_html_e( 'CSSのURLが設定されていません。', 'next-external-css-loader' ); ?></em></p>
			<?php
		endif;
		?>
	</div>
	<?php
}

/**
 * Fetch CSS content from external URL
 *
 * @param string $url The URL to fetch CSS from.
 * @return string|WP_Error CSS content or error.
 */
function next_external_css_loader_fetch_css_content( $url ) {
	/**
	 * Filter SSL verification for external CSS fetch.
	 *
	 * @param bool $sslverify Whether to verify SSL certificate. Default true.
	 * @param string $url The URL being fetched.
	 */
	$sslverify = apply_filters( 'next_external_css_loader_sslverify', true, $url );

	$response = wp_remote_get( $url, array(
		'timeout' => 10,
		'sslverify' => $sslverify,
	) );

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'fetch_error', __( 'CSSの取得に失敗しました: ', 'next-external-css-loader' ) . $response->get_error_message() );
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	if ( $response_code !== 200 ) {
		return new WP_Error( 'http_error', sprintf( __( 'HTTP エラー: %d', 'next-external-css-loader' ), $response_code ) );
	}

	$body = wp_remote_retrieve_body( $response );
	if ( empty( $body ) ) {
		return new WP_Error( 'empty_response', __( 'CSSファイルが空です。', 'next-external-css-loader' ) );
	}

	return $body;
}
