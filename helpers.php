<?php
/**
 * Member helper functions.
 *
 * @package Member_Frontend
 */

if ( ! function_exists( 'mf_select_options' ) ) {
	/**
	 * Fill <select> element with options with
	 * automatic selection.
	 *
	 * @param string $current_value The current select value.
	 * @param array  $options       The select options.
	 */
	function mf_select_options( $current_value, $options ) {
		if ( empty( $options ) ) {
			return;
		}

		foreach ( $options as $value => $label ) {
			$active = false;

			if ( is_array( $current_value ) ) {
				$active = in_array( (string) $value, $current_value );
			} elseif ( null !== $current_value ) {
				$active = (string) $current_value === (string) $value;
			}

			$selected = $active ? ' selected' : '';

			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $value ),
				esc_attr( $selected ),
				esc_html( $label )
			);
		}
	}
}

if ( ! function_exists( 'mf_current_member_has_role' ) ) {
	/**
	 * Check if the logged in member has a role.
	 *
	 * @param string ...$roles The roles to check.
	 *
	 * @return bool
	 */
	function mf_current_member_has_role( ...$roles ) {
		static $user = null;

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( ! $user ) {
			$user = MF()->get_current_user();
		}

		return mf_member_has_role( $user, ...$roles );
	}
}

if ( ! function_exists( 'mf_member_has_role' ) ) {
	/**
	 * Check if the logged in member has a role.
	 *
	 * @param \WP_User $user     The user to check.
	 * @param string   ...$roles The roles to check.
	 *
	 * @return bool
	 */
	function mf_member_has_role( WP_User $user, ...$roles ) {
		if ( empty( $roles ) ) {
			return false;
		}

		$has_role = false;

		foreach ( $roles as $role ) {
			$has_role = in_array( 'mf_' . $role, $user->roles, true );

			if ( $has_role ) {
				break;
			}
		}

		return $has_role;
	}
}

if ( ! function_exists( 'mf_get_current_member_role' ) ) {
	/**
	 * Return the logged in member's role.
	 *
	 * @return string
	 */
	function mf_get_current_member_role() {
		static $user = null;

		if ( ! $user ) {
			$user = wp_get_current_user();
		}

		return $user ? mf_get_member_role( $user ) : null;
	}
}

if ( ! function_exists( 'mf_get_member_role' ) ) {
	/**
	 * Return the logged in member's role.
	 *
	 * @param WP_User $user The member to get the role for.
	 *
	 * @return string
	 */
	function mf_get_member_role( WP_User $user ) {
		$member_roles = MF()->role_manager->get_roles();

		$role = array_filter(
			$user->roles,
			function ( $role ) use ( $member_roles ) {
				return isset( $member_roles[ $role ] );
			}
		);

		return array_shift( $role );
	}
}

if ( ! function_exists( 'mf_url_to_action' ) ) {
	/**
	 * Convert a URL path to an action.
	 *
	 * @param string $url The URL to parse.
	 *
	 * @return string
	 */
	function mf_url_to_action( $url ) {
		if ( ! $url ) {
			return null;
		}

		return str_replace( '-', '_', $url );
	}
}

if ( ! function_exists( 'mf_action_to_url' ) ) {
	/**
	 * Convert an action name to a URL path.
	 *
	 * @param string $action The action to parse.
	 *
	 * @return string
	 */
	function mf_action_to_url( $action ) {
		return str_replace( '_', '-', $action );
	}
}

if ( ! function_exists( 'mf_nonce' ) ) {
	/**
	 * Create a member nonce.
	 *
	 * @param string $action     The action name.
	 * @param bool   $force_priv Whether to force user checks.
	 */
	function mf_nonce( $action, $force_priv = false ) {
		if ( $force_priv || ! in_array( $action, apply_filters( 'mf_allowed_actions', array() ), true ) ) {
			$nonce_action = 'priv_' . get_current_user_id() . '_' . $action;
		} else {
			$nonce_action = 'nopriv_' . $action;
		}

		wp_nonce_field( "{$nonce_action}", 'mf_nonce' );
	}
}

if ( ! function_exists( 'mf_verify_nonce' ) ) {
	/**
	 * Verify the member nonce.
	 *
	 * @param string $nonce  The nonce.
	 * @param string $action The action name.
	 *
	 * @return bool
	 */
	function mf_verify_nonce( $nonce, $action ) {
		$valid = wp_verify_nonce( $nonce, 'nopriv_' . $action );

		if ( ! $valid && is_user_logged_in() ) {
			$valid = wp_verify_nonce( $nonce, 'priv_' . get_current_user_id() . '_' . $action );
		}

		return $valid;
	}
}
