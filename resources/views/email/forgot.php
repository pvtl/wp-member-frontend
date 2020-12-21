<?php
/**
 * Forgot password email notification.
 *
 * @package MemberFrontend
 */

?>
Someone has requested a password reset for the following account:

Site Name: <?php echo esc_html( $site_name ); ?>

Username: <?php echo esc_html( $user_login ); ?>


If this was a mistake, just ignore this email and nothing will happen.

To reset your password, visit the following address:

<?php

printf(
	'%s?action=reset&key=%s&login=%s',
	esc_url( $this->redirect_to ),
	esc_attr( $key ),
	$user_login
);
