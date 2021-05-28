const gulp = require( 'gulp' );
const $ = require( 'gulp-load-plugins' )();
const pngquant = require( 'imagemin-pngquant' );
const mergeStream = require( 'merge-stream' );
const { scanDir } = require( '@kunoichi/grab-deps' );
const fs = require( 'fs' );
const named = require( 'vinyl-named' );
const webpackBundle = require( 'webpack' );
const webpack = require( 'webpack-stream' );

let plumber = true;

// Toggle flag.
gulp.task( 'noplumber', ( done ) => {
	plumber = false;
	done();
} );

// Compile sass.
gulp.task( 'scss:compile', function () {
	return gulp.src( [ 'assets/scss/**/*.scss' ] )
		.pipe( $.plumber() )
		.pipe( $.sass( {
			sourcemap: true
		} ) )
		.pipe( gulp.dest( 'assets/css' ) );
} );

// Stylelint.
gulp.task( 'scss:lint', () => {
	let task = gulp.src( [ 'assets/scss/**/*.scss' ] );
	if ( plumber ) {
		task = task.pipe( $.plumber() );
	}
	return task.pipe( $.stylelint( {
		reporters: [
			{
				formatter: 'string',
				console: true,
			},
		],
	} ) );
} );

// CSS tasks.
gulp.task( 'scss', gulp.parallel( 'scss:lint', 'scss:compile' ) );

// Minify
gulp.task( 'js:bundle', function () {
	const tmp = {};
	return gulp.src( [ './assets/js/src/**/*.js' ] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' ),
		} ) )
		.pipe( named() )
		.pipe( $.rename( function ( path ) {
			tmp[ path.basename ] = path.dirname;
		} ) )
		.pipe( webpack( require( './webpack.config.js' ), webpackBundle ) )
		.pipe( $.rename( function ( path ) {
			if ( tmp[ path.basename ] ) {
				path.dirname = tmp[ path.basename ];
			} else if ( path.extname === '.map' && tmp[ path.basename.replace( /\.js$/, '' ) ] ) {
				path.dirname = tmp[ path.basename.replace( /\.js$/, '' ) ];
			}
			return path;
		} ) )
		.pipe( gulp.dest( './assets/js/dist/') );
} );

// JS Hint
gulp.task( 'js:lint', function () {
	let task = gulp.src( [ './assets/js/src/**/*.js' ] );
	if ( plumber ) {
		task = task.pipe( $.plumber() );
	}
	return task.pipe( $.eslint( { useEslintrc: true } ) )
		.pipe( $.eslint.format() );
} );

// JS task
gulp.task( 'js', gulp.parallel( 'js:bundle', 'js:lint' ) );

// Imagemin
gulp.task( 'imagemin', function () {
	return gulp.src( './assets/images/**/*' )
		.pipe( $.imagemin( {
			progressive: true,
			svgoPlugins: [ { removeViewBox: false } ],
			use: [ pngquant() ]
		} ) )
		.pipe( gulp.dest( './assets/images' ) );
} );

// Copy required
gulp.task( 'copy', () => {
	return mergeStream(
		gulp.src(
			[
				'node_modules/chart.js/dist/Chart.min.js',
			]
		)
			.pipe( gulp.dest( './assets/vendor/chart-js' ) ),
		gulp.src(
			[
				'node_modules/jsrender/jsrender.min.js',
				'node_modules/jsrender/jsrender.min.js.map',
			]
		)
			.pipe( gulp.dest( './assets/vendor/jsrender' ) ),
		gulp.src(
			[
				'node_modules/jquery-ui/ui/i18n/*.js',
			],
			{ base: 'node_modules/jquery-ui/ui/i18n' }
		)
			.pipe( gulp.dest( './assets/vendor/jquery-ui-i18n' ) ),
		gulp.src(
			[
				'node_modules/font-awesome/css/font-awesome.min.css',
				'node_modules/font-awesome/css/font-awesome.css.map',
				'node_modules/font-awesome/fonts/**/*'
			],
			{ base: 'node_modules/font-awesome' }
		)
			.pipe( gulp.dest( './assets/vendor/font-awesome' ) ),
		gulp.src(
			[
				'node_modules/jquery-ui-mp6/src/css/*',
				'node_modules/jquery-ui-mp6/src/images/*'
			],
			{ base: 'node_modules/jquery-ui-mp6/src' }
		)
			.pipe( gulp.dest( './assets/vendor/jquery-ui-mp6' ) ),
		gulp.src(
			[
				'node_modules/jquery-ui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js',
				'node_modules/jquery-ui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css'
			],
			{ base: 'node_modules/jquery-ui-timepicker-addon/dist' }
		)
			.pipe( gulp.dest( './assets/vendor/jquery-ui-timepicker-addon' ) ),
		gulp.src(
			[
				'node_modules/jquery-ui-timepicker-addon/dist/i18n/*',
			],
		)
			.pipe( gulp.dest( './assets/vendor/jquery-ui-timepicker-addon/i18n' ) )
	);
} );

// watch
gulp.task( 'watch', function () {
	gulp.watch( './assets/scss/**/*.scss', gulp.parallel( [ 'scss:compile', 'scss:lint' ] ) );
	gulp.watch( './assets/js/src/**/*.js', gulp.parallel( 'js:lint', 'js:bundle' ) );
	gulp.watch( [ 'assets/**/*.css', 'assets/**/*.js' ], gulp.task( 'dump' ) );
} );

// Dump task.
gulp.task( 'dump', function( done ) {
	const result = [];
	for ( const dir of [ 'js/dist', 'css' ] ) {
		scanDir( `assets/${dir}` ).forEach( ( setting ) => {
			result.push( setting );
		} );
	}
	fs.writeFileSync( './wp-dependencies.json', JSON.stringify( result, null, "\t" ) );
	done();
} );

// Build
gulp.task( 'build', gulp.series( gulp.parallel( 'copy', 'js:bundle', 'scss:compile', 'imagemin' ), gulp.task( 'dump' ) ) );

// Lint task.
gulp.task( 'lint', gulp.series( 'noplumber', gulp.parallel( 'js:lint', 'scss:lint' ) ) );

// Default Tasks
gulp.task( 'default', gulp.task( 'watch' ) );
