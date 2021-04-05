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
import parseSerialized from '../generic/utils/parseSerialized';
import getHandlesWithDeps from './getHandlesWithDeps';
import loadStyle from './loadStyle';

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
 * Get a list of assets by type already loaded on start
 *
 * @param   string  type    Type of asset: script|style
 * @return  array           Array of asset sources
 */
let loadedOnStart = {};
const getLoadedOnStart = type => {
    if ( ! loadedOnStart[type+'s'] ) {
        switch( type ) {
            case 'script':
                loadedOnStart[type+'s'] = Array.from( document.getElementsByTagName( 'script' ) )
                    .map( node => node.src )
                    .filter( src => !! src )
                    .map( src => src.replace( /\?ver=.*$/, '' ) );
                break;
            case 'style':
                loadedOnStart[type+'s'] = Array.from( document.getElementsByTagName( 'link' ) )
                    .map( node => node.href )
                    .filter( src => !! src )
                    .map( src => src.replace( /\?ver=.*$/, '' ) )
                    .filter( src => src.endsWith( '.css' ) );
                break;
        }
    }
    return loadedOnStart[type+'s'];
}

/**
 * loadAssetsByType
 *
 * @param   string  type    Type of asset: script|style
 * @param   array  handles  Array of script handles
 * @return  Promise         Promise resolves when all scripts and their dependencies are loaded
 */
const assetsRequested = {
    style: {},
    script: {},
};
const loadAssetsByType = ( type, handles ) => new Promise( resolve => {
    let results = { errors: [], loaded: [] };
    if ( ! handles.length ) {
        return resolve( {...results, errors: [...results.errors, 'no ' + type + ' handles'] } );
    }
    const availableHandles = acll_loader[type+'s']
        ? Object.keys( acll_loader[type+'s'] )
        : [];
    if ( ! availableHandles.length ) {
        return resolve( {...results, errors: [...results.errors, 'no ' + type + 's'] } );
    }
    const loadAsset = 'script' === type ? loadScript : loadStyle;
    handles = getHandlesWithDeps( type, handles );
	return resolve( [...handles].reduce( ( accumulatorPromise, handle ) => {
        const loader = result => {
			return new Promise( resolve => {
                if ( assetsRequested[type][handle] ) {
                    return resolve( result );
                }
                if ( availableHandles.includes( handle ) && acll_loader[type+'s'][handle] ) {
                    if ( getLoadedOnStart( type ).includes( acll_loader[type+'s'][handle].src ) ) {
                        return resolve( result );
                    }
                    if ( 'script' === type ) {
                        // Add localized data as global
                        if ( acll_loader[type+'s'][handle].loc_data ) {
                            [...( acll_loader[type+'s'][handle].loc_data + "\n" ).matchAll( /var\s(\S+)\s=\s([\s\S]+?)(?=;\n)/g )].map( matches => {
                                if ( 3 === matches.length ) {
                                    window[matches[1]] = parseSerialized( matches[2] );
                                }
                            } );
                        }
                        // Set translations
                        if ( acll_loader[type+'s'][handle].translations && acll_loader[type+'s'][handle].translations.length ) {
                            [...acll_loader[type+'s'][handle].translations].map( translation => setTranslations(
                                acll_loader[type+'s'][handle].textdomain,
                                parseSerialized( translation )
                            ) );
                        }
                        // Set Before
                        if ( acll_loader[type+'s'][handle].before ) {
                            const tagBefore = document.createElement( 'script' );
                            tagBefore.innerHTML = [...acll_loader[type+'s'][handle].before].filter( a => !! a ).join( '\n' );
                            document.body.appendChild( tagBefore );
                        }
                    }
                    // Load file
                    assetsRequested[type][handle] = true;
                    return loadAsset( acll_loader[type+'s'][handle].src, acll_loader[type+'s'][handle].attrs )
                        .then( node => {
                            // Set After
                            if ( acll_loader[type+'s'][handle].after ) {
                                let tagAfter = false;
                                if ( 'script' === type ) {
                                    tagAfter = document.createElement( 'script' );
                                } else {
                                    tagAfter = document.createElement( 'style' );
                                    tagAfter.id = handle + '-inline-css';
                                    tagAfter.type = acll_loader[type+'s'][handle].attrs.type ? acll_loader[type+'s'][handle].attrs.type : 'text/css';
                                    document.body.appendChild( tagAfter );
                                }
                                tagAfter.innerHTML = [...acll_loader[type+'s'][handle].after].filter( a => !! a ).join( '\n' );
                                document.body.appendChild( tagAfter );
                            }
                            // Next
                            window.setTimeout( () => {
                                return resolve( {...result, loaded: [...result.loaded, {
                                    handle,
                                    node,
                                    type,
                                } ] } );
                            }, 1 )
                        } )
                        .catch( err => {
                            assetsRequested[type][handle];
                            return resolve( {...result, errors: [...result.errors, err] } );
                        } );
                } else {
                    assetsRequested[type][handle];
                    return resolve( {...result, errors: [...result.errors, type + ' not found: ' + handle] } );
                }
			} );
		};
        return accumulatorPromise.then( loader ).catch( loader );
	}, Promise.resolve( results ) ) );
} );

export default loadAssetsByType;