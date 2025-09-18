<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Addweb_Bricks_Ai_Compatibility {
	public function init() {
		add_action( 'admin_init', array( $this, 'maybe_show_admin_notice' ) );
	}

	public function is_bricks_active_and_compatible() {
		$min_version = '2.0.0';
		$active      = defined( 'BRICKS_DB_VERSION' ) || defined( 'BRICKS_VERSION' ) || function_exists( 'bricks_is_builder' );
		$version     = defined( 'BRICKS_VERSION' ) ? BRICKS_VERSION : ( defined( 'BRICKS_DB_VERSION' ) ? BRICKS_DB_VERSION : null );

		if ( ! $active ) {
			return false;
		}
		if ( $version && version_compare( $version, $min_version, '>=' ) ) {
			return true;
		}
		return true; // Assume compatible if version is unknown but plugin appears active.
	}

	public function maybe_show_admin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! $this->is_bricks_active_and_compatible() ) {
			add_action(
				'admin_notices',
				function () {
					printf(
						'<div class="notice notice-error"><p>%s</p></div>',
						esc_html__( 'Bricks AI Page Builder requires Bricks Builder 2.x or later. Some features are disabled.', 'addweb-bricks-ai' )
					);
				}
			);
		}
	}
}
