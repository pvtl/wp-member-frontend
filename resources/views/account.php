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
	<?php wp_nonce_field( "mf_form_priv_{$user->ID}", 'mf_nonce' ); ?>

	<h3>Update your account</h3>

	<?php $this->partial( 'notices' ); ?>

	<fieldset>
		<div class="form-group">
			<?php $error_message = $this->get_error( 'first_name' ); ?>
			<label for="first_name">First Name</label>
			<input
				id="first_name"
				name="first_name"
				type="text"
				class="form-control<?php echo $error_message ? ' is-invalid' : ''; ?>"
				value="<?php echo esc_html( $user->user_firstname ); ?>"
				autocomplete="given-name"
			>
			<?php if ( $error_message ) { ?>
				<small class="invalid-feedback"><?php echo esc_html( $error_message ); ?></small>
			<?php } ?>
		</div>

		<div class="form-group">
			<?php $error_message = $this->get_error( 'last_name' ); ?>
			<label for="last_name">Last Name</label>
			<input
				id="last_name"
				name="last_name"
				type="text"
				class="form-control<?php echo $error_message ? ' is-invalid' : ''; ?>"
				value="<?php echo esc_html( $user->user_lastname ); ?>"
				autocomplete="family-name"
			>
			<?php if ( $error_message ) { ?>
				<small class="invalid-feedback"><?php echo esc_html( $error_message ); ?></small>
			<?php } ?>
		</div>

		<div class="form-group">
			<?php $error_message = $this->get_error( 'email' ); ?>
			<label for="email">Email</label>
			<input
				id="email"
				name="email"
				type="email"
				class="form-control<?php echo $error_message ? ' is-invalid' : ''; ?>"
				value="<?php echo esc_html( $user->user_email ); ?>"
				autocomplete="email"
			>
			<?php if ( $error_message ) { ?>
				<small class="invalid-feedback"><?php echo esc_html( $error_message ); ?></small>
			<?php } ?>
		</div>
	</fieldset>

	<h3>Change your password</h3>
	<p>If you would like to change your password, enter a new one here.</p>

	<fieldset>
		<div class="form-group">
			<?php $error_message = $this->get_error( 'password' ); ?>
			<label for="password">New password</label>
			<input
				id="password"
				name="password"
				type="password"
				class="form-control<?php echo $error_message ? ' is-invalid' : ''; ?>"
				autocomplete="new-password"
			>
			<?php if ( $error_message ) { ?>
				<small class="invalid-feedback"><?php echo esc_html( $error_message ); ?></small>
			<?php } ?>
		</div>

		<div class="form-group">
			<label for="confirm_password">Confirm password</label>
			<input
				id="confirm_password"
				name="confirm_password"
				type="password"
				class="form-control"
				autocomplete="new-password"
			>
		</div>
	</fieldset>

	<div>
		<button type="submit" class="btn btn-primary">Update profile</button>
	</div>
</form>
