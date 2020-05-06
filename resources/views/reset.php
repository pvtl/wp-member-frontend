<?php
/**
 * Reset password view.
 *
 * @package MemberFrontend
 */

?>
<form method="POST" action="<?php echo esc_url( get_permalink() ); ?>">
	<h4>Reset Password</h4>

	<?php if ( $this->has_flash( 'error' ) ) { ?>
		<div class="callout alert"><?php echo esc_html( $this->get_flash( 'error' ) ); ?></div>
	<?php } ?>

	<fieldset>
		<div>
			<label for="password">New password</label>
			<input type="password" id="password" name="password">
		</div>

		<div>
			<label for="confirm_password">Confirm new password</label>
			<input type="password" id="confirm_password" name="confirm_password">
		</div>
	</fieldset>

	<div>
		<input type="submit" value="Reset Password" class="button" name="submit">
		<input type="hidden" name="action" value="reset">
		<input type="hidden" name="key" value="<?php echo wp_unslash( $_GET['key'] ); ?>">
		<input type="hidden" name="login" value="<?php echo wp_unslash( $_GET['login'] ); ?>">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>">
	</div>
</form>
