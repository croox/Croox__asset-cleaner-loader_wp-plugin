<?php
/*
	Plugin Name: Croox Asset Cleaner Loader
	Plugin URI: https://github.com/croox/Croox__asset-cleaner-loader_wp-plugin
	Description: Stop some assets from being enqueued. Use JS to load them when needed
	Version: 0.4.2
	Author: croox
	Author URI: https://github.com/croox
	License: GNU General Public License v2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
	Text Domain: acll
	Domain Path: /languages
	Tags: load,script,async,clean,assets
	GitHub Plugin URI: https://github.com/croox/Croox__asset-cleaner-loader_wp-plugin
	Release Asset: true
*/
?><?php
/**
 * Croox Asset Cleaner Loader Plugin init
 *
 * @package Croox__asset-cleaner-loader_wp-plugin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

include_once( dirname( __FILE__ ) . '/vendor/autoload.php' );

function acll_init() {

	$init_args = array(
		'version'		=> '0.4.2',
		'slug'			=> 'Croox__asset-cleaner-loader_wp-plugin',
		'name'			=> 'Croox Asset Cleaner Loader',
		'prefix'		=> 'acll',
		'textdomain'	=> 'acll',
		'project_kind'	=> 'plugin',
		'FILE_CONST'	=> __FILE__,
		'db_version'	=> 0,
		'wde'			=> array(
			'generator-wp-dev-env'	=> '1.3.1',
			'wp-dev-env-grunt'		=> '1.3.1',
			'wp-dev-env-frame'		=> '0.14.1',
		),
		'deps'			=> array(
			'php_version'	=> '5.6.0',		// required php version
			'wp_version'	=> '5.0.0',			// required wp version
			'plugins'    	=> array(
				/*
				'woocommerce' => array(
					'name'              => 'WooCommerce',               // full name
					'link'              => 'https://woocommerce.com/',  // link
					'ver_at_least'      => '3.0.0',                     // min version of required plugin
					'ver_tested_up_to'  => '3.2.1',                     // tested with required plugin up to
					'class'             => 'WooCommerce',               // test by class
					//'function'        => 'WooCommerce',               // test by function
				),
				*/
			),
			'php_ext'     => array(
				/*
				'xml' => array(
					'name'              => 'Xml',                                           // full name
					'link'              => 'http://php.net/manual/en/xml.installation.php', // link
				),
				*/
			),
		),
	);

	// see ./classes/Acll.php
	return acll\Acll::get_instance( $init_args );
}
acll_init();

?>