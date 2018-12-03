<?php
/*
    Forgot Password
    - The template used to send password reset
*/
?>

<?php if ( $this->hasFlash( 'error' ) ) { ?>
    <div class="callout alert"><?= $this->getFlash( 'error' ); ?></div>
<?php } ?>

<?php if ( $this->hasFlash( 'success' ) ) { ?>
    <div class="callout success"><?= $this->getFlash( 'success' ); ?></div>
<?php } ?>

<form method="POST" action="<?= get_permalink() ?>">
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
        <input type="hidden" name="redirect_to" value="<?= get_permalink() ?>">
    </div>

</form>
