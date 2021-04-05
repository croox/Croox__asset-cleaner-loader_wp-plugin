// const path = require('path');

const watchAddTaskMakeDocs = grunt => {
    grunt.hooks.addFilter( 'config.watch', 'config.watch.addTaskMakeDocs', config => ( {
        ...config,
        makeDocs: {
            files: [
                'src/**/*.php',
                ...grunt.option( 'pattern' ).exclude,
            ],
            tasks: [
                'makeDocs',
                // afterTasks
                ...[
                    'sync',
                    'sound:blob',
                ],
            ]
        },
    } ), 90 );
};

module.exports = watchAddTaskMakeDocs;