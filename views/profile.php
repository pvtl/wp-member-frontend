<?php
/**
 * Update profile view.
 *
 * @package Member_Frontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 * @var \WP_User $user
 */

?>
<h2>Hi <?php echo esc_html( $user->user_firstname ); ?></h2>

<?php $this->partial( 'nav' ); ?>

<form method="post" data-form-hook="profile">
	<?php mf_nonce( 'profile' ); ?>

	<h3>Update Your Profile</h3>

	<?php $this->partial( 'notices' ); ?>

	<fieldset>
		<div class="form-group">
			<label for="first_name">First Name</label>
			<input id="first_name" name="first_name" type="text" class="form-control" value="<?php echo esc_html( $user->user_firstname ); ?>" autocomplete="given-name">
		</div>

		<div class="form-group">
			<label for="last_name">Last Name</label>
			<input id="last_name" name="last_name" type="text" class="form-control" value="<?php echo esc_html( $user->user_lastname ); ?>" autocomplete="family-name">
		</div>

		<div class="form-group">
			<label for="email">Email</label>
			<input id="email" name="email" type="email" class="form-control" value="<?php echo esc_html( $user->user_email ); ?>" autocomplete="email">
		</div>
	</fieldset>

	<h3>Change your password</h3>
	<p>If you would like to change your password, enter a new one here.</p>

	<fieldset>
		<div class="form-group">
			<label for="password">New password</label>
			<input id="password" name="password" type="password" class="form-control" autocomplete="new-password">
		</div>

		<div class="form-group">
			<label for="confirm_password">Confirm password</label>
			<input id="confirm_password" name="confirm_password" type="password" class="form-control" autocomplete="new-password">
		</div>
	</fieldset>

	<div>
		<button type="submit" class="btn btn-primary">Update Profile</button>
	</div>
</form>
