/**
 * Internal dependencies
 */
import loadAssetsByType from './loadAssetsByType';

/**
 * Wrapper function around loadAssetsByType to load both scripts and styles in same moment.
 *
 * @param   {[array]}       scripts  Array of script handles.
 * @param   {[array]}       styles   Array of style handles.
 * @return  Promise                  Resolves when all is done. Loaded, or failed.
 */
const loadAssets = ( { scripts, styles } ) => new Promise( resolve => Promise.all( [
    ...( scripts && scripts.length > 0 ? [{ type: 'script', assets: scripts }] : [] ),
    ...( styles && styles.length > 0 ? [{ type: 'style', assets: styles }] : [] ),
].map( ( { type, assets } ) => loadAssetsByType( type, assets ) ) )
.then( results =>  resolve( [...results].reduce( ( acc, result ) => ( {
    errors: [
        ...acc.errors,
        ...result.errors,
    ],
    loaded: [
        ...acc.loaded,
        ...result.loaded,
    ],
} ), {
    errors: [],
    loaded: [],
} ) ) ) );

export default loadAssets;