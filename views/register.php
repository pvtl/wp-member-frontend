<?php
/**
 * Registration view.
 *
 * @package Member_Frontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

?>
<form method="post">
	<?php mf_nonce( 'register' ); ?>

	<h2>Register for an account</h2>

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
				value="<?php echo esc_html( $this->old( 'first_name' ) ); ?>"
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
				value="<?php echo esc_html( $this->old( 'last_name' ) ); ?>"
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
				value="<?php echo esc_html( $this->old( 'email' ) ); ?>"
				autocomplete="email"
			>
			<?php if ( $error_message ) { ?>
				<small class="invalid-feedback"><?php echo esc_html( $error_message ); ?></small>
			<?php } ?>
		</div>

		<div class="form-group">
			<?php $error_message = $this->get_error( 'password' ); ?>
			<label for="password">Password</label>
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
			<label for="confirm_password">Confirm Password</label>
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
		<button type="submit" class="btn btn-primary">Submit</button>
	</div>
</form>

<a class="btn btn-link" href="<?php echo $this->url( 'login' ); // phpcs:ignore ?>">
	Already have an account? Login
</a>
