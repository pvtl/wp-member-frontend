<?php
/*
    Forgot Password
    - The template used to send password reset
*/
?>

<?php if (isset($_GET['message-error'])) : ?>
    <div class="callout alert"><h5><?=ucfirst($_GET['message-error'])?></h5></div>
<?php endif; ?>

<form method="POST" action="<?=get_permalink()?>">
    <h4>Reset Password</h4>
    <p>Please enter your username. You will receive a link to create a new password via email.</p>

    <fieldset>
      <div>
        <label for="user_login">Username</label>
        <input type="text" id="user_login" name="user_login" value="">
      </div>
    </fieldset>

    <div>
        <input type="submit" value="Send Password Reset Email" class="button" name="submit">
        <input type="hidden" name="action" value="forgot">
        <input type="hidden" name="redirect_to" value="<?= get_permalink() ?>">
    </div>

</form>
