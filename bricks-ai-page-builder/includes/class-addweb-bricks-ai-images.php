<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Addweb_Bricks_Ai_Images {
	private $settings_key = 'addweb_bricks_ai_settings';

	public function generate_image( $prompt, $size = '1024x1024' ) {
		$options = get_option( $this->settings_key, array() );
		$api_key = isset( $options['image_api_key'] ) ? $options['image_api_key'] : '';
		$api_url = isset( $options['image_api_url'] ) ? $options['image_api_url'] : '';

		if ( empty( $api_key ) || empty( $api_url ) ) {
			$seed = urlencode( substr( md5( $prompt ), 0, 8 ) );
			$url  = 'https://picsum.photos/seed/' . $seed . '/1024/768';
			return $this->sideload_image( $url, 0, 'AI Image: ' . $prompt );
		}

		$body = array(
			'prompt' => (string) $prompt,
			'size'   => (string) $size,
		);

		$args = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
			'timeout' => 60,
			'body'    => wp_json_encode( $body ),
		);

		$response = wp_remote_post( esc_url_raw( $api_url ), $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( 200 !== $code || ! is_array( $data ) ) {
			return new WP_Error( 'addweb_image_bad_response', __( 'Unexpected response from Image API.', 'addweb-bricks-ai' ), array( 'status' => $code ) );
		}

		$image_url = isset( $data['url'] ) ? $data['url'] : ( isset( $data['data'][0]['url'] ) ? $data['data'][0]['url'] : '' );
		if ( empty( $image_url ) ) {
			return new WP_Error( 'addweb_image_no_url', __( 'No image URL returned by Image API.', 'addweb-bricks-ai' ) );
		}

		return $this->sideload_image( esc_url_raw( $image_url ), 0, 'AI Image: ' . $prompt );
	}

	public function sideload_image( $url, $post_id = 0, $desc = '' ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = download_url( $url, 60 );
		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}

		$file_array            = array();
		$file_array['name']    = wp_basename( parse_url( $url, PHP_URL_PATH ) );
		$file_array['tmp_name'] = $tmp;

		$att_id = media_handle_sideload( $file_array, $post_id, $desc );
		if ( is_wp_error( $att_id ) ) {
			@unlink( $file_array['tmp_name'] );
			return $att_id;
		}
		return $att_id;
	}
}
