<?php
/**
 * Registration view.
 *
 * @package MemberFrontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

?>
<form method="post">
	<h2>Register</h2>

	<?php $this->partial( 'notices' ); ?>

	<fieldset>
		<div>
			<label for="first_name">First Name</label>
			<input type="text" id="first_name" name="first_name" autocomplete="given-name">
		</div>

		<div>
			<label for="last_name">Last Name</label>
			<input type="text" id="last_name" name="last_name" autocomplete="family-name">
		</div>

		<div>
			<label for="email">Email</label>
			<input type="email" id="email" name="email" autocomplete="email">
		</div>

		<div>
			<label for="password">Password</label>
			<input type="password" id="password" name="password" autocomplete="new-password">
		</div>

		<div>
			<label for="confirm_password">Confirm Password</label>
			<input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
		</div>
	</fieldset>

	<div>
		<button type="submit" class="btn">Submit</button>
	</div>
</form>
