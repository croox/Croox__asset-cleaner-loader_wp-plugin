<?php

namespace acll;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use croox\wde\utils\Arr;

class Loader {

    protected static $instance = null;

    /**
     * Array of scripts to be used as kind of localize data
     */
    protected $scripts = array();

    /**
     * Array of styles to be used as kind of localize data
     */
    protected $styles = array();

    /**
     * Script handle
     */
    protected $handle = 'acll_loader';

    /**
     * Script dependencies
     */
    protected $deps = array(
        'wp-i18n',
    );

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
        /**
         * Allow hook priorities to be filtered.
         *
         * @param array     $priorities     Associative array of hook priorities.
         */
        $priorities = apply_filters( 'acll_loader_hook_priorities', array(
            'register_script'                       => 10,
            'collect_styles_for_loc_data'           => 2,
            'collect_scripts_for_loc_data_header'   => 2,
            'collect_scripts_for_loc_data_footer'   => 2,
            'add_loc_data'                          => 5,
        ) );

        // Register script and enqueue it in footer.
        add_action( 'wp_enqueue_scripts', array( $this, 'register_script' ), Arr::get( $priorities, 'register_script' ) );

        // Collect styles to use as kind of localize data.
        add_action( 'wp_print_styles', array( $this, 'collect_styles_for_loc_data' ), Arr::get( $priorities, 'collect_styles_for_loc_data' ) );

        // Collect scripts to use as kind of localize data.
        // Do it twice, in header and in footer.
        add_action( 'wp_print_scripts', array( $this, 'collect_scripts_for_loc_data_header' ), Arr::get( $priorities, 'collect_scripts_for_loc_data_header' ) );
        // Has do be done before Cleaner will dequeue the scripts. See Cleaner::hooks
        // And after woocommerceBlocks added the inline script data wcSettings. See Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::enqueue_asset_data in woo-gutenberg-products-block/src/Assets/AssetDataRegistry.php
        add_action( 'wp_print_footer_scripts', array( $this, 'collect_scripts_for_loc_data_footer' ), Arr::get( $priorities, 'collect_scripts_for_loc_data_footer' ) );

