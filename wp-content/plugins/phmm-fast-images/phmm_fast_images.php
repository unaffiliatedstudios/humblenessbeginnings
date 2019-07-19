<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://codeneric.com
 * @since             4.9.6
 * @package           PHMM_Fast_Images
 *
 * @wordpress-plugin
 * Plugin Name:       PHMM Fast Images
 * Plugin URI:        https://codeneric.com/shop/phmm-fast-images/
 * Description:       Fast images extension for Photography Management 
 * Version:           5.2.4
 * Author:            Codeneric
 * Author URI:        https://codeneric.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       phmm-fast-images
 * Domain Path:       /languages
 */

define("PHMM_FI_VERSION", "5.2.4");  


$a_p = get_option('active_plugins');
$the_plugs = get_site_option('active_sitewide_plugins'); //multisite


if (
	!in_array('photography-management/photography_management.php', $a_p) &&
	!isset($the_plugs['photography-management/photography_management.php'])
) {
	if (!function_exists('cc_phmm_premium_admin_notice_disabled_base')) {
		function cc_phmm_premium_admin_notice_disabled_base() {
			$class = "error";
			$message =
				"Please install and activate the latest free Photography Management plugin";
			echo "<div class=\"$class\"> <p>$message</p></div>";
		}

		add_action(
			'admin_notices',
			'cc_phmm_premium_admin_notice_disabled_base'
		);
	}

	// exit(1);
	return;
}

global $wp_version;
if ( version_compare( $wp_version, '5.1', '<' ) ) {
	if (!function_exists('cc_phmm_fi_admin_notice_wp_too_old')) {
		function cc_phmm_fi_admin_notice_wp_too_old() {
			$class = "error";
			$message =
				"PHMM Fast Images: Your WordPress version is too old! Please update to use PHMM Fast Images.";
			echo "<div class=\"$class\"> <p>$message</p></div>";
		}

		add_action(
			'admin_notices',
			'cc_phmm_fi_admin_notice_wp_too_old'
		);
	}
	return;
}

require_once('util.php'); 

function phmm_fast_images_base_generate_htaccess(
	$htaccess_path,
	$new_site_url = null
) {
	// $upload_dir = wp_upload_dir(); //['basedir'].'/photography_management';
	//   $upload_dir = $upload_dir['baseurl'];
	// $protect_url = plugins_url('load.plain.php', __FILE__);
	$settings = \codeneric\phmm\base\admin\Settings::getCurrentSettings();
	$should_generate = !array_key_exists('fast_image_load', $settings) || !$settings['fast_image_load']; //if undefined or false

	if( $should_generate ){
		$protect_url = is_null($new_site_url) ? get_site_url() : $new_site_url;
		$htaccess = "RewriteEngine On".
			PHP_EOL.
			"RewriteCond %{REQUEST_URI} !protect.php".
			PHP_EOL.
			"RewriteCond %{QUERY_STRING} ^(.*)".
			PHP_EOL.
			"RewriteRule ^(.+)$ $protect_url/?codeneric_load_image=1&%1&f=$1 [L,NC]";

		return insert_with_markers($htaccess_path, 'CODENERIC PHMM', $htaccess);
	} 

	return null;
	

}

function phmm_fast_images_refresh_htaccess(){
	$upload_dir = wp_upload_dir();
	$upload_dir = $upload_dir['basedir'].'/photography_management';
	$htaccess_path = "$upload_dir/.htaccess";
	$fast_load_url =
		plugins_url('load.plain.php', __DIR__.'/code/load.plain.php');
	phmm_fast_images_base_generate_htaccess($htaccess_path, $fast_load_url);

}

add_action('codeneric/phmm/refresh-htaccess', 'phmm_fast_images_refresh_htaccess'); 


function activate_phmm_fast_images() {
	phmm_fast_images_refresh_htaccess();
	pfi_register(); 
	pfi_send_event("activated", array("plugin" => "phmm-fast-images") ); 

}



/**
 * The code that runs during plugin deactivation.how
 * This action is documented in includes/class-phmm-deactivator.php
 */
function deactivate_phmm_fast_images() {
	$upload_dir = wp_upload_dir();
	$upload_dir = $upload_dir['basedir'].'/photography_management';
	$htaccess_path = "$upload_dir/.htaccess";
	phmm_fast_images_base_generate_htaccess($htaccess_path);
	delete_option('CODENERIC_FI_WP_LOAD_PATH');

	pfi_send_event("deactivated", array("plugin" => "phmm-fast-images") );  
}

register_activation_hook(__FILE__, 'activate_phmm_fast_images');
register_deactivation_hook(__FILE__, 'deactivate_phmm_fast_images');


function update_htaccess_phmm_fast_images($old_siteurl, $new_siteurl) {
	$upload_dir = wp_upload_dir();
	$upload_dir = $upload_dir['basedir'].'/photography_management';
	$htaccess_path = "$upload_dir/.htaccess";
	// $fast_load_url =
	// 	plugins_url('load.plain.php', __DIR__.'/code/load.plain.php'); //gives old siteurl!!!
	$fast_load_url =
		"$new_siteurl/wp-content/plugins/phmm-fast-images/code/load.plain.php";


	phmm_fast_images_base_generate_htaccess($htaccess_path, $fast_load_url);


}

add_action('update_option_siteurl', 'update_htaccess_phmm_fast_images', 11, 2);

function make_load_script() {
	$location = get_option('CODENERIC_FI_WP_LOAD_PATH');
	$plugin_version = get_option('CODENERIC_FI_PLUGIN_VERSION');
	if ($location !== ABSPATH || $plugin_version !== PHMM_FI_VERSION) {
		//update load.plain.php

		$src = file_get_contents(__DIR__.'/code/load.src.php');
		$pattern =
			'/\/\/CODENERIC_BEGIN_LOCATION_DEFINITION\n(.*)\n\/\/CODENERIC_END_LOCATION_DEFINITION/s';
		$replacement = "define('CODENERIC_FI_WP_LOAD_PATH', '".ABSPATH."');";
		$build = preg_replace($pattern, $replacement, $src);
		file_put_contents(__DIR__.'/code/load.plain.php', $build);
		update_option('CODENERIC_FI_WP_LOAD_PATH', ABSPATH);
		update_option('CODENERIC_FI_PLUGIN_VERSION', PHMM_FI_VERSION);

		pfi_register(); 
	}

}

make_load_script();


function codeneric_phmm_fast_images_updater(){
	if (!isset($GLOBALS['HACKLIB_ROOT'])) {
	$GLOBALS['HACKLIB_ROOT'] =
		plugin_dir_path(__FILE__).'lib/hacklib/hacklib.php'; // will be injected by gulp
	}

	require('PluginUpdater.php');  
}

codeneric_phmm_fast_images_updater();

require_once('debug.php'); 
 


// add_action( 'admin_init', 'codeneric_phmm_fast_images_updater' ); 
  
