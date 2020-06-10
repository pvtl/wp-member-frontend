<?php
/**
 * Login view.
 *
 * @package MemberFrontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

$this->partial( 'notices' );

wp_login_form(
	array(
		'echo'           => true,
		'redirect'       => $this->url(),
		'form_id'        => 'mf_login_form',
		'label_username' => __( 'Email' ),
		'label_password' => __( 'Password' ),
		'label_remember' => __( 'Remember Me' ),
		'label_log_in'   => __( 'Log In' ),
		'id_username'    => 'user_login',
		'id_password'    => 'user_pass',
		'id_remember'    => 'remember_me',
		'id_submit'      => 'wp_submit',
		'remember'       => true,
		'value_username' => null,
		'value_remember' => true,
	)
);

?>

<a class="btn btn-text" href="<?php echo $this->url( 'forgot_password' ); ?>">Forgot your password?</a>

<a class="btn btn-primary" href="<?php echo $this->url( 'register' ); ?>">Don't have an account? Register now</a>
