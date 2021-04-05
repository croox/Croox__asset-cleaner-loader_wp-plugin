const path = require('path');
const fs = require('fs');
const DocBlock = require('docblock');

const makeDocs = grunt => {
	grunt.registerTask( 'makeDocs', '???', function() {

        let fileContent = [
            '# Action and Filter Hooks',
            '',
        ];

        grunt.file.expand( {}, [
            path.resolve( 'src' ) + '/**/*.php',
        ] ).map( file => {
            let hooks = [];
            [
                'apply_filters',
                'do_action',
            ].map( functionName => {
                const matches = fs.readFileSync( file, { encoding: 'utf8' } ).match( new RegExp(
                    '(\\/\\*\\*[\\r\\n](.|[\\r\\n])*?\\*\\/)[\\r\\n].*',
                    'g'
                ) );
                let results = [];
                if ( Array.isArray( matches ) ) {
                    [...matches].filter( match => match.includes( functionName ) ).map( match => {
                        const docBlock = new DocBlock();
                        results = [
                            ...results,
                            ...docBlock.parse( match, 'js' ),
                        ];
                    } );
                }
                hooks = [
                    ...hooks,
                    ...[...results].map( result => {
                        let hookName = result.code.match( new RegExp( functionName + '\\(\\s[\'"](\\S*?)[\'"]' ) );
                        return {
                            name: Array.isArray( hookName ) ? hookName[1] : '',
                            type: functionName === 'apply_filters' ? 'filter' : 'action',
                            title: result.title,
                            params: [...result.tags.params].map( param => {
                                const desc = param.description.split( '  ' ).map( a => a.trim() ).filter( a => a.length );
                                return {
                                    type: param.name,
                                    name: desc.length > 0 ? desc[0] : '',
                                    desc: desc.length > 1 ? desc[1] : '',
                                }
                            } ),
                        };
                    } )
                ];
            } );

            if ( hooks.length ) {

                fileContent = [...fileContent,
                    '',
                    '## File: ' + file.replace( path.resolve( 'src' ), '' ),
                    '',
                ];

                [...hooks].map( hook => {

                    fileContent = [...fileContent,
                        '- `' + hook.type + '` `' + hook.name + '`',
                        '  ',
                        '  ' + hook.title,
                        '  ',
                        '  #### Params:',
                        '  ',
                    ];

                    [...hook.params].map( param => {
                        fileContent = [...fileContent,
                            '  - `' + param.type + '` ' + param.name,
                            '  ',
                            '    ' + param.desc,
                        ];
                    } );

                    fileContent = [...fileContent,
                        '',
                    ];

                } );
            }

        } );

        grunt.file.write(
            path.join( grunt.option( 'destination' ), 'docs_hooks.md' ),
            fileContent.join( '\n' )
        );

	} );
};

module.exports = makeDocs;
