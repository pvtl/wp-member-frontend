<h1>Hi <?php echo $user->user_firstname; ?></h1>

<?php echo $this->partial( 'nav' ); ?>

<?php $errors = $this->getFlash( 'error' ); ?>

<?php if ( $this->hasFlash( 'success' ) ) { ?>
    <div class="callout success"><?= $this->getFlash( 'success' ); ?></div>
<?php } ?>

<form method="POST" action="<?= get_permalink() ?>">
    <h4>Update Account</h4>

    <?php if ( is_array( $errors ) ) { ?>
        <div class="callout alert">
            <p>Please fix the errors below.</p>
        </div>
    <?php } ?>

    <fieldset>
        <div class="<?php echo isset( $errors['first_name'] ) ? 'has-error' : '' ?>">
            <label for="first_name">First name</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo $user->user_firstname; ?>" aria-describedby="first_name_help">
            <?php if ( isset( $errors['first_name'] ) ) { ?>
                <p class="help-text" id="first_name_help"><?php echo $errors['first_name']; ?></p>
            <?php } ?>
        </div>

        <div class="<?php echo isset( $errors['last_name'] ) ? 'has-error' : '' ?>">
            <label for="last_name">Last name</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo $user->user_lastname; ?>" aria-describedby="last_name_help">
            <?php if ( isset( $errors['last_name'] ) ) { ?>
                <p class="help-text" id="last_name_help"><?php echo $errors['last_name']; ?></p>
            <?php } ?>
        </div>

        <div class="<?php echo isset( $errors['email'] ) ? 'has-error' : '' ?>">
            <label for="email">Email</label>
            <input type="text" id="email" name="email" value="<?php echo $user->user_email; ?>" aria-describedby="email_help">
            <?php if ( isset( $errors['email'] ) ) { ?>
                <p class="help-text" id="email_help"><?php echo $errors['email']; ?></p>
            <?php } ?>
        </div>
    </fieldset>

    <h4>Change password</h4>
    <p>If you would like to change the password type a new one. Otherwise leave this blank.</p>

    <fieldset>
        <div class="<?php echo isset( $errors['user_pass'] ) ? 'has-error' : '' ?>">
            <label for="pass1">New password</label>
            <input type="password" id="user_pass" name="user_pass" value="" autocomplete="off" aria-describedby="user_pass_help">
            <?php if ( isset( $errors['user_pass'] ) ) { ?>
                <p class="help-text" id="user_pass_help"><?php echo $errors['user_pass']; ?></p>
            <?php } ?>
        </div>

        <div>
            <label for="pass2">Confirm password</label>
            <input type="password" id="pass2" name="pass2" value="" autocomplete="off">
        </div>
    </fieldset>

    <div>
        <input type="submit" value="Update profile" class="button" name="submit">
        <input type="hidden" name="action" value="update-profile">
        <input type="hidden" name="redirect_to" value="<?= get_permalink() ?>">
        <?php wp_nonce_field( 'mf_update_' . $user->ID ); ?>
    </div>

</form>
