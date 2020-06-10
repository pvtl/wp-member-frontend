<?php
/**
 * Reset password view.
 *
 * @package MemberFrontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

?>
<form method="post">
	<h4>Reset Password</h4>

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
		<input type="hidden" name="key" value="<?php echo wp_unslash( $_GET['key'] ); // phpcs:ignore ?>">
		<input type="hidden" name="login" value="<?php echo wp_unslash( $_GET['login'] ); // phpcs:ignore ?>">
	</div>
</form>
