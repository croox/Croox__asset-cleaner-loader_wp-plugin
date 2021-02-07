<?php

namespace acll;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use croox\wde;

class Acll extends wde\Plugin {

	public function hooks(){
		parent::hooks();

		if ( ! is_admin() && ! acll_is_rest() ) {
			Cleaner::get_instance();
			Loader::get_instance();
		}
	}

}