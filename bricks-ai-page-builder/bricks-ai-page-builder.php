<?php
/**
 * Plugin Name:       Bricks AI Page Builder
 * Plugin URI:        https://example.com/plugins/bricks-ai-page-builder
 * Description:       Create Bricks Builder websites with AI-generated pages, blocks, content, and images using Gemini and free image APIs.
 * Version:           1.0.0
 * Author:            AddWeb Solutions
 * Author URI:        https://www.addwebsolution.com/
 * Text Domain:       addweb-bricks-ai
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
if ( ! defined( 'ADDWEB_BRICKS_AI_VERSION' ) ) {
	define( 'ADDWEB_BRICKS_AI_VERSION', '1.0.0' );
}
if ( ! defined( 'ADDWEB_BRICKS_AI_PLUGIN_FILE' ) ) {
	define( 'ADDWEB_BRICKS_AI_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'ADDWEB_BRICKS_AI_PLUGIN_DIR_PATH' ) ) {
	define( 'ADDWEB_BRICKS_AI_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'ADDWEB_BRICKS_AI_PLUGIN_DIR_URL' ) ) {
	define( 'ADDWEB_BRICKS_AI_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Simple autoloader for plugin classes.
 */
spl_autoload_register(
    function ( $class_name ) {
        if ( strpos( $class_name, 'Addweb_Bricks_Ai_' ) !== 0 ) {
            return;
        }
        $relative = 'includes/class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';
        $candidates = array(
            ADDWEB_BRICKS_AI_PLUGIN_DIR_PATH . $relative,
            // Fallback if main file is placed at repo root and classes live in a nested folder
            ADDWEB_BRICKS_AI_PLUGIN_DIR_PATH . 'bricks-ai-page-builder/' . $relative,
        );
        foreach ( $candidates as $file ) {
            if ( file_exists( $file ) ) {
                require_once $file;
                return;
            }
        }
    }
);

/**
 * Load plugin textdomain for translations.
 */
function addweb_bricks_ai_load_textdomain() {
	load_plugin_textdomain( 'addweb-bricks-ai', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'addweb_bricks_ai_load_textdomain' );

/**
 * Activation hook callback.
 */
function addweb_bricks_ai_activate() {
	// Create default options if not present.
	$defaults = array(
		'gemini_api_key'        => '',
		'gemini_api_url'        => '',
		'image_api_key'         => '',
		'image_api_url'         => '',
		'default_primary_color' => '#2b6cb0',
		'default_logo_colors'   => array(),
		'business_type_presets' => array( 'Restaurant', 'Agency', 'SaaS', 'E-commerce', 'Portfolio' ),
	);
	$options = get_option( 'addweb_bricks_ai_settings', array() );
	update_option( 'addweb_bricks_ai_settings', wp_parse_args( $options, $defaults ) );

    // Ensure CPTs are registered for rewrite rules.
    if ( ! class_exists( 'Addweb_Bricks_Ai_Logger' ) ) {
        $logger_files = array(
            ADDWEB_BRICKS_AI_PLUGIN_DIR_PATH . 'includes/class-addweb-bricks-ai-logger.php',
            ADDWEB_BRICKS_AI_PLUGIN_DIR_PATH . 'bricks-ai-page-builder/includes/class-addweb-bricks-ai-logger.php',
        );
        foreach ( $logger_files as $logger_file ) {
            if ( file_exists( $logger_file ) ) {
                require_once $logger_file;
                break;
            }
        }
    }
    if ( class_exists( 'Addweb_Bricks_Ai_Logger' ) ) {
		Addweb_Bricks_Ai_Logger::register_post_type();
	}
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'addweb_bricks_ai_activate' );

/**
 * Deactivation hook callback.
 */
function addweb_bricks_ai_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'addweb_bricks_ai_deactivate' );

/**
 * Bootstrap the plugin services.
 */
function addweb_bricks_ai_bootstrap() {
	$compat = new Addweb_Bricks_Ai_Compatibility();
	$compat->init();

	$logger = new Addweb_Bricks_Ai_Logger();
	$logger->init();

	$admin = new Addweb_Bricks_Ai_Admin();
	$admin->init();

	$ai_client = new Addweb_Bricks_Ai_Ai();
	$images    = new Addweb_Bricks_Ai_Images();

	$rest = new Addweb_Bricks_Ai_Rest( $ai_client, $images, $logger );
	$rest->init();

	$bricks = new Addweb_Bricks_Ai_Bricks( $ai_client, $images, $logger );
	$bricks->init();
}
add_action( 'plugins_loaded', 'addweb_bricks_ai_bootstrap' );
