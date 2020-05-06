<?php
/**
 * Forgot password view.
 *
 * @package MemberFrontend
 */

?>

<?php if ( $this->has_flash( 'error' ) ) { ?>
	<div class="callout alert"><?php echo esc_html( $this->get_flash( 'error' ) ); ?></div>
<?php } ?>

<?php if ( $this->has_flash( 'success' ) ) { ?>
	<div class="callout success"><?php echo esc_html( $this->get_flash( 'success' ) ); ?></div>
<?php } ?>

<form method="POST" action="<?php echo esc_url( get_permalink() ); ?>">
	<h4>Reset Password</h4>
	<p>Please enter your email address. You will receive a link to create a new password via email.</p>

	<fieldset>
		<div>
			<label for="user_login">Email</label>
			<input type="text" id="user_login" name="user_login">
		</div>
	</fieldset>

	<div>
		<input type="submit" value="Send Password Reset Email" class="button" name="submit">
		<input type="hidden" name="action" value="forgot">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>">
	</div>

</form>
