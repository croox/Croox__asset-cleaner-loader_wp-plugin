<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Checks if the current request is a WP REST API request.
 *
 * Case #1: After WP_REST_Request initialisation
 * Case #2: Support "plain" permalink settings
 * Case #3: URL Path begins with wp-json/ (your REST prefix)
 *          Also supports WP installations in subfolders
 *
 * @author matzeeable
 * @see https://wordpress.stackexchange.com/questions/221202/does-something-like-is-rest-exist#answer-317041
 * @return boolean
 */
function acll_is_rest() {

	$prefix = rest_get_url_prefix( );
	if (defined('REST_REQUEST') && REST_REQUEST // (#1)
		|| isset($_GET['rest_route']) // (#2)
			&& strpos( trim( $_GET['rest_route'], '\\/' ), $prefix , 0 ) === 0)
		return true;

	// (#3)
	$rest_url = wp_parse_url( site_url( $prefix ) );
	$current_url = wp_parse_url( add_query_arg( array( ) ) );

	// Check for parse error
	if ( false === $rest_url || false === $current_url )
		return false;

	if ( ! array_key_exists( 'path', $rest_url )
		|| ! array_key_exists( 'path', $current_url )
	) {
		return false;
	}

	return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
}
