<?php

class Photection_Admin_Options {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;


	/**
	 * Start up
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}


	/**
	 * Add options page
	 */
	public function add_plugin_page() {

		// This page will be under "Settings"
		add_options_page(
			'Settings Admin',
			'Photection',
			'manage_options',
			'photection',
			array( $this, 'create_admin_page' )
		);
	}


	/**
	 * Options page callback
	 */
	public function create_admin_page() {

		// Set class property
		$this->options = get_option( 'photection' );

		if( !isset( $this->options['photection_message'])) {
			$this->options['photection_message'] = esc_html__('Copyrighted Image', 'photection');
		}

		?>
		<div class="wrap">
			<h1>Photection</h1>
			<hr>
			<br>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'photection_settings' );
				do_settings_sections( 'photection-main' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}


	/**
	 * Register and add settings
	 */
	public function page_init() {

		register_setting(
			'photection_settings', // Option group
			'photection', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'photection_general_settings', // ID
			'', // Empty Title
			'', // Empty Callback
			'photection-main' // Page
		);

		add_settings_field(
			'photection_message', // ID
			'Copyright Message', // Title
			array( $this, 'photection_message_cb' ), // Callback
			'photection-main', // Page
			'photection_general_settings' // Section
		);

	}


	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 *
	 * @return array
	 */
	public function sanitize( $input ) {

		$new_input = array();

		if ( isset( $input['photection_message'] ) ) {
			$new_input['photection_message'] = sanitize_text_field( $input['photection_message'] );
		}

		return $new_input;
	}


	/**
	 * Get the settings option array and print one of its values
	 */
	public function photection_message_cb() {

		printf(
			'<textarea id="photection_message" name="photection[photection_message]" cols="60">%s</textarea>',
			sanitize_text_field( $this->options['photection_message'] )
		);
		echo '<br> This message will appear when an user right-clicks on your images - keep it short :)';
	}


}

if ( is_admin() ) {
	$my_settings_page = new Photection_Admin_Options();
}