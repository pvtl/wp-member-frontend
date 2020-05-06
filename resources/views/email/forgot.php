<?php
/**
 * Forgot password email notification.
 *
 * @package MemberFrontend
 * @global $site_name, $user_login
 */

?>
Someone has requested a password reset for the following account:

Site Name: <?php echo esc_html( $site_name ); ?>

Username: <?php echo esc_html( $user_login ); ?>

If this was a mistake, just ignore this email and nothing will happen.

To reset your password, visit the following address:

<?php

echo esc_html(
	sprintf(
		'<%1$s?action=reset&key=%2$s&login=%3$s>',
		$this->redirect_to,
		$key,
		esc_url( $user_login )
	)
);
