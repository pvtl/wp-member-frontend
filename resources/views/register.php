<?php
/**
 * Registration view.
 *
 * @package MemberFrontend
 */

$registration_errors = $this->get_flash( 'error' );

?>
<form method="POST" action="<?php echo esc_url( get_permalink() ); ?>">
	<h4>Register</h4>

	<?php if ( $registration_errors ) { ?>
		<div class="alert callout">
			<p><i class="fi-alert"></i> Please fix the errors below.</p>
		</div>
	<?php } ?>

	<fieldset>
		<div class="<?php echo isset( $registration_errors['first_name'] ) ? 'has-error' : ''; ?>">
			<label for="first_name">First Name</label>
			<input type="text" id="first_name" name="first_name" value="<?php echo $this->old( 'first_name' ); ?>" aria-describedby="first_name_help">
			<?php if ( isset( $registration_errors['first_name'] ) ) { ?>
				<p class="help-text" id="first_name_help"><?php echo esc_html( $registration_errors['first_name'] ); ?></p>
			<?php } ?>
		</div>

		<div class="<?php echo isset( $registration_errors['last_name'] ) ? 'has-error' : ''; ?>">
			<label for="last_name">Last Name</label>
			<input type="text" id="last_name" name="last_name" value="<?php echo $this->old( 'last_name' ); ?>" aria-describedby="last_name_help">
			<?php if ( isset( $registration_errors['last_name'] ) ) { ?>
				<p class="help-text" id="last_name_help"><?php echo esc_html( $registration_errors['last_name'] ); ?></p>
			<?php } ?>
		</div>

		<div class="<?php echo isset( $registration_errors['email'] ) ? 'has-error' : ''; ?>">
			<label for="email">Email</label>
			<input type="email" id="email" name="email" value="<?php echo $this->old( 'email' ); ?>" aria-describedby="email_help">
			<?php if ( isset( $registration_errors['email'] ) ) { ?>
				<p class="help-text" id="email_help"><?php echo esc_html( $registration_errors['email'] ); ?></p>
			<?php } ?>
		</div>

		<div class="<?php echo isset( $registration_errors['user_pass'] ) ? 'has-error' : ''; ?>">
			<label for="user_pass">Password</label>
			<input type="password" id="user_pass" name="user_pass" autocomplete="off" aria-describedby="user_pass_help">
			<?php if ( isset( $registration_errors['user_pass'] ) ) { ?>
				<p class="help-text" id="user_pass_help"><?php echo esc_html( $registration_errors['user_pass'] ); ?></p>
			<?php } ?>
		</div>

		<div>
			<label for="pass2">Confirm Password</label>
			<input type="password" id="pass2" name="pass2" autocomplete="off">
		</div>
	</fieldset>

	<div>
		<input type="submit" value="Register" class="button" name="submit">
		<input type="hidden" name="action" value="register">
		<?php wp_nonce_field( 'mf_register' ); ?>
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>">
	</div>

</form>
