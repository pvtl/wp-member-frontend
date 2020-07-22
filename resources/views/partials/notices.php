<?php
/**
 * Display flash notices.
 *
 * @package MemberFrontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

if ( $this->has_flash( 'error' ) ) {
	$error_message = $this->get_flash( 'error' );

	// Check if the error message is an array of form input
	// errors. If so, these will be displayed individually with
	// the form field.
	if ( ! is_array( $error_message ) ) {
		?>
		<div class="alert alert-danger">
			<?php echo esc_html( $error_message ); ?>
		</div>
		<?php
	} else {
		// Add the error messages back into flash for later use.
		$this->set_flash( 'error', $error_message );

		?>
		<div class="alert alert-danger">Validation errors occurred</div>
		<?php
	}
}

if ( $this->has_flash( 'success' ) ) {
	?>
	<div class="alert alert-success"><?php echo esc_html( $this->get_flash( 'success' ) ); ?></div>
	<?php
}
