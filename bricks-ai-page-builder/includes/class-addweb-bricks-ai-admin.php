<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Addweb_Bricks_Ai_Admin {
	private $option_key = 'addweb_bricks_ai_settings';

	public function init() {
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_menu', array( $this, 'register_wizard_page' ) );
	}

	public function register_settings_page() {
		add_options_page(
			__( 'Bricks AI', 'addweb-bricks-ai' ),
			__( 'Bricks AI', 'addweb-bricks-ai' ),
			'manage_options',
			'addweb-bricks-ai-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_wizard_page() {
		add_submenu_page(
			'edit.php?post_type=page',
			__( 'Bricks AI Wizard', 'addweb-bricks-ai' ),
			__( 'Bricks AI Wizard', 'addweb-bricks-ai' ),
			'edit_pages',
			'addweb-bricks-ai-wizard',
			array( $this, 'render_wizard_page' )
		);
	}

	public function register_settings() {
		register_setting( $this->option_key, $this->option_key, array( $this, 'sanitize_settings' ) );

		add_settings_section( 'addweb_bricks_ai_main', __( 'API Settings', 'addweb-bricks-ai' ), '__return_false', $this->option_key );

		add_settings_field( 'gemini_api_key', __( 'Gemini API Key', 'addweb-bricks-ai' ), array( $this, 'field_text' ), $this->option_key, 'addweb_bricks_ai_main', array( 'key' => 'gemini_api_key' ) );
		add_settings_field( 'gemini_api_url', __( 'Gemini API URL', 'addweb-bricks-ai' ), array( $this, 'field_text' ), $this->option_key, 'addweb_bricks_ai_main', array( 'key' => 'gemini_api_url' ) );
		add_settings_field( 'image_api_key', __( 'Image API Key', 'addweb-bricks-ai' ), array( $this, 'field_text' ), $this->option_key, 'addweb_bricks_ai_main', array( 'key' => 'image_api_key' ) );
		add_settings_field( 'image_api_url', __( 'Image API URL', 'addweb-bricks-ai' ), array( $this, 'field_text' ), $this->option_key, 'addweb_bricks_ai_main', array( 'key' => 'image_api_url' ) );

		add_settings_section( 'addweb_bricks_ai_design', __( 'Design Defaults', 'addweb-bricks-ai' ), '__return_false', $this->option_key );
		add_settings_field( 'default_primary_color', __( 'Default Primary Color', 'addweb-bricks-ai' ), array( $this, 'field_color' ), $this->option_key, 'addweb_bricks_ai_design', array( 'key' => 'default_primary_color' ) );
		add_settings_field( 'default_logo_colors', __( 'Default Logo Colors (comma separated)', 'addweb-bricks-ai' ), array( $this, 'field_text' ), $this->option_key, 'addweb_bricks_ai_design', array( 'key' => 'default_logo_colors' ) );
		add_settings_field( 'business_type_presets', __( 'Business Type Presets (comma separated)', 'addweb-bricks-ai' ), array( $this, 'field_text' ), $this->option_key, 'addweb_bricks_ai_design', array( 'key' => 'business_type_presets' ) );
	}

	public function sanitize_settings( $input ) {
		$sanitized                                = array();
		$sanitized['gemini_api_key']              = isset( $input['gemini_api_key'] ) ? sanitize_text_field( $input['gemini_api_key'] ) : '';
		$sanitized['gemini_api_url']              = isset( $input['gemini_api_url'] ) ? esc_url_raw( $input['gemini_api_url'] ) : '';
		$sanitized['image_api_key']               = isset( $input['image_api_key'] ) ? sanitize_text_field( $input['image_api_key'] ) : '';
		$sanitized['image_api_url']               = isset( $input['image_api_url'] ) ? esc_url_raw( $input['image_api_url'] ) : '';
		$sanitized['default_primary_color']       = isset( $input['default_primary_color'] ) ? sanitize_hex_color( $input['default_primary_color'] ) : '#2b6cb0';
		$logo_colors                              = isset( $input['default_logo_colors'] ) ? sanitize_text_field( $input['default_logo_colors'] ) : '';
		$sanitized['default_logo_colors']         = array_filter( array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', (string) $logo_colors ) ) ) );
		$presets                                  = isset( $input['business_type_presets'] ) ? sanitize_text_field( $input['business_type_presets'] ) : '';
		$sanitized['business_type_presets']       = array_filter( array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', (string) $presets ) ) ) );
		return $sanitized;
	}

	private function get_option( $key, $default = '' ) {
		$options = get_option( $this->option_key, array() );
		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
	}

	public function field_text( $args ) {
		$key   = isset( $args['key'] ) ? $args['key'] : '';
		$value = $this->get_option( $key, '' );
		$name  = $this->option_key . '[' . $key . ']';
		echo '<input type="text" class="regular-text" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_array( $value ) ? implode( ',', $value ) : $value ) . '" />';
	}

	public function field_color( $args ) {
		$key   = isset( $args['key'] ) ? $args['key'] : '';
		$value = $this->get_option( $key, '#2b6cb0' );
		$name  = $this->option_key . '[' . $key . ']';
		echo '<input type="text" class="addweb-bricks-ai-color-field" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" data-default-color="#2b6cb0" />';
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Bricks AI Settings', 'addweb-bricks-ai' ) . '</h1>';
		echo '<form method="post" action="options.php">';
		settings_fields( $this->option_key );
		do_settings_sections( $this->option_key );
		submit_button();
		echo '</form>';
		echo '</div>';
	}

	public function render_wizard_page() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		wp_enqueue_style( 'addweb-bricks-ai-wizard', ADDWEB_BRICKS_AI_PLUGIN_DIR_URL . 'assets/css/wizard.css', array(), ADDWEB_BRICKS_AI_VERSION );
		wp_enqueue_script( 'addweb-bricks-ai-wizard', ADDWEB_BRICKS_AI_PLUGIN_DIR_URL . 'assets/js/wizard.js', array( 'wp-i18n', 'wp-element' ), ADDWEB_BRICKS_AI_VERSION, true );
		wp_localize_script(
			'addweb-bricks-ai-wizard',
			'AddwebBricksAI',
			array(
				'nonce'        => wp_create_nonce( 'wp_rest' ),
				'restBase'     => esc_url_raw( rest_url( 'addweb-bricks-ai/v1' ) ),
				'defaults'     => array(
					'primaryColor' => $this->get_option( 'default_primary_color', '#2b6cb0' ),
					'logoColors'   => $this->get_option( 'default_logo_colors', array() ),
					'presets'      => $this->get_option( 'business_type_presets', array() ),
				),
				'i18n'         => array(
					'title' => __( 'Bricks AI Wizard', 'addweb-bricks-ai' ),
				),
			)
		);
		echo '<div class="wrap"><h1>' . esc_html__( 'Bricks AI Wizard', 'addweb-bricks-ai' ) . '</h1>';
		echo '<div id="addweb-bricks-ai-wizard-root"></div></div>';
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_addweb-bricks-ai-settings' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'addweb-bricks-ai-admin', ADDWEB_BRICKS_AI_PLUGIN_DIR_URL . 'assets/css/admin.css', array(), ADDWEB_BRICKS_AI_VERSION );
		wp_enqueue_script( 'addweb-bricks-ai-admin', ADDWEB_BRICKS_AI_PLUGIN_DIR_URL . 'assets/js/admin.js', array( 'wp-color-picker' ), ADDWEB_BRICKS_AI_VERSION, true );
	}
}

