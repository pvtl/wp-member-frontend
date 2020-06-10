<?php
/**
 * Update profile view.
 *
 * @package MemberFrontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

?>
<h2>Hi <?php echo esc_html( $user->user_firstname ); ?></h2>

<?php $this->partial( 'nav' ); ?>

<form method="post">
	<h3>Update your account</h3>

	<?php $this->partial( 'notices' ); ?>

	<fieldset>
		<div>
			<label for="first_name">First name</label>
			<input type="text" id="first_name" name="first_name" value="<?php echo esc_html( $user->user_firstname ); ?>">
		</div>

		<div>
			<label for="last_name">Last name</label>
			<input type="text" id="last_name" name="last_name" value="<?php echo esc_html( $user->user_lastname ); ?>">
		</div>

		<div>
			<label for="email">Email</label>
			<input type="email" id="email" name="email" value="<?php echo esc_html( $user->user_email ); ?>">
		</div>
	</fieldset>

	<h3>Change your password</h3>
	<p>If you would like to change your password, enter a new one here.</p>

	<fieldset>
		<div>
			<label for="password">New password</label>
			<input type="password" id="password" name="password" autocomplete="new-password">
		</div>

		<div>
			<label for="confirm_password">Confirm password</label>
			<input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
		</div>
	</fieldset>

	<div>
		<button type="submit" class="btn btn-primary">Update profile</button>
	</div>
</form>
