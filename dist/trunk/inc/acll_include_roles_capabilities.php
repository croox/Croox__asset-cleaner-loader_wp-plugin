<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function acll_include_roles_capabilities() {

	$paths = array(
	);

	if ( count( $paths ) > 0 ) {
		foreach( $paths as $path ) {
			include_once( acll\Acll::get_instance()->dir_path . $path );
		}
	}

}

?>