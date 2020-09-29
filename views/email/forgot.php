<?php
/**
 * Forgot password email notification.
 *
 * @package Member_Frontend
 * @global $site_name
 * @global $user_login
 * @global $reset_url
 */

?>
Someone has requested a password reset for the following account:

Site Name: <?php echo esc_html( $site_name ); ?>

Username: <?php echo esc_html( $user_login ); ?>


If this was a mistake, just ignore this email and nothing will happen.

To reset your password, visit the following address:

<?php echo $reset_url; // phpcs:ignore ?>
