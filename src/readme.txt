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

