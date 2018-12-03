<?php
/*
    Login Form
    - The template used to display the login form
*/
?>

<?php if ( $this->hasFlash( 'error' ) ) { ?>
    <div class="callout alert"><?= $this->getFlash( 'error' ); ?></div>
<?php } ?>

<?php if ( $this->hasFlash( 'success' ) ) { ?>
    <div class="callout success"><?= $this->getFlash( 'success' ); ?></div>
<?php } ?>

<?php
    // Login form arguments.
    $args = array(
        'echo'           => true,
        'redirect'       => get_permalink(),
        'form_id'        => 'loginform',
        'label_username' => __( 'Email' ),
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
