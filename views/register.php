<?php
/**
 * Registration view.
 *
 * @package Member_Frontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

?>
<form method="post" data-form-hook="register">
	<?php mf_nonce( 'register' ); ?>

	<h2>Register</h2>

	<?php $this->partial( 'notices' ); ?>

	<div class="form-group">
		<label for="first_name">First Name</label>
		<input id="first_name" name="first_name" type="text" class="form-control" autocomplete="given-name">
	</div>

	<div class="form-group">
		<label for="last_name">Last Name</label>
		<input id="last_name" name="last_name" type="text" class="form-control" autocomplete="family-name">
	</div>

	<div class="form-group">
		<label for="email">Email</label>
		<input id="email" name="email" type="email" class="form-control" autocomplete="email">
	</div>

	<div class="form-group">
		<label for="password">Password</label>
		<input id="password" name="password" type="password" class="form-control" autocomplete="new-password">
	</div>

	<div class="form-group">
		<label for="confirm_password">Confirm Password</label>
		<input id="confirm_password" name="confirm_password" type="password" class="form-control" autocomplete="new-password">
	</div>

	<button type="submit" class="btn btn-primary">Register <span style="display: none;" class="spinner"><i class="fal fa-spinner-third fa-spin"></i></span></button>

	<a class="btn btn-light" href="<?php echo esc_url( $this->url( 'login' ) ); ?>">Already have an account? Login</a>
</form>
