const path = require('path');

const loadCustomTasks = grunt => {
	grunt.hooks.addAction( 'startGrunt.loadTasks.after', 'startGrunt.loadTasks.after.loadCustomTasks', () => {
        grunt.loadTasks( path.join( path.resolve( 'grunt' ), 'tasks' ) );
	}, 10 );
}

module.exports = loadCustomTasks;
