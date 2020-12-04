<?php
/**
 * Member Frontend Router.
 *
 * @package Member_Frontend
 */

namespace App\Plugins\Pvtl\Classes;

defined( 'ABSPATH' ) || die;

/**
 * Class Router
 *
 * @package App\Plugins\Pvtl
 */
class Router {
	/**
	 * Construct.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'rewrites' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
	}

	/**
	 * Main WooCommerce Instance.
	 *
	 * Ensures only one instance of WooCommerce is loaded or can be loaded.
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
	 * Add custom query vars to request.
	 *
	 * @param array $query_vars The existing query vars.
	 *
	 * @return array
	 */
	public function add_query_vars( $query_vars ) {
		$query_vars[] = 'mf_action';
		$query_vars[] = 'mf_id';

		return $query_vars;
	}

	/**
	 * Add custom rewrite rules.
	 */
	public function rewrites() {
		$post_id   = MF()->member_page->ID;
		$post_path = get_page_uri( MF()->member_page );

		$available_actions = apply_filters( 'mf_actions', array() );

		if ( empty( $available_actions ) ) {
			return;
		}

		$rules = array();

		foreach ( $available_actions as $action ) {
			if ( preg_match( '(:[\w]+)', $action ) ) {
				$regex_action = preg_replace( array( '([/])', '(:[\w]+)' ), array( '\/', '([\w]+?)' ), $action );

				$rules[] = array(
					'regex'  => "^{$post_path}\/{$regex_action}?$",
					'query'  => 'index.php?page_id=' . $post_id . '&mf_action=' . $action . '&mf_id=$matches[1]',
				);
			}
		}

		$rules[] = array(
			'regex' => "^{$post_path}\/([/\w]+?(?=\/page)?)(?:\/page\/([0-9]+))?$",
			'query' => 'index.php?page_id=' . $post_id . '&mf_action=$matches[1]&paged=$matches[2]',
		);

		foreach ( $rules as $rule ) {
			add_rewrite_rule( $rule['regex'], $rule['query'], 'top' );
		}
	}
}
