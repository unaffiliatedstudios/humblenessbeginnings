<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://colormelon.com
 * @since      1.0.0
 *
 * @package    Photection
 * @subpackage Photection/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Photection
 * @subpackage Photection/public
 * @author     justnorris <im@justnorris.com>
 */
class Photection_View {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;


	/**
	 * Initialize the class and set its properties.x
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}


	public function enqueue_styles() {

		wp_enqueue_style( 'photection-style', plugin_dir_url( __FILE__ ) . 'resources/build/photection.css', array(), $this->version );

		/**
		 * In the future add an option to change protected selectors
		 */
		$selectors = 'img';

		/*
		 * CSS to protect images
		 */
		$protection_css = "
		{$selectors} {
			-webkit-user-drag: none;
			user-drag: none;
			-webkit-touch-callout: none;
			-webkit-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none; 
		}";

		wp_add_inline_style( 'photection-style', $protection_css );

	}


	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'resources/build/photection.js',
			array( 'jquery' ),
			$this->version,
			true
		);

	}


	public function protection_message() {

		require_once __DIR__ . '/partials/photection-modal.php';

	}
}
