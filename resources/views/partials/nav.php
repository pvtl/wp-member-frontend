<?php
/**
 * Member navigation view.
 *
 * @package MemberFrontend
 */

?>
<nav class="member-nav">
	<ul>
		<li>
			<a href="<?php echo $this->get_page_url( 'dashboard' ); ?>">Dashboard</a>
		</li>
		<li>
			<a href="<?php echo $this->get_logout_url(); ?>">Logout</a>
		</li>
	</ul>
</nav>
