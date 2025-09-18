<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Addweb_Bricks_Ai_Ai {
	private $settings_key = 'addweb_bricks_ai_settings';

	public function generate_text( $prompt, $params = array() ) {
		$options = get_option( $this->settings_key, array() );
		$api_key = isset( $options['gemini_api_key'] ) ? $options['gemini_api_key'] : '';
		$api_url = isset( $options['gemini_api_url'] ) ? $options['gemini_api_url'] : '';

		if ( empty( $api_key ) || empty( $api_url ) ) {
			return new WP_Error( 'addweb_ai_missing_config', __( 'Gemini API configuration missing.', 'addweb-bricks-ai' ) );
		}

		$body = array(
			'prompt' => (string) $prompt,
			'params' => $params,
		);

		$args = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
			'timeout' => 45,
			'body'    => wp_json_encode( $body ),
		);

		$response = wp_remote_post( esc_url_raw( $api_url ), $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( 200 !== $code || ! is_array( $data ) ) {
			return new WP_Error( 'addweb_ai_bad_response', __( 'Unexpected response from AI API.', 'addweb-bricks-ai' ), array( 'status' => $code ) );
		}

		if ( isset( $data['text'] ) ) {
			return (string) $data['text'];
		}
		if ( isset( $data['candidates'][0]['content'] ) ) {
			return (string) $data['candidates'][0]['content'];
		}
		return new WP_Error( 'addweb_ai_no_text', __( 'No text returned by AI API.', 'addweb-bricks-ai' ) );
	}
}

