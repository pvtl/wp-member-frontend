<?php
/**
 * Action handlers.
 *
 * @package MemberFrontend
 */

namespace App\Plugins\Pvtl\Classes;

defined( 'ABSPATH' ) || die;

/**
 * Class Actions
 *
 * @package MemberFrontend
 */
class Actions {
	/**
	 * Actions constructor.
	 */
	public function __construct() {
		// Set the default fallback actions.
		add_filter( 'mf_fallback_action', array( $this, 'fallback_action' ) );

		// Dispatch action handlers.
		add_action( 'template_redirect', array( $this, 'action_handler' ) );
	}

	/**
	 * Return the default actions.
	 *
	 * @return array
	 */
	protected function default_actions() {
		return array(
			'login',
			'update',
			'register',
			'dashboard',
			'reset_password',
			'forgot_password',
		);
	}

	/**
	 * Check the current request method.
	 *
	 * @param string $method The method to check.
	 *
	 * @return bool
	 */
	protected function is( $method ) {
		return trim( strtolower( $method ) ) === $this->method();
	}

	/**
	 * Get the current request method.
	 *
	 * @return string
	 */
	protected function method() {
		return isset( $_SERVER['REQUEST_METHOD'] )
			? strtolower( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) )
			: null;
	}

	/**
	 * Return the fallback member action if empty.
	 *
	 * @param string $action The action if passed.
	 *
	 * @return string
	 */
	public function fallback_action( $action = '' ) {
		if ( ! $action ) {
			$action = is_user_logged_in() ? 'dashboard' : 'login';
		}

		$available_actions = apply_filters( 'mf_actions', $this->default_actions() );

		if ( ! in_array( $action, $available_actions, true ) ) {
			return null;
		}

		return $action;
	}

	/**
	 * Return the current action.
	 *
	 * @return string
	 */
	public function action() {
		$action = str_replace( '-', '_', get_query_var( 'mf_action' ) );
		$action = apply_filters( 'mf_fallback_action', $action );

		if ( ! $action ) {
			return null;
		}

		return $action;
	}

	/**
	 * Handle an action post.
	 */
	public function action_handler() {
		if ( ! $this->is( 'post' ) ) {
			return;
		}

		$action = $this->action();

		if ( ! $action ) {
			return;
		}

		$data = wp_unslash( $_POST );

		do_action( "mf_action_{$action}", $data );
	}
}
