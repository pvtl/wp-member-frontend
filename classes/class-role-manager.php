<?php
/**
 * Roles.
 *
 * @package MemberFrontend
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
	 * The list of custom member roles.
	 *
	 * @var array
	 */
	public $roles = array();

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
	 * Create the custom member roles.
	 */
	public function setup_roles() {
		$this->roles = apply_filters( 'mf_roles', $this->default_roles() );

		if ( empty( $this->roles ) ) {
			return;
		}

		foreach ( $this->roles as $role_name => $role_options ) {
			if ( ! isset( $role_options['capabilities'] ) ) {
				$role_options['capabilities'] = array();
			}

			add_role( $role_name, $role_options['name'], $role_options['capabilities'] );
		}
	}
}
