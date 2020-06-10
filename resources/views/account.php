<?php
/**
 * Update profile view.
 *
 * @package MemberFrontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

?>
<h1>Hi <?php echo esc_html( $user->user_firstname ); ?></h1>

<?php $this->partial( 'nav' ); ?>

<form method="post">
	<h4>Update Account</h4>

	<?php $this->partial( 'notices' ); ?>

	<fieldset>
		<div class="<?php echo isset( $update_errors['first_name'] ) ? 'has-error' : ''; ?>">
			<label for="first_name">First name</label>
			<input type="text" id="first_name" name="first_name" value="<?php echo $user->user_firstname; ?>" aria-describedby="first_name_help">
			<?php if ( isset( $update_errors['first_name'] ) ) { ?>
				<p class="help-text" id="first_name_help"><?php echo esc_html( $update_errors['first_name'] ); ?></p>
			<?php } ?>
		</div>

		<div class="<?php echo isset( $update_errors['last_name'] ) ? 'has-error' : ''; ?>">
			<label for="last_name">Last name</label>
			<input type="text" id="last_name" name="last_name" value="<?php echo $user->user_lastname; ?>" aria-describedby="last_name_help">
			<?php if ( isset( $update_errors['last_name'] ) ) { ?>
				<p class="help-text" id="last_name_help"><?php echo esc_html( $update_errors['last_name'] ); ?></p>
			<?php } ?>
		</div>

		<div class="<?php echo isset( $update_errors['email'] ) ? 'has-error' : ''; ?>">
			<label for="email">Email</label>
			<input type="text" id="email" name="email" value="<?php echo $user->user_email; ?>" aria-describedby="email_help">
			<?php if ( isset( $update_errors['email'] ) ) { ?>
				<p class="help-text" id="email_help"><?php echo esc_html( $update_errors['email'] ); ?></p>
			<?php } ?>
		</div>
	</fieldset>

	<h4>Change password</h4>
	<p>If you would like to change the password type a new one. Otherwise leave this blank.</p>

	<fieldset>
		<div class="<?php echo isset( $update_errors['user_pass'] ) ? 'has-error' : ''; ?>">
			<label for="user_pass">New password</label>
			<input type="password" id="user_pass" name="user_pass" autocomplete="off" aria-describedby="user_pass_help">
			<?php if ( isset( $update_errors['user_pass'] ) ) { ?>
				<p class="help-text" id="user_pass_help"><?php echo esc_html( $update_errors['user_pass'] ); ?></p>
			<?php } ?>
		</div>

		<div>
			<label for="confirm_password">Confirm password</label>
			<input type="password" id="confirm_password" name="confirm_password" autocomplete="off">
		</div>
	</fieldset>

	<div>
		<input type="submit" value="Update profile" class="button" name="submit">
		<input type="hidden" name="action" value="update-profile">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>">
		<?php wp_nonce_field( 'mf_update_' . $user->ID ); ?>
	</div>
</form>
