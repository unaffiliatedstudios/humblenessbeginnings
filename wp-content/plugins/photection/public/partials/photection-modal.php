<?php
/**
 *
 * @link       http://colormelon.com
 * @since      1.0.0
 *
 * @package    Photection
 * @subpackage Photection/public/partials
 */

$message = photection_get_option( 'photection_message' );
if ( empty( $message ) ) {
	$message = esc_html__( 'Copyrighted Image', 'photection' );
}
?>
<div id="photection" class="photection-modal">
	<div id="photection-message" class="photection__message-wrapper">
		<p class="photection__message"><?php echo wp_kses_post( $message ) ?></p>
	</div>
</div>
