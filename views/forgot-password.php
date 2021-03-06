<?php
/**
 * Forgot password view.
 *
 * @package Member_Frontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

?>
<form method="post">
	<?php mf_nonce( 'forgot_password' ); ?>

	<h2>Reset your password</h2>
	<p>Please enter your email address. You will receive a link to create a new password via email.</p>

	<?php $this->partial( 'notices' ); ?>

	<fieldset>
		<div>
			<label for="email">Email</label>
			<input type="email" id="email" name="email" autocomplete="email">
		</div>
	</fieldset>

	<div>
		<button type="submit" class="btn btn-primary">Send Password Reset Email</button>

		<a class="btn btn-link" href="<?php echo $this->url( 'login' ); // phpcs:ignore ?>">Login</a>
	</div>
</form>
