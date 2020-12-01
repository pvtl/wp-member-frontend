<?php
/**
 * Action handlers.
 *
 * @package Member_Frontend
 */

namespace App\Plugins\Pvtl\Classes;

defined( 'ABSPATH' ) || die;

/**
 * Class Actions
 *
 * @package Member_Frontend
 */
class Actions {
	/**
	 * Actions constructor.
	 */
	public function __construct() {
		// Set the default allowed actions.
		add_filter( 'mf_allowed_actions', array( $this, 'default_allowed_actions' ) );

		// Set the default fallback actions.
		add_filter( 'mf_fallback_action', array( $this, 'fallback_action' ) );

		// Dispatch action handlers.
		add_action( 'template_redirect', array( $this, 'action_handler' ) );
	}

	/**
	 * Return the default allowed actions.
	 *
	 * @return array
	 */
	public function default_allowed_actions() {
		return array(
			'login',
			'register',
			'reset_password',
			'forgot_password',
		);
	}

	/**
	 * Return the default actions.
	 *
	 * @return array
	 */
	protected function default_actions() {
		return array(
			'login',
			'profile',
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
	public function is( $method ) {
		return trim( strtolower( $method ) ) === $this->method();
	}

	/**
	 * Get the current request method.
	 *
	 * @return string
	 */
	public function method() {
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
	 * @return ?string
	 */
	public function action() {
		if ( ! MF()->is_member_page() ) {
			return null;
		}

		$action = mf_url_to_action( get_query_var( 'mf_action' ) );
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

		if (
			empty( $_REQUEST )
			&& isset( $_SERVER['CONTENT_TYPE'] )
			&& 'application/json' === $_SERVER['CONTENT_TYPE']
		) {
			$_REQUEST = json_decode( file_get_contents( 'php://input' ), true );
		}

		$data  = wp_unslash( $_REQUEST );
		$nonce = isset( $data['mf_nonce'] ) ? $data['mf_nonce'] : null;

		if ( ! mf_verify_nonce( $nonce, $action ) ) {
			wp_die( 'Nonce verification failed' );
		}

		do_action( "mf_action_{$action}", $data );
	}
}
