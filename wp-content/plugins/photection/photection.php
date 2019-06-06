<?php

/**
 * @link              http://colormelon.com/photection
 * @since             1.0.0
 * @package           Photection
 *
 * @wordpress-plugin
 * Plugin Name:       Photection
 * Plugin URI:        http://colormelon.com/photection
 * Description:       This plugin helps you prevent image downloads from your site. Nothing is perfect, but this will help in a pretty way.
 * Version:           1.0.0
 * Author:            Colormelon, justnorris
 * Author URI:        http://colormelon.com
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       photection
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


// ============================================
// START <PHP Version Check>
// ============================================
/**
 * Check if PHP Requirements are met
 * @return mixed
 */
function photection_requirements_met() {

	return version_compare( PHP_VERSION, '5.3.29', '>=' );
}

/**
 * Deactivate and trigger wp_die() with message that explains requirements
 */
function photection_auto_deactivate() {

	deactivate_plugins( plugin_basename( __FILE__ ) );

	wp_die(
		'<p>The <strong>Photection</strong> plugin requires PHP version 5.3.29 or greater.</p>',
		'Plugin Activation Error',
		array( 'response' => 200, 'back_link' => true )
	);
}

/**
 * This will check for PHP Version on each load and on plugin activation
 * If invalid PHP version is found, wp_die() will be triggered.
 */
function photection_require_php53() {

	if ( ! photection_requirements_met() ) {
		photection_auto_deactivate();
	}

}

register_activation_hook( __FILE__, 'photection_require_php53' );
add_action( 'admin_init', 'photection_require_php53' );


// ============================================
// END <PHP Version Check>
// ============================================


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/Photection.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_photection() {

	$plugin = new Photection();
	$plugin->run();

}

/**
 * Wrapper to easily get option values
 * @since  0.1.0
 *
 * @param  string $key Options array key
 *
 * @return mixed        Option value
 */
function photection_get_option( $key = '' ) {

	$options = get_option( 'photection' );

	if ( empty( $key ) ) {
		return $options;
	}

	if ( isset( $options[ $key ] ) ) {
		return $options[ $key ];
	}

	return false;

}


run_photection();
