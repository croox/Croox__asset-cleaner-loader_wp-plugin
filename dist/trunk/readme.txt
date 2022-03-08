=== Croox Asset Cleaner Loader ===
Tags: load,script,async,clean,assets
Donate link: https://github.com/croox/donate
Contributors: croox
Tested up to: 5.6.1
Requires at least: 5.0.0
Requires PHP: 5.6.0
Stable tag: trunk
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Stop some assets from being enqueued. Use JS to load them when needed


== Description ==

Removes certain scripts, styles and their dependencies from the list of enqueued assets.

Allows to load scripts, styles and their dependencies to be loaded by frontend JS.

= Usage =

The Plugin needs to be configured using filter and action hooks.

For a detailed list of filters and actions see https://github.com/croox/Croox__asset-cleaner-loader_wp-plugin/blob/master/dist/trunk/docs_hooks.md

= Examples =

1. Stop script `foo` and style `bar` from being enqueued:

```php
function prefix_stop_script_foo( $handles ) {
    return array_merge( $handles, array( 'foo' ) ) );
}
// Remove script `foo` from the list of scripts enqueued.
add_action( "acll_cleaner_scripts", "prefix_stop_script_foo", 10, 1 );

function prefix_stop_style_bar( $handles ) {
    return array_merge( $handles, array( 'bar' ) ) );
}
// Remove style `bar` from the list of styles enqueued.
add_action( "acll_cleaner_styles", "prefix_stop_style_bar", 10, 1 );
```

2. Load script `foo` and style `bar` via frontend JS:


Send info about the assets to the frontend and enqueue a script to trigger loading the assets:
```php
/**
 * Make `foo` script available for `acll_loader`
 * (`foo` had to be registered before, and needed to be stopped from being enqueued).
 */
function prefix_send_script_foo_to_frontend( $handles ) {
    return array_merge( $handles, array( 'foo' ) ) );
}
// Send info about script `foo` to the frontend. If `foo` is meant to be enqueued in header.
add_action( "acll_loader_script_handles_header", "prefix_send_script_foo_to_frontend", 10, 1 );
// Send info about script `foo` to the frontend. If `foo` is meant to be enqueued in footer.
add_action( "acll_loader_script_handles_footer", "prefix_send_script_foo_to_frontend", 10, 1 );

/**
 * Make `bar` style available for `acll_loader`
 * (`bar` had to be registered before, and needed to be stopped from being enqueued).
 */
function prefix_send_style_bar_to_frontend( $handles ) {
    return array_merge( $handles, array( 'bar' ) ) );
}
// Send info about style `bar` to the frontend.
add_action( "acll_loader_style_handles", "prefix_send_style_bar_to_frontend", 10, 1 );

/**
 * Enqueue script with `acll_loader` as dependency, to load frontend assets.
 */
function prefix_enqueue_loader_script() {
    wp_enqueue_script(
        'prefix_loader',                    // $handle
        'prefix_loader.js',                 // $src
        array( 'jquery', 'acll_loader' ),   // $deps, with `acll_loader` as dependency
        false,                              // $ver
        true,                               // $in_footer
    );
}
add_action( 'wp_enqueue_scripts', 'prefix_enqueue_loader_script' );
```

The frontend script `prefix_loader.js`:
```js
jQuery( document ).ready( function() {
    // Load script `foo` and style `bar` when element `.load-assets` is clicked.
    jQuery( '.load-assets' ).click( function() {
        acll_loader.loadAssets( {
            scripts: ['foo'],
            styles: ['bar'],
        } ).then( result => console.log( 'loaded:', result ) );
    } );
} );
```





== Installation ==
Upload and install this Theme the same way you'd install any other Theme.


== Screenshots ==


== Upgrade Notice ==



# 

== Changelog ==

## 0.4.1 - 2022-03-08
Updated to generator-wp-dev-env#1.3.0 ( wp-dev-env-grunt#1.3.1 wp-dev-env-frame#0.14.0 )

### Changed
- Updated to generator-wp-dev-env#1.3.0 ( wp-dev-env-grunt#1.3.1 wp-dev-env-frame#0.14.0 )

## 0.4.0 - 2021-11-24
Allow filtering the $excluded_handles for the loader

### Added
- Allow filtering the $excluded_handles. The handles that will not be added to the loader data. Eg to forcefully exclude some more dependencies.

## 0.3.1 - 2021-09-22
Update dependencies

### Changed
- Updated to generator-wp-dev-env#1.1.1 ( wp-dev-env-grunt#1.2.1 wp-dev-env-frame#0.12.0 )

## 0.3.0 - 2021-04-05
Updated readme.txt

### Added
- Create file `docs_hooks.md` on build and watch.

### Changed
- Updated readme.txt

### Fixed
- Handle multible localize data
- Don't mess up with equal handle names of scripts and styles. Allow same handle name for script and style.

## 0.2.0 - 2021-02-09
Change hook priorities to enable woocommerce-blocks to add inline script data wcSettings

### Added
- Filters `acll_loader_hook_priorities`|`acll_cleaner_hook_priorities` to allow hook priorities to be filtered

### Changed
- Hook priorities. To enable woocommerce-blocks to add inline script data wcSettings
- Only load assets when they are not already loaded on start

## 0.1.0 - 2021-02-08
Support to load CSS via JS

### Added
- Support to load CSS via JS
- PHP Api filter acll_loader_style_handles
- Api function acll_loader.loadAssetsByType
- Api function acll_loader.loadAssets
- Finally remove assets from print_scripts_array and print_styles_array, if still somehow in queue

### Removed
- Api function acll_loader.loadScripts, use acll_loader.loadAssetsByType instead

## 0.0.2 - 2021-02-07
Fix

### Removed
- Function acll_is_rest

## 0.0.1 - 2021-02-07
Cleans assets and loads JS ... no CSS loading by now

### Added
- Cleaner
- Loader and acll_loader script. (Only JS by now)
