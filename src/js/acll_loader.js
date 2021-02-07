/**
 * External dependencies
 */
import loadScript from 'load-script2';

/**
 * WordPress dependencies
 */
const { setLocaleData, __ } = wp.i18n;

/**
 * Internal dependencies
 */
import parseSerialized from './generic/utils/parseSerialized';

const {
    scripts,
} = acll_loader;

/**
 * getHandlesWithDeps
 *
 * @param   array   handles Array of script handles
 * @param   object  loaded  Should be empty initially.
 *                          Used internally to store which dependencies are loaded into the handles array already-
 * @return  array           Array of script handles with all their dependency handles prepended.
 *                          Note: Scripts that were already enqueued by wp queue are excluded
 */
const getHandlesWithDeps = ( handles, loaded ) => {
    if ( ! loaded ) {
        loaded = {};
        [...handles].map( handle => {
            loaded[handle] = false;
        } );
    }

    const scriptsHandles = Object.keys( scripts );

    [...handles].map( handle => {
        if ( scriptsHandles.includes( handle ) && scripts[handle] ) {   // Check if script existing in loc_data. Excludes scripts that were already enqueued bu wp queue are excluded
            const handleIndex = handles.findIndex( h => h === handle );
            const deps = Array.isArray( scripts[handle]['deps'] )
                ? scripts[handle]['deps']
                : 'object' === typeof object
                    ? Object.values( scripts[handle]['deps'] )
                    : [] ;
            [...deps].reverse().map( depHandle => {
                if ( scriptsHandles.includes( depHandle ) ) {
                    handles.splice( handleIndex, 0, depHandle );
                    loaded[depHandle] = loaded[depHandle] ? loaded[depHandle] : false;
                }
            } );
        }
        loaded[handle] = true;
    } );

    if ( Object.values( loaded ).includes( false ) ) {
        return getHandlesWithDeps( handles, loaded );
    } else {
        // uniqe
        return handles.filter( ( value, index, self ) => self.indexOf( value ) === index );
    }
};

/**
 * Copy of wp print_translations inline script
 * @see WP_Scripts::print_translations
 * @see wp-includes/class.wp-scripts.php
 */
const setTranslations = ( domain, translations ) => {
    const localeData = translations.locale_data[ domain ] || translations.locale_data.messages;
    localeData[""].domain = domain;
    setLocaleData( localeData, domain );
};

/**
 * loadScripts
 *
 * @param   array  handles  Array of script handles
 * @return  Promise         Promise resolves when all scripts and their dependencies are loaded
 */
const scriptsRequested = {};
const loadScripts = handles => new Promise( ( resolve, reject ) => {
    if ( ! handles.length ) {
        return reject( 'no script handles' );
    }
    const scriptsHandles = Object.keys( scripts );
    if ( ! scripts || ! scriptsHandles.length ) {
        return reject( 'no scripts' );
    }
    handles = getHandlesWithDeps( handles )
	return resolve( [...handles].reduce( ( accumulatorPromise, handle ) => {
        const loader = result => {
			return new Promise( ( resolve, reject ) => {
                if ( scriptsRequested[handle] ) {
                    return resolve( result );
                }
                if ( scriptsHandles.includes( handle ) && scripts[handle] ) {
                    // Add localized data as global
                    if ( scripts[handle].loc_data ) {
                        const matches = scripts[handle].loc_data.match( /^var\s(\S+)\s=\s([\s\S]+)(?=;$)/ );
                        if ( 3 === matches.length ) {
                            window[matches[1]] = parseSerialized( matches[2] );
                        }
                    }
                    // Set translations
                    if ( scripts[handle].translations && scripts[handle].translations.length ) {
                        [...scripts[handle].translations].map( translation => setTranslations(
                            scripts[handle].textdomain,
                            parseSerialized( translation )
                        ) );
                    }
                    // Set Before
                    if ( scripts[handle].before ) {
                        const tagBefore = document.createElement( 'script' );
                        tagBefore.innerHTML = [...scripts[handle].before].filter( a => !! a ).join( '\n' );
                        document.body.appendChild( tagBefore );
                    }
                    // Load file
                    scriptsRequested[handle] = true;
                    return loadScript( scripts[handle].src, scripts[handle].attrs )
                        .then( node => {
                            // Set After
                            if ( scripts[handle].after ) {
                                const tagAfter = document.createElement( 'script' );
                                tagAfter.innerHTML = [...scripts[handle].after].filter( a => !! a ).join( '\n' );
                                document.body.appendChild( tagAfter );
                            }
                            // Next
                            window.setTimeout( () => {
                                return resolve( {...result, loaded: [...result.loaded, {
                                    handle,
                                    node,
                                } ] } );
                            }, 1 )
                        } )
                        .catch( err => {
                            scriptsRequested[handle];
                            return reject( {...result, errors: [...result.errors, err] } );
                        } );
                } else {
                    scriptsRequested[handle];
                    return reject( {...result, errors: [...result.errors, 'Script not found: ' + handle] } );
                }
			} );
		};
        return accumulatorPromise.then( loader ).catch( loader );
	}, Promise.resolve( { errors: [], loaded: [] } ) ) );
} );

acll_loader.loadScripts = loadScripts;



