<?php
/**
 * Admin filters.
 *
 * @package MemberFrontend
 *
 * phpcs:disable WordPress.Security.NonceVerification
 */

namespace App\Plugins\Pvtl\Classes;

defined( 'ABSPATH' ) || die;

/**
 * Class Admin
 *
 * @package MemberFrontend
 */
class Admin {
	/**
	 * Admin constructor.
	 */
	public function __construct() {
		// Add the filter form fields.
		add_action( 'restrict_manage_users', array( $this, 'add_member_filters' ), 20 );

		// Apply the member filters.
		add_filter( 'pre_get_users', array( $this, 'filter_members' ) );
	}

	/**
	 * Add the member filter fields to the users filter.
	 *
	 * @param string $which The location of the extra table nav markup.
	 */
	public function add_member_filters( $which ) {
		$member_filters = apply_filters( 'mf_member_filters', array() );

		if ( empty( $member_filters ) ) {
			return;
		}

		$text_template   = '<input type="text" name="mf_filter_%s" placeholder="%s" value="%s">';
		$select_template = '<select name="mf_filter_%s"><option value="">%s</option>%s</select>';
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

		submit_button( 'Filter', '', "mf_filter_{$which}", false );
	}

	/**
	 * @param $query
	 */
	function filter_members( $query ) {
		global $pagenow;
		if ( is_admin() && 'users.php' == $pagenow ) {
			$button = key( array_filter( $_GET, function ( $v ) {
				return __( 'Filter' ) === $v;
			} ) );
			if ( $section = $_GET[ 'course_section_' . $button ] ) {
				$meta_query = [ [ 'key' => 'courses', 'value' => $section, 'compare' => 'LIKE' ] ];
				$query->set( 'meta_key', 'courses' );
				$query->set( 'meta_query', $meta_query );
			}
		}
	}
}
