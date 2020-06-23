<?php
/**
 * Reset password view.
 *
 * @package MemberFrontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 * @global $key
 * @global $login
 */

?>
<form method="post">
	<?php wp_nonce_field( 'mf_form_nopriv', 'mf_nonce' ); ?>

	<h2>Reset your password</h2>

	<?php $this->partial( 'notices' ); ?>

	<fieldset>
		<div>
			<label for="password">New password</label>
			<input type="password" id="password" name="password">
		</div>

		<div>
			<label for="confirm_password">Confirm new password</label>
			<input type="password" id="confirm_password" name="confirm_password">
		</div>
	</fieldset>

	<div>
		<button type="submit" class="btn btn-primary">Reset Password</button>
		<input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>">
		<input type="hidden" name="login" value="<?php echo esc_attr( $login ); ?>">
	</div>
</form>
