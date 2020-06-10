<?php
/**
 * Member navigation view.
 *
 * @package MemberFrontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

?>
<nav class="member-nav">
	<ul>
		<li>
			<a href="<?php echo $this->url( 'dashboard' ); ?>">Dashboard</a>
		</li>
		<li>
			<a href="<?php echo $this->url( 'account' ); ?>">Account</a>
		</li>
		<li>
			<a href="<?php echo $this->get_logout_url(); ?>">Logout</a>
		</li>
	</ul>
</nav>
