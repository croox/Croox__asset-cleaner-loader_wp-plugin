<?php

namespace acll;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use croox\wde\Plugin;

class Acll extends Plugin {

	public function initialize() {

		// // Run Updates when plugin version changes.
		// add_filter( $this->prefix . '_update_version', function( $success, $new_version, $old_version ) {
		// 	return $success;
		// }, 10, 3 );

		// // Run Updates when plugin db_version changes.
		// add_filter( $this->prefix . '_update_db_version', function( $success, $new_db_version, $old_db_version ) {
		// 	return $success;
		// }, 10, 3 );

		parent::initialize();
	}

	public function hooks(){
		parent::hooks();

		if ( ! is_admin() ) {
			Cleaner::get_instance();
			Loader::get_instance();
		}
	}

}