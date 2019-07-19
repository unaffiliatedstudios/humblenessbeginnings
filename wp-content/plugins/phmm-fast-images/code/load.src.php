<?php
//mimic the actuall admin-ajax

//make sure you update this line 
//to the relative location of the wp-load.php
//wp/wp-content/plugins/phmm/code/protect_images

//CODENERIC_BEGIN_LOCATION_DEFINITION
define(
  'CODENERIC_FI_WP_LOAD_PATH',
  dirname(dirname(dirname(dirname(dirname(__FILE__)))))
);
//CODENERIC_END_LOCATION_DEFINITION

define('SHORTINIT', true);
require_once(CODENERIC_FI_WP_LOAD_PATH."wp-load.php");

function filter_other_plugins($aps) {
  remove_filter('pre_option_active_plugins', 'filter_other_plugins');
  $aps = get_option('active_plugins', array());
  add_filter('pre_option_active_plugins', 'filter_other_plugins');
  return array_filter(
    $aps,
    function($e) {
      return strpos($e, 'photography_management.php') !== false ||
        strpos($e, 'phmm_fast_images.php') !== false ||
        strpos($e, 'photography_management-premium.php') !== false;
    }
  );
}

add_filter(
  'pre_option_active_plugins',
  'filter_other_plugins'
); //load only our stuff!
//Include only the files and function we need
require_once(ABSPATH.WPINC.'/l10n.php');
// require_once( ABSPATH . WPINC . '/class-wp-locale.php' );
// require_once( ABSPATH . WPINC . '/class-wp-locale-switcher.php' );

// Run the installer if WordPress is not installed.
// wp_not_installed();

// Load most of WordPress.
require(ABSPATH.WPINC.'/class-wp-walker.php'); // REQUIRED
// require( ABSPATH . WPINC . '/class-wp-ajax-response.php' ); 
// require(ABSPATH.WPINC.'/formatting.php'); // 
require(ABSPATH.WPINC.'/capabilities.php'); // REQUIRED
require(ABSPATH.WPINC.'/class-wp-roles.php');
require(ABSPATH.WPINC.'/class-wp-role.php');
require(ABSPATH.WPINC.'/class-wp-user.php');
require(ABSPATH.WPINC.'/class-wp-query.php'); //REQUIRED
// require( ABSPATH . WPINC . '/query.php' );
// require( ABSPATH . WPINC . '/date.php' );
require(ABSPATH.WPINC.'/theme.php'); //REQUIRED
// require( ABSPATH . WPINC . '/class-wp-theme.php' );
// require( ABSPATH . WPINC . '/template.php' );
require(ABSPATH.WPINC.'/user.php');
require(ABSPATH.WPINC.'/class-wp-user-query.php');
require(ABSPATH.WPINC.'/class-wp-session-tokens.php');
require(ABSPATH.WPINC.'/class-wp-user-meta-session-tokens.php');

// require(ABSPATH.WPINC.'/meta.php'); //
// require(ABSPATH.WPINC.'/class-wp-meta-query.php'); //
// require( ABSPATH . WPINC . '/class-wp-metadata-lazyloader.php' );
require(ABSPATH.WPINC.'/general-template.php'); //REQUIRED
require(ABSPATH.WPINC.'/link-template.php'); //REQUIRED
// require( ABSPATH . WPINC . '/author-template.php' );
require(ABSPATH.WPINC.'/post.php'); //REQUIRED
// require( ABSPATH . WPINC . '/class-walker-page.php' );
// require( ABSPATH . WPINC . '/class-walker-page-dropdown.php' );
// require( ABSPATH . WPINC . '/class-wp-post-type.php' );
require(ABSPATH.WPINC.'/class-wp-post.php'); //REQUIRED
// require( ABSPATH . WPINC . '/post-template.php' );

// require( ABSPATH . WPINC . '/revision.php' );
// require( ABSPATH . WPINC . '/post-formats.php' );
// require( ABSPATH . WPINC . '/post-thumbnail-template.php' );
// require( ABSPATH . WPINC . '/category.php' );
// require( ABSPATH . WPINC . '/class-walker-category.php' );
// require( ABSPATH . WPINC . '/class-walker-category-dropdown.php' );
// require( ABSPATH . WPINC . '/category-template.php' );
// require( ABSPATH . WPINC . '/comment.php' );
// require( ABSPATH . WPINC . '/class-wp-comment.php' );
// require( ABSPATH . WPINC . '/class-wp-comment-query.php' );
// require( ABSPATH . WPINC . '/class-walker-comment.php' );
// require( ABSPATH . WPINC . '/comment-template.php' );

