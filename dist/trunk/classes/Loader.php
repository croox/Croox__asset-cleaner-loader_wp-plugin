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
        // Register script and enqueue it in footer.
        add_action( 'wp_enqueue_scripts', array( $this, 'register_script' ), 10 );
        // Collect scripts to use as kind of localize data. Collect twice, in header and in footer.
        add_action( 'wp_print_scripts', array( $this, 'collect_scripts_for_loc_data_header' ), 1 );
        add_action( 'wp_print_footer_scripts', array( $this, 'collect_scripts_for_loc_data_footer' ), 1 );
        // Add collected scripts as kind of localized data. Inline script actually.
        add_action( 'wp_print_footer_scripts', array( $this, 'add_loc_data' ), 2 );
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
     * Collect scripts in header to use as kind of localize data.
     */
    public function collect_scripts_for_loc_data_header() {
        $this->scripts = array_merge(
            $this->scripts,
            $this->get_scripts_data(
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
                apply_filters( 'acll_loader_script_handles_footer', array() )
            )
        );
    }

    /**
     * Add collected scripts as kind of localized data. Inline script actually.
     */
    public function add_loc_data() {
        if ( ! empty( $this->scripts ) ) {
            wp_add_inline_script(
                $this->handle,
                'var ' . $this->handle . ' = ' . wp_json_encode( array(
                    'scripts' => $this->scripts,
                ) ) . ';',
                'before'
            );
        }
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
            $scripts = self::scripts_fill_deps( $scripts, $this->get_all_queued_scripts_and_deps_handles() );
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
     * @param   array       $scripts          Array of _WP_Dependency objects.
     * @param   array       $exclude_handles  Array of handles to be excluded.
     * @param   null|array  $deps_loaded      Should be null initially.
     *                                        Used internally to store which dependencies are loaded already.
     * @return  array                         Array of _WP_Dependency objects with all dependencies.
     */
    protected static function scripts_fill_deps( $scripts = array(), $exclude_handles = array(), $deps_loaded = null ) {
        global $wp_scripts;

        if ( null === $deps_loaded ) {
            $deps_loaded = array();
            foreach( $scripts as $script ) {
                if ( array_key_exists( $script->handle, $wp_scripts->registered ) ) { // Avoid unregistered scripts.
                    $deps_loaded[$script->handle] = false;
                }
            }
        }

        if ( false === array_search( false, $deps_loaded ) ) {
            return $scripts;
        }

        foreach( $scripts as $script ) {
            if ( ! $deps_loaded[$script->handle] ) {
                foreach( $script->deps as $dep_handle ) {
                    if ( ! array_key_exists( $dep_handle, $scripts )
                        && ! in_array( $dep_handle, $exclude_handles )
                        && array_key_exists( $dep_handle, $wp_scripts->registered ) // Avoid unregistered scripts.
                    ) {
                        $scripts[$dep_handle] = $wp_scripts->registered[$dep_handle];
                        $deps_loaded[$dep_handle] = false;
                    }
                }
                $deps_loaded[$script->handle] = true;
            }
        }

        return self::scripts_fill_deps( $scripts, $exclude_handles, $deps_loaded );
    }

    /**
     * Get a list of all script handles in queue,
     * and all their dependencies as well,
     * but exclude the ones that are going to be dequeued later.
     * @return  array
     */
    protected function get_all_queued_scripts_and_deps_handles() {
        global $wp_scripts;
        // Get the list of scripts that will be dequeued.
        $dequeue_handles = Cleaner::get_instance()->get_dequeue_handles( 'scripts' );
        // Collect all scripts in queue.
        // But not the ones that are going to be dequeued.
        $queue_scripts = array();
        foreach( $wp_scripts->queue as $handle ) {
            if ( ! in_array( $handle, $dequeue_handles )                // Not the ones that are going to be dequeued.
                && array_key_exists( $handle, $wp_scripts->registered ) // Avoid unregistered scripts.
            ) {
                $queue_scripts[$handle] = $wp_scripts->registered[$handle];
            }
        }
        // Fill with dependencies.
        $queue_scripts = self::scripts_fill_deps( $queue_scripts );
        // We only need the handles.
        $queue_scripts_handles = array_keys( $queue_scripts );
        // Add own dependencies, if missing in queue.
        foreach( $this->deps as $dep_handle ) {
            if ( ! in_array( $dep_handle, $queue_scripts_handles ) ) {
                $queue_scripts_handles[] = $dep_handle;
            }
        }
        return $queue_scripts_handles;
    }

}