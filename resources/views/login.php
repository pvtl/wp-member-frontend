<?php
/**
 * Login view.
 *
 * @package MemberFrontend
 */

?>

<?php if ( $this->has_flash( 'error' ) ) { ?>
	<div class="callout alert"><?php echo esc_html( $this->get_flash( 'error' ) ); ?></div>
<?php } ?>

<?php if ( $this->has_flash( 'success' ) ) { ?>
	<div class="callout success"><?php echo esc_html( $this->get_flash( 'success' ) ); ?></div>
<?php } ?>

<?php

$args = array(
	'echo'           => true,
	'redirect'       => get_permalink(),
	'form_id'        => 'loginform',
	'label_username' => __( 'Email' ),
	'label_password' => __( 'Password' ),
	'label_remember' => __( 'Remember Me' ),
	'label_log_in'   => __( 'Log In' ),
	'id_username'    => 'user_login',
	'id_password'    => 'user_pass',
	'id_remember'    => 'rememberme',
	'id_submit'      => 'wp-submit',
	'remember'       => true,
	'value_username' => null,
	'value_remember' => true,
);

wp_login_form( $args );

?>

<a href="<?php echo esc_url( get_permalink() ) . '?action=forgot'; ?>">Forgot your password?</a>
