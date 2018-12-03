Someone has requested a password reset for the following account:

<?= sprintf(__('Site Name: %s'), $site_name); ?>

<?= sprintf(__('Username: %s'), $user_login); ?>

If this was a mistake, just ignore this email and nothing will happen.

To reset your password, visit the following address:

<?= sprintf('<%1$s?action=reset&key=%2$s&login=%3$s>', $this->redirectTo, $key, urlencode( $user_login ) ) ?>
