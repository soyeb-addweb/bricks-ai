<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Addweb_Bricks_Ai_Logger {
	const CPT = 'addweb_ai_log';

	public function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	public static function register_post_type() {
		register_post_type(
			self::CPT,
			array(
				'labels'       => array(
					'name'          => __( 'AI Logs', 'addweb-bricks-ai' ),
					'singular_name' => __( 'AI Log', 'addweb-bricks-ai' ),
				),
				'public'       => false,
				'show_ui'      => false,
				'show_in_menu' => false,
				'supports'     => array( 'title', 'editor', 'custom-fields' ),
			)
		);
	}

	public function register_menu() {
		add_management_page(
			__( 'Bricks AI Logs', 'addweb-bricks-ai' ),
			__( 'Bricks AI Logs', 'addweb-bricks-ai' ),
			'manage_options',
			'addweb-bricks-ai-logs',
			array( $this, 'render_logs_page' )
		);
	}

	public function render_logs_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$logs = get_posts(
			array(
				'post_type'      => self::CPT,
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
		echo '<div class="wrap"><h1>' . esc_html__( 'Bricks AI Logs', 'addweb-bricks-ai' ) . '</h1>';
		if ( empty( $logs ) ) {
			echo '<p>' . esc_html__( 'No logs yet.', 'addweb-bricks-ai' ) . '</p></div>';
			return;
		}
		echo '<table class="widefat fixed striped"><thead><tr><th>' . esc_html__( 'Date', 'addweb-bricks-ai' ) . '</th><th>' . esc_html__( 'Level', 'addweb-bricks-ai' ) . '</th><th>' . esc_html__( 'Message', 'addweb-bricks-ai' ) . '</th></tr></thead><tbody>';
		foreach ( $logs as $log ) {
			$level = get_post_meta( $log->ID, '_addweb_ai_level', true );
			echo '<tr>';
			echo '<td>' . esc_html( get_the_time( 'Y-m-d H:i', $log ) ) . '</td>';
			echo '<td>' . esc_html( $level ? $level : 'info' ) . '</td>';
			echo '<td>' . esc_html( wp_strip_all_tags( $log->post_content ) ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table></div>';
	}

	public function log( $level, $message, $context = array() ) {
		$allowed_levels = array( 'debug', 'info', 'warning', 'error' );
		$level          = in_array( $level, $allowed_levels, true ) ? $level : 'info';
		$post_id        = wp_insert_post(
			array(
				'post_type'   => self::CPT,
				'post_status' => 'publish',
				'post_title'  => '[' . strtoupper( $level ) . '] ' . current_time( 'mysql' ),
				'post_content'=> is_string( $message ) ? $message : wp_json_encode( $message ),
			)
		);
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_addweb_ai_level', $level );
			if ( ! empty( $context ) ) {
				update_post_meta( $post_id, '_addweb_ai_context', wp_json_encode( $context ) );
			}
		}
	}
}