        // Add collected scripts as kind of localized data. Inline script actually.
        add_action( 'wp_print_footer_scripts', array( $this, 'add_loc_data' ), Arr::get( $priorities, 'add_loc_data' ) );
    }

    /**
     * Register script in footer.
     */
	public function register_script() {
        Acll::get_instance()->register_script( array(
            'handle'		=> $this->handle,
            'deps'			=> $this->deps,
            'in_footer'		=> true,
            'enqueue'		=> false,
            // 'localize_data'	=> array(), // inline script instead, see add_loc_data
        ) );
    }

    /**
     * Collect styles to use as kind of localize data.
     */
    public function collect_styles_for_loc_data() {
        $this->styles = array_merge(
            $this->styles,
            $this->get_styles_data(
                /**
                 * Modifies the array of style handles that will available for frontend js.
                 *
                 * @param array     $style_handles     Style handles for frontend js.
                 */
                apply_filters( 'acll_loader_style_handles', array() )
            )
        );
    }
    /**
     * Collect scripts in header to use as kind of localize data.
     */
    public function collect_scripts_for_loc_data_header() {
        $this->scripts = array_merge(
            $this->scripts,
            $this->get_scripts_data(
                /**
                 * Modifies the array of script handles, collected in header, that will available for frontend js..
                 *
                 * @param array     $script_handles     Header script handles for frontend js.
                 */
                apply_filters( 'acll_loader_script_handles_header', array() )
            )
        );
    }

    /**
     * Collect scripts in footer to use as kind of localize data.
     */
    public function collect_scripts_for_loc_data_footer() {
        $this->scripts = array_merge(
            $this->scripts,
            $this->get_scripts_data(
                /**
                 * Modifies the array of script handles, collected in footer, that will available for frontend js..
                 *
                 * @param array     $script_handles     Footer script handles for frontend js.
                 */
                apply_filters( 'acll_loader_script_handles_footer', array() )
            )
        );
    }

    /**
     * Add collected scripts as kind of localized data. Inline script actually.
     */
    public function add_loc_data() {
        wp_add_inline_script(
            $this->handle,
            'var ' . $this->handle . ' = ' . wp_json_encode( array(
                'scripts' => $this->scripts,
                'styles' => $this->styles,
            ) ) . ';',
            'before'
        );
    }

    /**
     * Get an array with info for each style,
     * and all their dependencies as well,
     * but exclude dependencies that will be enqueued anyway.
     *
     * ??? TODO missing rtl support !
     *
     * @param   array  $handles  Array of style handles.
     * @return  array            Array of style-data-arrays.
     */
    protected function get_styles_data( $handles ) {
        global $wp_styles;
        // Collect all styles for given handles.
        $styles = array();
        foreach( $handles as $handle ) {
            if ( array_key_exists( $handle, $wp_styles->registered ) ) { // Avoid unregistered styles.
                $styles[$handle] = $wp_styles->registered[$handle];
            }
        }
        if ( ! empty( $styles ) ) {
            // $type_attr Copy of WP_Styles::type_attr. See WordPress/wp-includes/class.wp-styles.php
            $type_attr = '';
            if ( function_exists( 'is_admin' ) && ! is_admin()
                && function_exists( 'current_theme_supports' ) && ! current_theme_supports( 'html5', 'style' )
            ) {
                $type_attr = " type='text/css'";
            }
            // Fill the array of styles with their dependencies,
            // but exclude dependencies that will be enqueued anyway.
            // $styles = self::fill_deps( 'style', $styles, $this->get_all_queued_assets_and_deps_handles( 'style' ) );
            // Change each style into the structure we need.
            foreach( $styles as $style ) {
                $media = isset( $style->args ) ? esc_attr( $style->args ) : 'all';
                $src = $wp_styles->_css_href( $style->src, $style->ver, $style->handle );
                $rel = isset( $style->extra['alt'] ) && $style->extra['alt'] ? 'alternate stylesheet' : 'stylesheet';
                $title = isset( $style->extra['title'] ) ? sprintf( "title='%s'", esc_attr( $style->extra['title'] ) ) : '';
                $tag = sprintf(
                    "<link rel='%s' id='%s-css' %s href='%s'%s media='%s' />\n",
                    $rel,
                    $style->handle,
                    $title,
                    $src,
                    $type_attr,
                    $media
                );
                $tag = apply_filters( 'style_loader_tag', $tag, $handle, $src, $media );
                // ??? TODO missing rtl support !
                // Get additional tag attributes.
                // Build pseudo tag, apply style_loader_tag filter, match the attributes and build the attrs array.
                preg_match_all(
                    "/\s(\S+?)='([^']*?)'/",
                    $tag,
                    $attrs_matches,
                    PREG_SET_ORDER
                );
                $attrs = array();
                foreach( $attrs_matches as $match ) {
                    if ( ! in_array( $match[1], array(
                        'href',
                    ) ) ) {
                        $attrs[$match[1]] = $match[2];
                    }
                }
                // The new structure
                $styles[$style->handle] = array(
                    'handle'        => $style->handle,
                    'deps'          => $style->deps,
                    'src'           => $style->src,
                    'after'         => Arr::get( $style->extra, 'after', false ),
                    'attrs'         => $attrs,
                );
            }
        }
        return $styles;
    }

    /**
     * Get an array with info for each script,
     * and all their dependencies as well,
     * but exclude dependencies that will be enqueued anyway.
     *
     * @param   array  $handles  Array of script handles.
     * @return  array            Array of script-data-arrays.
     */
    protected function get_scripts_data( $handles ) {
        global $wp_scripts;

        // Collect all scripts for given handles.
        $scripts = array();
        foreach( $handles as $handle ) {
            if ( array_key_exists( $handle, $wp_scripts->registered ) ) { // Avoid unregistered scripts.
                $scripts[$handle] = $wp_scripts->registered[$handle];
            }
        }
        if ( ! empty( $scripts ) ) {
            // Fill the array of scripts with their dependencies,
            // but exclude dependencies that will be enqueued anyway.
            $scripts = self::fill_deps( 'script', $scripts, $this->get_all_queued_assets_and_deps_handles( 'script' ) );
            // Change each script into the structure we need.
            foreach( $scripts as $script ) {
                // Get translations json
                $translations = array();
                if ( $script->textdomain && $script->translations_path ) {
                    // Load script translations inside an output-buffer,
                    // because some plugins echo additional translations, eg: woocommerce-gutenberg-products-block plugin.
                    // See woocommerce-gutenberg-products-block/woocommerce-gutenberg-products-block.php function woocommerce_blocks_get_i18n_data_json
                    ob_start();
                    $translations[] = load_script_translations(
                        "{$script->translations_path}/{$script->textdomain}-" . get_locale() . "-{$script->handle}.json",
                        $script->handle,
                        $script->textdomain
                    );
                    $translation_ob = ob_get_clean();
                    \preg_match_all(
                        "/<script\stype='text\/javascript'>[\s\S]*?\(\s\"" . $script->textdomain . "\",\s({.*?})(?=\s\);)/",
                        $translation_ob,
                        $translation_ob_matches,
                        PREG_SET_ORDER
                    );
                    foreach( $translation_ob_matches as $translation_ob_match ) {
                        if ( 2 === count( $translation_ob_match ) ) {
                            $translations[] = $translation_ob_match[1];
                        }
                    }
                    $translations = array_filter( $translations, function( $translation ) {
                        return !! $translation;
                    } );
                }
                // Get additional tag attributes.
                // Build pseudo tag, apply script_loader_tag filter, match the attributes and build the attrs array.
                preg_match_all(
                    '/\s(\S+?)="([^"]*?)"/',
                    apply_filters(
                        'script_loader_tag',
                        sprintf( '<script src="%s" ></script>', $script->src ),
                        $script->handle,
                        $script->src
                    ),
                    $attrs_matches,
                    PREG_SET_ORDER
                );
                $attrs = array();
                foreach( $attrs_matches as $match ) {
                    if ( 'src' !== $match[1] ) {
                        $attrs[$match[1]] = $match[2];
                    }
                }
                // The new structure
                $scripts[$script->handle] = array(
                    'handle'        => $script->handle,
                    'deps'          => $script->deps,
                    'src'           => $script->src,
                    'textdomain'    => $script->textdomain,
                    'loc_data'      => Arr::get( $script->extra, 'data', false ),
                    'before'        => Arr::get( $script->extra, 'before', false ),
                    'after'         => Arr::get( $script->extra, 'after', false ),
                    'translations'  => $translations,
                    'attrs'         => $attrs,
                );
            }
        }
        return $scripts;
	}

    /**
     * Takes an array of _WP_Dependency objects and fills the array with all their dependencies
     *
     * @param   array       $assets           Array of _WP_Dependency objects.
     * @param   array       $exclude_handles  Array of handles to be excluded.
     * @param   null|array  $deps_loaded      Should be null initially.
     *                                        Used internally to store which dependencies are loaded already.
     * @return  array                         Array of _WP_Dependency objects with all dependencies.
     */
    protected static function fill_deps( $type = 'script', $assets = array(), $exclude_handles = array(), $deps_loaded = null ) {
        global $wp_scripts;
        global $wp_styles;
        $wp_assets = 'script' === $type ? $wp_scripts : $wp_styles;

        if ( null === $deps_loaded ) {
            $deps_loaded = array();
            foreach( $assets as $asset ) {
                if ( array_key_exists( $asset->handle, $wp_assets->registered ) ) { // Avoid unregistered assets.
                    $deps_loaded[$asset->handle] = false;
                }
            }
        }

        if ( false === array_search( false, $deps_loaded ) ) {
            return $assets;
        }

        foreach( $assets as $asset ) {
            if ( ! $deps_loaded[$asset->handle] ) {
                foreach( $asset->deps as $dep_handle ) {
                    if ( ! array_key_exists( $dep_handle, $assets )
                        && ! in_array( $dep_handle, $exclude_handles )
                        && array_key_exists( $dep_handle, $wp_assets->registered ) // Avoid unregistered assets.
                    ) {
                        $assets[$dep_handle] = $wp_assets->registered[$dep_handle];
                        $deps_loaded[$dep_handle] = false;
                    }
                }
                $deps_loaded[$asset->handle] = true;
            }
        }

        return self::fill_deps( $type, $assets, $exclude_handles, $deps_loaded );
    }

    /**
     * Get a list of all asset handles in queue,
     * and all their dependencies as well,
     * but exclude the ones that are going to be dequeued later.
     * @return  array
     */
    protected function get_all_queued_assets_and_deps_handles( $type = 'script' ) {
        global $wp_scripts;
        global $wp_styles;
        $wp_assets = 'script' === $type ? $wp_scripts : $wp_styles;
        // Get the list of assets that will be dequeued.
        $dequeue_handles = Cleaner::get_instance()->get_dequeue_handles( $type );
        // Collect all assets in queue.
        // But not the ones that are going to be dequeued.
        $queue_assets = array();
        foreach( $wp_assets->queue as $handle ) {
            if ( ! in_array( $handle, $dequeue_handles )                // Not the ones that are going to be dequeued.
                && array_key_exists( $handle, $wp_assets->registered )  // Avoid unregistered assets.
            ) {
                $queue_assets[$handle] = $wp_assets->registered[$handle];
            }
        }
        // Fill with dependencies.
        $queue_assets = self::fill_deps( $type, $queue_assets );
        // We only need the handles.
        $queue_assets_handles = array_keys( $queue_assets );
        // Add own dependencies, if missing in queue.
        foreach( $this->deps as $dep_handle ) {
            if ( ! in_array( $dep_handle, $queue_assets_handles ) ) {
                $queue_assets_handles[] = $dep_handle;
            }
        }
        return $queue_assets_handles;
    }

}