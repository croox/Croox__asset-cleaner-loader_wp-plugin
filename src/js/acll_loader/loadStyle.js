
/**
 * Load stylesheet
 *
 * Inspired by https://github.com/feross/load-script2/blob/master/index.js
 *
 * @param   string  src         Any url that you would like to load. May be absolute or relative.
 * @param   object  attrs       An object that contains HTML attributes to set on the <link> tag.
 *                              For example, the value { id: 'hi' } would set the attribute id="hi" on the <link> tag before it is injected.
 * @param   Element parentNode  The HTML node to which the <link> tag will be appended. If not specified, defaults to the <head> tag.
 * @return  Promise             Returns a promise which resolves to the link node that was appended to the DOM,
 *                              or rejects with err if any occurred.
 */
const loadStyle = ( src, attrs, parentNode ) => {
    return new Promise( ( resolve, reject ) => {
        const ele = document.createElement( 'link' );
        ele.href = src;

        attrs = attrs ? attrs : {};
        attrs.rel = attrs.rel ? attrs.rel : 'stylesheet';
        attrs.type = attrs.type ? attrs.type : 'text/css';

        // Trigger an asynchronous stylesheet download by using invalid media attribute.
        // See https://stackoverflow.com/questions/32759272/how-to-load-css-asynchronously#answer-40314216
        const media = attrs.media ? attrs.media : 'all';
        attrs.media = 'loadAsync';

        for ( const [k, v] of Object.entries( attrs ) ) {
            ele.setAttribute( k, v );
        }

        ele.onload = () => {
            ele.onerror = ele.onload = null;
            if( ele.media !== media ) {
                ele.media = media;
            }
            resolve( ele );
        }

        ele.onerror = () => {
            ele.onerror = ele.onload = null;
            reject( new Error(`Failed to load ${src}`) );
        }

        const node = parentNode || document.head || document.getElementsByTagName( 'head' )[0];
        node.prepend( ele );    // prepend instead of appendChild. Position it before theme styles
    } );
}

  export default loadStyle;