<?php
/*
    Register
    - The template used to register
*/
?>

<?php if (isset($_GET['message-success'])) : ?>
    <div class="callout success"><h5><?=ucfirst($_GET['message-success'])?></h5></div>
<?php endif; ?>

<?php if (isset($_GET['message-error'])) : ?>
    <div class="callout alert"><h5><?=ucfirst($_GET['message-error'])?></h5></div>
<?php endif; ?>

<form method="POST" action="<?=get_permalink()?>">
    <h4>Register</h4>

    <fieldset>
      <div>
        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" value="">
      </div>

      <div>
        <label for="last_name">Last Name</label>
        <input type="text" id="last_name" name="last_name" value="">
      </div>

      <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="">
      </div>

      <div>
        <label for="pass1">Password</label>
        <input type="password" id="pass1" name="pass1" value="" autocomplete="off">
      </div>

      <div>
        <label for="pass2">Confirm Password</label>
        <input type="password" id="pass2" name="pass2" value="" autocomplete="off">
      </div>

    </fieldset>

    <div>
        <input type="submit" value="Register" class="button" name="submit">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="redirect_to" value="<?=get_permalink()?>">
    </div>

</form>
