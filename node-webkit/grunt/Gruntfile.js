module.exports = function(grunt) {
	grunt.initConfig({
		nodewebkit: {
			options: {
				version: '0.11.6',
				buildDir: './build',
				macIcns: './icon.icns',
				platforms: ['osx', 'win', 'linux']
			},
			src: '../yongche/**/*'
		}
	});

	grunt.loadNpmTasks('grunt-node-webkit-builder');
	grunt.registerTask('default', ['nodewebkit']);
}