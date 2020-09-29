<?php
/**
 * Member navigation view.
 *
 * @package Member_Frontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- The URLs have already been escaped.

?>
<nav class="member-nav">
	<ul>
		<li>
			<a href="<?php echo $this->url( 'dashboard' ); ?>">Dashboard</a>
		</li>
		<li>
			<a href="<?php echo $this->url( 'profile' ); ?>">Profile</a>
		</li>
		<li>
			<a href="<?php echo $this->get_logout_url(); ?>">Logout</a>
		</li>
	</ul>
</nav>
