<?php
/**
 * Forgot password view.
 *
 * @package MemberFrontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

?>
<form method="post">
	<h4>Reset Password</h4>
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

		<a href="<?php echo $this->url( 'login' ); ?>">Login</a>
	</div>
</form>
