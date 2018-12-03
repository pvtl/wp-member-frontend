<?php
/*
    Reset Password
    - The template used to reset password
*/
?>

<form method="POST" action="<?=get_permalink()?>">
    <h4>Reset Password</h4>

    <?php if ( $this->hasFlash( 'error' ) ) { ?>
        <div class="callout alert"><?= $this->getFlash( 'error' ); ?></div>
    <?php } ?>

    <fieldset>
        <div>
            <label for="pass1">New password</label>
            <input type="password" id="pass1" name="pass1" value="">
        </div>

        <div>
            <label for="pass2">Confirm new password</label>
            <input type="password" id="pass2" name="pass2" value="">
        </div>
    </fieldset>

    <div>
        <input type="submit" value="Reset Password" class="button" name="submit">
        <input type="hidden" name="action" value="reset">
        <input type="hidden" name="key" value="<?= wp_unslash( $_GET['key'] ) ?>">
        <input type="hidden" name="login" value="<?= wp_unslash( $_GET['login'] ) ?>">
        <input type="hidden" name="redirect_to" value="<?= get_permalink() ?>">
    </div>

</form>
