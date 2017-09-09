/* jshint node:true */
/* global module */
module.exports = function( grunt ) {
	require( 'matchdep' ).filterDev( ['grunt-*', '!grunt-legacy-util'] ).forEach( grunt.loadNpmTasks );
	grunt.util = require( 'grunt-legacy-util' );

	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		jshint: {
			options: grunt.file.readJSON( '.jshintrc' ),
			grunt: {
				src: ['Gruntfile.js']
			},
			all: ['Gruntfile.js', 'templates/*.js']
		},
		checktextdomain: {
			options: {
				correct_domain: false,
				text_domain: 'anticonferences',
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'_n:1,2,4d',
					'_ex:1,2c,3d',
					'_nx:1,2,4c,5d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: ['**/*.php', '!**/node_modules/**'],
				expand: true
			}
		},
		clean: {
			all: ['templates/*.min.js', 'templates/*.min.css']
		},
		makepot: {
			target: {
				options: {
					domainPath: 'languages',
					exclude: ['/node_modules'],
					mainFile: 'anticonferences.php',
					potFilename: 'anticonferences.pot',
					processPot: function( pot ) {
						pot.headers['last-translator']      = 'WordCampParis <wordcamp.paris@gmail.com>';
						pot.headers['language-team']        = 'FRENCH <wordcamp.paris@gmail.com>';
						pot.headers['report-msgid-bugs-to'] = 'https://github.com/wordcampparis/anticonferences/issues';
						return pot;
					},
					type: 'wp-plugin'
				}
			}
		},
		uglify: {
			minify: {
				extDot: 'last',
				expand: true,
				ext: '.min.js',
				src: ['templates/*.js', '!*.min.js']
			},
			options: {
				banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
				'<%= grunt.template.today("UTC:yyyy-mm-dd h:MM:ss TT Z") %> - ' +
				'https://github.com/wordcampparis/anticonferences */\n'
			}
		},
		cssmin: {
			minify: {
				extDot: 'last',
				expand: true,
				ext: '.min.css',
				src: ['templates/*.css', '!*.min.css']
			}
		},
		jsvalidate:{
			src: ['templates/*.js'],
			options:{
				globals: {},
				esprimaOptions:{},
				verbose: false
			}
		}
	} );

	/**
	 * Register tasks.
	 */

	grunt.registerTask( 'jstest', ['jsvalidate', 'jshint'] );

	grunt.registerTask( 'shrink', ['cssmin', 'uglify'] );

	grunt.registerTask( 'commit',  ['checktextdomain', 'jstest'] );

	grunt.registerTask( 'release', ['checktextdomain', 'makepot', 'clean', 'jstest', 'shrink'] );

	// Default task.
	grunt.registerTask( 'default', ['commit'] );
};
