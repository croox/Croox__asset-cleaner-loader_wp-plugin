
const addTasksToBuild = grunt => {

    grunt.hooks.addFilter( 'tasks.build.tasks', 'tasks.build.tasks.addTasksToBuild', tasks => {
        return [
            ...tasks,
            'makeDocs',
        ];
    }, 90 );

}

module.exports = addTasksToBuild;
