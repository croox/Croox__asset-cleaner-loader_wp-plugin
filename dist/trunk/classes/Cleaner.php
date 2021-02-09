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
        add_action( 'wp_loaded', array( $this, 'hooks' ), 20 );
	}

    /**
     * Setup hooks.
     */
	public function hooks() {
        // Allow priorities to be filtered.
        $priorities = apply_filters( 'acll_cleaner_hook_priorities', array(
            'dequeue_scripts_header'            => 999,
            'dequeue_scripts_footer'            => 3,
            'remove_from_print_scripts_array'   => 10,
            'dequeue_styles'                    => 999,
            'remove_from_print_styles_array'    => 10,
            'woo_blocks_dependencies'           => 999,
        ) );

        // Dequeue scripts. Twice, in header and in footer.
		add_action( 'wp_print_scripts', array( $this, 'dequeue_scripts_header' ), Arr::get( $priorities, 'dequeue_scripts_header' ) );
        // Dequeue footer scripts.
        // Has do be done after woocommerceBlocks added the inline script data wcSettings. See Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::enqueue_asset_data in woo-gutenberg-products-block/src/Assets/AssetDataRegistry.php
        // And before Loader collected scripts in footer. See Loader::hooks
        add_action( 'wp_print_footer_scripts', array( $this, 'dequeue_scripts_footer' ), Arr::get( $priorities, 'dequeue_scripts_footer' ) );
        // If scripts somehow remain in the queue, remove them just before they are going to be printed.
        add_filter( 'print_scripts_array', array( $this, 'remove_from_print_scripts_array' ), Arr::get( $priorities, 'remove_from_print_scripts_array' ), 1 );

        // Dequeue styles
        add_action( 'wp_print_styles', array( $this, 'dequeue_styles' ), Arr::get( $priorities, 'dequeue_styles' ) );
        // If styles somehow remain in the queue, remove them just before they are going to be printed.
        add_filter( 'print_styles_array', array( $this, 'remove_from_print_styles_array' ), Arr::get( $priorities, 'remove_from_print_styles_array' ), 1 );

        // Remove some dependencies from all woo block scripts
		add_filter( 'woocommerce_blocks_register_script_dependencies', array( $this, 'woo_blocks_dependencies' ), Arr::get( $priorities, 'woo_blocks_dependencies' ), 1 );
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
    public function dequeue_scripts_header() {
        $this->dequeue_scripts();
    }
    public function dequeue_scripts_footer() {
        $this->dequeue_scripts();
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
     * Remove scripts from the array of enqueued styles before processing for output.
     * Note: this removes just the script itself, not its dependencies.
     */
    public function remove_from_print_scripts_array( $handles ) {
        $dequeue_handles = $this->get_dequeue_handles( 'script' );
        return array_filter( $handles, function( $handle ) use ( $dequeue_handles ) {
            return ! in_array( $handle, $dequeue_handles );
        } );
    }

    /**
     * Remove styles from the array of enqueued styles before processing for output.
     * Note: this removes just the style itself, not its dependencies.
     */
    public function remove_from_print_styles_array( $handles ) {
        $dequeue_handles = $this->get_dequeue_handles( 'style' );
        return array_filter( $handles, function( $handle ) use ( $dequeue_handles ) {
            return ! in_array( $handle, $dequeue_handles );
        } );
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