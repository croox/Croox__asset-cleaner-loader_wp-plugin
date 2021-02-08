<?php

namespace acll;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use croox\wde\utils\Arr;

class Cleaner {

    protected static $instance = null;

    /**
     * Array of script handles.
     * Use $this->get_dequeue_handles( 'script' )
     */
    protected $dequeue_script_handles = null;

    /**
     * Array of style handles.
     * Use $this->get_dequeue_handles( 'style' )
     */
    protected $dequeue_style_handles = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __construct() {
        // Dequeue scripts. Twice, in header and in footer.
		add_action( 'wp_print_scripts', array( $this, 'dequeue_scripts' ), 999 );
        add_action( 'wp_print_footer_scripts', array( $this, 'dequeue_scripts' ), 3 );
        // Dequeue styles
        add_action( 'wp_print_styles', array( $this, 'dequeue_styles' ), 999 );
        // Remove some dependencies from all woo block scripts
		add_filter( 'woocommerce_blocks_register_script_dependencies', array( $this, 'woo_blocks_dependencies' ), 999, 1 );
	}

    /**
     * Get an array of script handles.
     *
     * @param   string   $type  Type of asset. 'scripts'|'styles'
     * @return  array
     */
    public function get_dequeue_handles( $type = 'script' ) {
        switch( $type ) {
            case 'script':
                if ( null === $this->dequeue_script_handles ) {
                    $this->dequeue_script_handles = apply_filters( 'acll_cleaner_scripts', array() );
                }
                return $this->dequeue_script_handles;
                break;
            case 'style':
                if ( null === $this->dequeue_style_handles ) {
                    $this->dequeue_style_handles = apply_filters( 'acll_cleaner_styles', array() );
                }
                return $this->dequeue_style_handles;
                break;
        }
    }

    /**
     * Dequeue certain scripts
     */
    public function dequeue_scripts() {
        foreach ( $this->get_dequeue_handles( 'script' ) as $handle ) {
            wp_dequeue_script( $handle );
        }
    }

    /**
     * Dequeue certain styles
     */
    public function dequeue_styles() {
        foreach ( $this->get_dequeue_handles( 'style' ) as $handle ) {
            wp_dequeue_style( $handle );
        }
    }

    /**
     * Remove some dependencies from all woo block scripts
     */
    public function woo_blocks_dependencies( $dependencies ) {
        if ( is_admin() )
            return;

        // Array of handles to be removed from all woo block script dependencies
        $remove = apply_filters( 'acll_cleaner_woo_block_scripts_dependencies', array() );

        foreach( $remove as $handle ) {
            $index = array_search( $handle, $dependencies );
            if ( $index ) {
                unset( $dependencies[$index] );
            }
        }
        return $dependencies;
   }

}