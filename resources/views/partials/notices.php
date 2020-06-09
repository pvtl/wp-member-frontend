<?php
/**
 * Display flash notices.
 *
 * @package MemberFrontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

?>
<?php if ( $this->has_flash( 'error' ) ) { ?>
	<div class="alert alert-danger"><?php echo esc_html( $this->get_flash( 'error' ) ); ?></div>
<?php } ?>

<?php if ( $this->has_flash( 'success' ) ) { ?>
	<div class="alert alert-success"><?php echo esc_html( $this->get_flash( 'success' ) ); ?></div>
<?php } ?>
