<?php
/*
    Login Form
    - The template used to display the login form
*/
?>

<?php if (isset($_GET['message-error']) && $_GET['message-error'] == 'failed') : ?>
    <div class="callout alert"><h5>Sorry, login failed</h5></div>
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
