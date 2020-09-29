<?php
/**
 * Dashboard view.
 *
 * @package Member_Frontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

$this->partial( 'notices' );

?>
<h2>Hi <?php echo esc_html( $user->user_firstname ); ?></h2>

<?php $this->partial( 'nav' ); ?>

<?php echo $content; // phpcs:ignore ?>
