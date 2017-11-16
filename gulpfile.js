var gulp = require('gulp'),
  $ = require('gulp-load-plugins')(),
  pngquant = require('imagemin-pngquant');


// Compass Task
gulp.task('sass', function () {
  return gulp.src(['assets/scss/**/*.scss'])
    .pipe($.plumber())
    .pipe($.sass({
      sourcemap: true
    }))
    .pipe(gulp.dest('assets/css'));
});


// Minify
gulp.task('js', function () {
  return gulp.src(['./assets/js/src/**/*.js'])
    .pipe($.sourcemaps.init({
      loadMaps: true
    }))
    .pipe($.uglify())
    .on('error', $.util.log)
    .pipe($.sourcemaps.write('./map'))
    .pipe(gulp.dest('./assets/js/dist/'));
});

// JS Hint
gulp.task('jshint', function () {
  return gulp.src(['./assets/js/src/**/*.js'])
    .pipe($.plumber())
    .pipe($.jshint('./assets/.jshintrc'))
    .pipe($.jshint.reporter('jshint-stylish'));
});

// Image min
gulp.task('imagemin', function () {
  return gulp.src('./assets/images/**/*')
    .pipe($.imagemin({
      progressive: true,
      svgoPlugins: [{removeViewBox: false}],
      use: [pngquant()]
    }))
    .pipe(gulp.dest('./assets/images'));
});

// Copy required
gulp.task('copyJs', function () {
  return gulp.src(
    [
      'node_modules/chart.js/dist/Chart.min.js',
      'node_modules/imagesloaded/imagesloaded.pkgd.min.js',
      'node_modules/jsrender/jsrender.min.js',
      'node_modules/jsrender/jsrender.min.js.map',
      'node_modules/jquery-ui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js',
      'node_modules/jquery-ui-timepicker-addon/dist/i18n/*.js',
      'node_modules/jquery-ui/ui/i18n/*.js',
      'node_modules/font-awesome/css/font-awesome.min.css',
      'node_modules/font-awesome/css/font-awesome.css.map',
      'node_modules/font-awesome/fonts/**/*'
    ],
    {base: 'node_modules/'}
  )
    .pipe(gulp.dest('./assets/js/lib'));
});

// Copy mp6
gulp.task('copyMp6', function () {
  return gulp.src(
    [
      'node_modules/jquery-ui-mp6/src/css/*',
      'node_modules/jquery-ui-mp6/src/images/*'
    ],
    {base: 'node_modules/jquery-ui-mp6/src'}
  )
    .pipe(gulp.dest('./assets'));
});

// copy time picker
gulp.task('copyTimePicker', function () {
  return gulp.src(
    [
      'node_moduels/jquery-ui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css'
    ],
    {base: 'node_moduels/jquery-ui-timepicker-addon/dist'}
  )
    .pipe(gulp.dest('./assets/css'));
});

// Copy
gulp.task('copy', ['copyJs', 'copyMp6', 'copyTimePicker']);

// watch
gulp.task('watch', function () {
  gulp.watch('./assets/scss/**/*.scss', ['sass']);
  gulp.watch('./assets/js/src/**/*.js', ['js', 'jshint']);
});

// Build
gulp.task('build', ['copy', 'jshint', 'js', 'sass', 'imagemin']);

// Default Tasks
gulp.task('default', ['watch']);
