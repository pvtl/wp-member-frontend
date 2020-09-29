<?php
/**
 * Roles.
 *
 * @package Member_Frontend
 */

namespace App\Plugins\Pvtl\Classes;

defined( 'ABSPATH' ) || die;

/**
 * Class Roles
 *
 * @package App\Plugins\Pvtl
 */
class Role_Manager {
	/**
	 * Roles constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup_roles' ), 30, 0 );
	}

	/**
	 * Set the default role.
	 *
	 * @param array $roles The existing roles.
	 *
	 * @return array
	 */
	protected function default_roles( $roles = array() ) {
		$roles['mf_member'] = array(
			'name' => 'Member',
		);

		return $roles;
	}

	/**
	 * Return a list of loaded member roles.
	 *
	 * @return array
	 */
	protected function get_loaded_roles() {
		return array_filter(
			wp_roles()->roles,
			function ( $role ) {
				return strpos( $role, 'mf_' ) === 0;
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Add new WP_Roles.
	 *
	 * @param array $roles_to_add The roles to add.
	 */
	protected function add_roles( $roles_to_add ) {
		if ( empty( $roles_to_add ) ) {
			return;
		}

		foreach ( $roles_to_add as $role_name => $role_options ) {
			if ( ! isset( $role_options['capabilities'] ) ) {
				$role_options['capabilities'] = array();
			}

			// Remove the role if it exists.
			remove_role( $role_name );

			// Add a possibly new version of the role.
			add_role( $role_name, $role_options['name'], $role_options['capabilities'] );
		}
	}

	/**
	 * Remove loaded WP_Roles.
	 *
	 * @param array $roles_to_remove The roles to remove.
	 */
	protected function remove_roles( $roles_to_remove ) {
		if ( empty( $roles_to_remove ) ) {
			return;
		}

		foreach ( $roles_to_remove as $role_name ) {
			remove_role( $role_name );
		}
	}

	/**
	 * Return a list of filtered member roles.
	 *
	 * @return array
	 */
	public function get_roles() {
		return apply_filters( 'mf_roles', $this->default_roles() );
	}

	/**
	 * Create the custom member roles.
	 */
	public function setup_roles() {
		$loaded_roles    = $this->get_loaded_roles();
		$roles_to_add    = $this->get_roles();
		$roles_to_remove = array();

		if ( ! empty( $loaded_roles ) ) {
			foreach ( $loaded_roles as $role_key => $role_options ) {
				if ( ! isset( $roles_to_add[ $role_key ] ) ) {
					$roles_to_remove[] = $role_key;

					continue;
				}

				$old_serialized = serialize( $role_options );
				$new_serialized = serialize( $roles_to_add[ $role_key ] );

				if ( $old_serialized === $new_serialized ) {
					unset( $roles_to_add[ $role_key ] );
				}
			}
		}

		$this->add_roles( $roles_to_add );
		$this->remove_roles( $roles_to_remove );
	}
}
