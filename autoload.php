<?php
/**
 * Autoload package classes.
 *
 * @package MemberFrontend
 */

spl_autoload_register(
	static function ( $class ) {
		$file = str_replace(
			array( 'App\\Plugins\\Pvtl\\', '\\', '_', 'Classes' . DIRECTORY_SEPARATOR ),
			array( '', DIRECTORY_SEPARATOR, '-', 'classes' . DIRECTORY_SEPARATOR . 'class-' ),
			$class
		);
		$file = MF_PATH . '/' . strtolower( $file ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}

		return false;
	}
);
