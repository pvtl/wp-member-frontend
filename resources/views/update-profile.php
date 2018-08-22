<?php
/*
    Update My Account
    - The template used to display user profile update
*/
?>

<p>
    Logged in as <?=$current_user->user_email?>,</br>
    <a href="<?=$this->getLogoutURL()?>" title="Logout" class="button">Logout</a>
</p>

<?php if (isset($_GET['message-success'])) : ?>
    <div class="callout success"><h5><?=ucfirst($_GET['message-success'])?></h5></div>
<?php endif; ?>

<?php if (isset($_GET['message-error'])) : ?>
    <div class="callout alert"><h5><?=ucfirst($_GET['message-error'])?></h5></div>
<?php endif; ?>

<form method="POST" action="<?=get_permalink()?>">
    <h4>Update Account</h4>

    <fieldset>
        <div>
            <label for="first_name">First name</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo $current_user->user_firstname; ?>">
        </div>

        <div>
            <label for="last_name">Last name</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo $current_user->user_lastname; ?>">
        </div>

        <div>
            <label for="email">Email</label>
            <input type="text" id="email" name="email" value="<?php echo $current_user->user_email; ?>">
        </div>
    </fieldset>

    <h4>Change password</h4>
    <p>If you would like to change the password type a new one. Otherwise leave this blank.</p>

    <fieldset>
        <div>
            <label for="pass1">New password</label>
            <input type="password" id="pass1" name="pass1" value="" autocomplete="off">
        </div>

        <div>
            <label for="pass2">Confirm password</label>
            <input type="password" id="pass2" name="pass2" value="" autocomplete="off">
        </div>
    </fieldset>

    <div>
        <input type="submit" value="Update profile" class="button" name="submit">
        <input type="hidden" name="action" value="update-profile">
        <input type="hidden" name="redirect_to" value="<?=get_permalink()?>">
    </div>

</form>
