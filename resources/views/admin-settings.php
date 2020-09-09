<?php
/**
 * Admin settings view.
 *
 * @package MemberFrontend
 *
 * @var \App\Plugins\Pvtl\Classes\Member_Frontend $this
 */

$this->partial( 'notices' );

$action = isset( $_POST['action'] ) ? $_POST['action'] : ''; // phpcs:ignore

if ( ! empty( $action ) ) {
	check_admin_referer( $action );

	if ( 'set-members-page' === $action ) {
		$members_page_id = isset( $_POST['page_for_members'] ) ? (int) $_POST['page_for_members'] : 0;
		update_option( 'mf_page_for_members', $members_page_id );

		$updated_message = __( 'Privacy Policy page updated successfully.' );

		add_settings_error( 'page_for_members', 'page_for_members', $updated_message, 'success' );
	}
}

$members_page_id = (int) get_option( 'mf_page_for_members' );

?>
<div class="wrap">
	<h1>Members Settings</h1>
	<h2>Members Page</h2>
	<p>Select the page which acts as the members area.</p>
	<table class="form-table tools-members-page" role="presentation">
		<tbody>
		<tr>
			<th scope="row">
				<label for="page_for_members">Select your Members page</label>
			</th>
			<td>
				<form method="post">
					<input type="hidden" name="action" value="set-members-page">
					<?php

					wp_dropdown_pages(
						array(
							'name'              => 'page_for_members',
							'show_option_none'  => esc_attr( '&mdash; Select &mdash;' ),
							'option_none_value' => '0',
							'selected'          => $members_page_id,
							'post_status'       => array( 'draft', 'publish' ),
						)
					);

					wp_nonce_field( 'set-members-page' );

					submit_button( __( 'Use This Page' ), 'primary', 'submit', false, array( 'id' => 'set-page' ) );

					?>
				</form>
			</td>
		</tr>
		</tbody>
	</table>
</div>