// require( ABSPATH . WPINC . '/rewrite.php' );
// require( ABSPATH . WPINC . '/class-wp-rewrite.php' );
// require( ABSPATH . WPINC . '/feed.php' );
// require( ABSPATH . WPINC . '/bookmark.php' );
// require( ABSPATH . WPINC . '/bookmark-template.php' );
require(ABSPATH.WPINC.'/kses.php'); //REQUIRED
// require( ABSPATH . WPINC . '/cron.php' );
// require( ABSPATH . WPINC . '/deprecated.php' );
// require( ABSPATH . WPINC . '/script-loader.php' );
require(ABSPATH.WPINC.'/taxonomy.php'); // REQUIRED
// require( ABSPATH . WPINC . '/class-wp-taxonomy.php' );
// require( ABSPATH . WPINC . '/class-wp-term.php' );
// require( ABSPATH . WPINC . '/class-wp-term-query.php' );
require(ABSPATH.WPINC.'/class-wp-tax-query.php'); //REQUIRED
// require( ABSPATH . WPINC . '/update.php' );
// require( ABSPATH . WPINC . '/canonical.php' );
require(ABSPATH.WPINC.'/shortcodes.php');
// require( ABSPATH . WPINC . '/embed.php' );
// require( ABSPATH . WPINC . '/class-wp-embed.php' );  //REQUIRED
// require( ABSPATH . WPINC . '/class-oembed.php' );
// require( ABSPATH . WPINC . '/class-wp-oembed-controller.php' );
require(ABSPATH.WPINC.'/media.php'); //REQUIRED
// require( ABSPATH . WPINC . '/http.php' );
// require( ABSPATH . WPINC . '/class-http.php' );
// require( ABSPATH . WPINC . '/class-wp-http-streams.php' );
// require( ABSPATH . WPINC . '/class-wp-http-curl.php' );
// require( ABSPATH . WPINC . '/class-wp-http-proxy.php' );
// require( ABSPATH . WPINC . '/class-wp-http-cookie.php' );
// require( ABSPATH . WPINC . '/class-wp-http-encoding.php' );
// require( ABSPATH . WPINC . '/class-wp-http-response.php' );
// require( ABSPATH . WPINC . '/class-wp-http-requests-response.php' );
// require( ABSPATH . WPINC . '/class-wp-http-requests-hooks.php' );
// require( ABSPATH . WPINC . '/widgets.php' );
// require( ABSPATH . WPINC . '/class-wp-widget.php' );
// require( ABSPATH . WPINC . '/class-wp-widget-factory.php' );
// require( ABSPATH . WPINC . '/nav-menu.php' );
// require( ABSPATH . WPINC . '/nav-menu-template.php' );
// require( ABSPATH . WPINC . '/admin-bar.php' );
require(ABSPATH.WPINC.'/rest-api.php'); // REQUIRED
// require( ABSPATH . WPINC . '/rest-api/class-wp-rest-server.php' );
// require( ABSPATH . WPINC . '/rest-api/class-wp-rest-response.php' );
// require( ABSPATH . WPINC . '/rest-api/class-wp-rest-request.php' );
// require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-controller.php' );
// require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-posts-controller.php' );
// require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-attachments-controller.php' );
// require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-post-types-controller.php' );
// require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-post-statuses-controller.php' );
// require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-revisions-controller.php' );
// require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-taxonomies-controller.php' );
// require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-terms-controller.php' );
// require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-users-controller.php' );
// require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-comments-controller.php' );
// require( ABSPATH . WPINC . '/rest-api/endpoints/class-wp-rest-settings-controller.php' );
// require( ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-meta-fields.php' );
// require( ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-comment-meta-fields.php' );
// require( ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-post-meta-fields.php' );
// require( ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-term-meta-fields.php' );
// require( ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-user-meta-fields.php' );

// $GLOBALS['wp_embed'] = new WP_Embed();

// Load multisite-specific files.
if (is_multisite()) {
  require(ABSPATH.WPINC.'/ms-functions.php');
  require(ABSPATH.WPINC.'/ms-default-filters.php');
  require(ABSPATH.WPINC.'/ms-deprecated.php');
}

// Define constants that rely on the API to obtain the default value.
// Define must-use plugin directory constants, which may be overridden in the sunrise.php drop-in.
wp_plugin_directory_constants();

$GLOBALS['wp_plugin_paths'] = array();

// Load must-use plugins.
// foreach ( wp_get_mu_plugins() as $mu_plugin ) {
// 	include_once( $mu_plugin );
// }
// unset( $mu_plugin );

// Load network activated plugins.
// if ( is_multisite() ) {
// 	foreach ( wp_get_active_network_plugins() as $network_plugin ) {
// 		wp_register_plugin_realpath( $network_plugin );
// 		include_once( $network_plugin );
// 	}
// 	unset( $network_plugin );
// }

/**
 * Fires once all must-use and network-activated plugins have loaded.
 *
 * @since 2.8.0
 */
do_action('muplugins_loaded');

if (is_multisite())
  ms_cookie_constants();

// Define constants after multisite is loaded.
wp_cookie_constants();

// Define and enforce our SSL constants
wp_ssl_constants();

// Create common globals.
require(ABSPATH.WPINC.'/vars.php');

// Make taxonomies and posts available to plugins and themes.
// @plugin authors: warning: these get registered again on the init hook.
// create_initial_taxonomies();
// create_initial_post_types();

// wp_start_scraping_edited_file_errors();

// // Register the default theme directory rootcreate_initial_taxonomies();
// create_initial_post_types();

// wp_start_scraping_edited_file_errors();

// // Register the default theme directory root
// register_theme_directory( get_theme_root() );
// register_theme_directory( get_theme_root() );

// Load active plugins.
$active_and_valid = wp_get_active_and_valid_plugins();

foreach ($active_and_valid as $plugin) {
  wp_register_plugin_realpath($plugin);
  include_once($plugin);
}
unset($plugin);

// Load pluggable functions.
require(ABSPATH.WPINC.'/pluggable.php'); //only defines stuff
// require( ABSPATH . WPINC . '/pluggable-deprecated.php' );

// Set internal encoding.
// wp_set_internal_encoding();

// Run wp_cache_postload() if object cache is enabled and the function exists.
if (WP_CACHE && function_exists('wp_cache_postload'))
  wp_cache_postload();


codeneric_send_image_if_allowed();
