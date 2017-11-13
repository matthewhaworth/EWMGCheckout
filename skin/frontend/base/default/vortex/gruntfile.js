module.exports = function(grunt) {
    grunt.initConfig({
        less: {
            development: {
                options: {
                    compress: true,
                    optimization: 2
                },
                files: [{
                    expand: true,
                    cwd: 'css/',
                    src: ['*.less', '!_*.less'],
                    dest: 'css/',
                    ext: '.css'
                }]
            }
        },
        watch: {
            styles: {
                files: ['css/**/*.less'], // which files to watch
                tasks: ['less'],
                options: {
                    nospawn: true
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['less', 'watch']);
};
