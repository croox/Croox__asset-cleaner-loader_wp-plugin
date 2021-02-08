/**
 * getHandlesWithDeps
 *
 * @param   string  type    Type of asset: script|style
 * @param   array   handles Array of script handles
 * @param   object  loaded  Should be empty initially.
 *                          Used internally to store which dependencies are loaded into the handles array already-
 * @return  array           Array of script handles with all their dependency handles prepended.
 *                          Note: Scripts that were already enqueued by wp queue are excluded
 */
const getHandlesWithDeps = ( type, handles, loaded ) => {
    if ( ! loaded ) {
        loaded = {};
        [...handles].map( handle => {
            loaded[handle] = false;
        } );
    }

    const availableHandles = acll_loader[type+'s']
        ? Object.keys( acll_loader[type+'s'] )
        : [];

    [...handles].map( handle => {
        if ( availableHandles.includes( handle ) && acll_loader[type+'s'][handle] ) {   // Check if asset existing in loc_data. Excludes assets that were already enqueued bu wp queue are excluded
            const handleIndex = handles.findIndex( h => h === handle );
            const deps = Array.isArray( acll_loader[type+'s'][handle]['deps'] )
                ? acll_loader[type+'s'][handle]['deps']
                : 'object' === typeof object
                    ? Object.values( acll_loader[type+'s'][handle]['deps'] )
                    : [] ;
            [...deps].reverse().map( depHandle => {
                if ( availableHandles.includes( depHandle ) ) {
                    handles.splice( handleIndex, 0, depHandle );
                    loaded[depHandle] = loaded[depHandle] ? loaded[depHandle] : false;
                }
            } );
        }
        loaded[handle] = true;
    } );

    if ( Object.values( loaded ).includes( false ) ) {
        return getHandlesWithDeps( type, handles, loaded );
    } else {
        // unique
        return handles.filter( ( value, index, self ) => self.indexOf( value ) === index );
    }
};

export default getHandlesWithDeps;