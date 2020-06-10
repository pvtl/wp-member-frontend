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

	if ( ! is_array( $error_message ) ) {
		?>
		<div class="alert alert-danger"><?php echo esc_html( $error_message ); ?></div>
		<?php
	} else {
		$this->set_flash( 'error', $error_message );
	}
}

if ( $this->has_flash( 'success' ) ) {
	?>
	<div class="alert alert-success"><?php echo esc_html( $this->get_flash( 'success' ) ); ?></div>
	<?php
}
