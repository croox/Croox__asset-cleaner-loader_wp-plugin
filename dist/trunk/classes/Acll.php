<?php

namespace acll;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use croox\wde\Plugin;

class Acll extends Plugin {

	public function hooks(){
		parent::hooks();

		if ( ! is_admin() ) {
			Cleaner::get_instance();
			Loader::get_instance();
		}
	}

}