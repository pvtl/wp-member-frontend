<?php
/*
    Login Form
    - The template used to display the login form
*/
?>

<?php if (isset($_GET['message-error']) && $_GET['message-error'] == 'failed') : ?>
    <div class="callout alert"><h5>Sorry, login failed</h5></div>
<?php endif; ?>

<?php if (isset($_GET['message-error']) && $_GET['message-error'] == 'token_expired') : ?>
    <div class="callout alert"><h5>Password reset token has expired</h5></div>
<?php endif; ?>

<?php if (isset($_GET['message-success']) && $_GET['message-success'] == 'password_reset_sent') : ?>
    <div class="callout alert"><h5>Check your email for the confirmation link</h5></div>
<?php endif; ?>

<?php if (isset($_GET['message-success']) && $_GET['message-success'] == 'password_reset') : ?>
    <div class="callout alert"><h5>Your password has successfully reset</h5></div>
<?php endif; ?>

<?php
    // Login form arguments.
    $args = array(
        'echo'           => true,
        'redirect'       => get_permalink(),
        'form_id'        => 'loginform',
        'label_username' => __( 'Username' ),
        'label_password' => __( 'Password' ),
        'label_remember' => __( 'Remember Me' ),
        'label_log_in'   => __( 'Log In' ),
        'id_username'    => 'user_login',
        'id_password'    => 'user_pass',
        'id_remember'    => 'rememberme',
        'id_submit'      => 'wp-submit',
        'remember'       => true,
        'value_username' => NULL,
        'value_remember' => true
    );
    wp_login_form( $args );
?>

<a href="<?php echo get_permalink() . '?action=forgot'; ?>">Forgot your password?</a>
