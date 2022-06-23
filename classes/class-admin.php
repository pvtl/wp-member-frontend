<?php
/**
 * Admin filters.
 *
 * @package Member_Frontend
 *
 * phpcs:disable WordPress.Security.NonceVerification
 */

namespace App\Plugins\Pvtl\Classes;

use \WP_User_Query;

defined( 'ABSPATH' ) || die;

/**
 * Class Admin
 *
 * @package Member_Frontend
 */
class Admin {
	/**
	 * Admin constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'setup_settings_page' ) );

		// Add the filter form fields.
		add_action( 'manage_users_extra_tablenav', array( $this, 'add_member_filters' ) );

		// Apply the member filters.
		add_filter( 'pre_get_users', array( $this, 'filter_members' ) );
	}

	/**
	 * Setup the admin settings page.
	 */
	public function setup_settings_page() {
		add_submenu_page(
			'options-general.php',
			'Members',
			'Members',
			'edit_posts',
			'mf_settings',
			array( $this, 'do_settings_page' )
		);
	}

	/**
	 * Callback for the settings page.
	 */
	public function do_settings_page() {
		$user = MF()->get_current_user();
		$vars = array(
			'user' => $user,
		);

		$view = MF()->view( 'admin/settings', $vars );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $view;
	}

	/**
	 * Add the member filter fields to the users filter.
	 *
	 * @param string $which The location of the extra table nav markup.
	 */
	public function add_member_filters( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$member_filters = apply_filters( 'mf_member_filters', array() );

		if ( empty( $member_filters ) ) {
			return;
		}

		?>
		<style>
			.mf_actions {
				float: none;
				margin: 1rem 0 0;
			}
		</style>
		<?php

		echo '<br class="clear"><div class="alignleft actions mf_actions">';

		$text_template   = '<input type="text" style="margin-right: .5rem;" name="mf_filter_%s" placeholder="%s" value="%s">';
		$date_template   = '<label style="vertical-align: initial; padding-right: .25rem;">%s</label><input type="date" name="mf_filter_%s" value="%s" style="margin-right: .5rem;">';
		$select_template = '<select name="mf_filter_%s" style="margin-right: .5rem;"><option value="">%s</option>%s</select>';
		$option_template = '<option value="%s"%s>%s</option>';

		$filter_values = array_filter(
			$_GET,
			function ( $k ) {
				return strpos( $k, 'mf_' ) === 0;
			},
			ARRAY_FILTER_USE_KEY
		);

		foreach ( $member_filters as $name => $filter ) {
			$value = isset( $filter_values[ "mf_filter_{$name}" ] ) ? $filter_values[ "mf_filter_{$name}" ] : null;

			if ( ! isset( $filter['type'] ) || 'text' === $filter['type'] ) {
				printf( $text_template, esc_attr( $name ), esc_attr( $filter['placeholder'] ), esc_attr( $value ) ); // phpcs:ignore
			}

			if ( 'date' === $filter['type'] ) {
				printf( $date_template, esc_attr( $filter['placeholder'] ), esc_attr( $name ), esc_attr( $value ) ); // phpcs:ignore
			}

			if ( 'select' === $filter['type'] ) {
				$options = array_map(
					function ( $option ) use ( $option_template, $name, $value ) {
						$selected = $value === $option['value'] ? ' selected' : '';

						return sprintf( $option_template, esc_attr( $option['value'] ), $selected, esc_html( $option['label'] ) );
					},
					$filter['options']
				);

				printf( $select_template, esc_attr( $name ), esc_attr( $filter['placeholder'] ), implode( "\n", $options ) ); // phpcs:ignore
			}
		}

		submit_button( 'Filter', 'primary', "mf_filter_{$which}", false );

		echo '&nbsp;<a href="' . esc_url( get_admin_url( null, 'users.php' ) ) . '" class="button">Clear Form</a></div>';
	}

	/**
	 * Filter the members table.
	 *
	 * @param WP_User_Query $query The query.
	 */
	public function filter_members( $query ) {
		global $pagenow;

		$member_filters = apply_filters( 'mf_member_filters', array() );

		if ( ! is_admin() || 'users.php' !== $pagenow || empty( $member_filters ) ) {
			return;
		}

		$filter_values = array_filter(
			$_GET,
			function ( $k ) {
				return strpos( $k, 'mf_' ) === 0;
			},
			ARRAY_FILTER_USE_KEY
		);

		$meta_query = $query->meta_query;

		foreach ( $filter_values as $filter => $value ) {
			$filter_name = str_replace( 'mf_filter_', '', $filter );

			if ( ! $value || ! isset( $member_filters[ $filter_name ] ) ) {
				continue;
			}

			$filter_queries = array(
				array(
					'key'   => $filter_name,
					'value' => $value,
				),
			);

			$filter_queries = apply_filters( 'mf_filter_meta', $filter_queries, $filter_name, $value );

			foreach ( $filter_queries as $filter_query ) {
				$meta_query[] = $filter_query;
			}
		}

		$query->set( 'meta_query', $meta_query );
	}
}
