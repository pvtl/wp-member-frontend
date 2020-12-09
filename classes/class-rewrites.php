<?php
/**
 * Member Frontend Rewrites.
 *
 * @package Member_Frontend
 */

namespace App\Plugins\Pvtl\Classes;

defined( 'ABSPATH' ) || die;

/**
 * Class Rewrites
 *
 * @package App\Plugins\Pvtl
 */
class Rewrites {
	/**
	 * Construct.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'rewrites' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
	}

	/**
	 * Rewrites instance.
	 *
	 * @return static
	 */
	public static function instance() {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * Add custom query vars to the request.
	 *
	 * @param array $query_vars The query vars.
	 *
	 * @return array
	 */
	public function add_query_vars( $query_vars ) {
		$query_vars[] = 'mf_action';
		$query_vars[] = 'mf_id';

		return $query_vars;
	}

	/**
	 * Add custom rewrite rules for member actions.
	 */
	public function rewrites() {
		$member_page = MF()->get_members_page();

		if ( ! $member_page ) {
			return;
		}

		$post_id   = $member_page->ID;
		$post_path = get_page_uri( $member_page );

		$available_actions = apply_filters( 'mf_rewrite_actions', array() );

		$rules = array();

		if ( ! empty( $available_actions ) ) {
			foreach ( $available_actions as $action ) {
				$action = mf_action_to_url( $action );

				// Create custom rewrite patterns for actions that contain
				// URL parameters. e.g. /components/:id/. Currently only one
				// URL parameter is handled, which is available in the mf_id query arg.
				if ( preg_match( '(:[\w-]+)', $action ) ) {
					// Escape forward slashes and replace ":id" with a pattern.
					$regex_action = preg_replace( array( '([/])', '(:[\w-]+)' ), array( '\/', '([\w-]+?)' ), $action );

					$rules[] = array(
						'regex' => "^{$post_path}\/{$regex_action}?$",
						'query' => 'index.php?page_id=' . $post_id . '&mf_action=' . $action . '&mf_id=$matches[1]',
					);
				}
			}
		}

		// Create a generic rewrite rule for actions that DO NOT
		// contain URL parameters. This will match the full URL
		// minus pagination (e.g. /page/2/) to the mf_action
		// query arg. It'll also add the correct pagination query arg.
		$rules[] = array(
			'regex' => "^{$post_path}\/(.+?(?=\/page)?)(?:\/page\/([0-9]+))?$",
			'query' => 'index.php?page_id=' . $post_id . '&mf_action=$matches[1]&paged=$matches[2]',
		);

		foreach ( $rules as $rule ) {
			add_rewrite_rule( $rule['regex'], $rule['query'], 'top' );
		}
	}
}
