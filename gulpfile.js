var gulp     = require('gulp'),
    $        = require('gulp-load-plugins')(),
    pngquant = require('imagemin-pngquant');


// Compass Task
gulp.task('compass', function () {
    return gulp.src(['src/scss/**/*.scss'])
        .pipe($.plumber())
        .pipe($.compass({
            config_file: 'assets/config.rb',
            sourcemap  : false,
            debug      : true,
            css        : 'src/css',
            sass       : 'src/scss'
        }))
        .pipe(gulp.dest('src/css'));
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
            use        : [pngquant()]
        }))
        .pipe(gulp.dest('./assets/images'));
});

// Copy required
gulp.task('copyJs', function () {
    return gulp.src(
        [
            'bower_components/chartjs/Chart.min.js',
            'bower_components/imagesloaded/imagesloaded.pkgd.min.js',
            'bower_components/jsrender/jsrender.min.js',
            'bower_components/jquery-timepicker-addon/jquery-ui-timepicker-addon.js',
            'bower_components/jquery-timepicker-addon/i18n/*.js',
            'bower_components/jquery-ui/ui/i18n/*.js'
        ],
        {base: 'bower_components/'}
    )
        .pipe(gulp.dest('./assets/js/lib'));
});

// Copy mp6
gulp.task('copyMp6', function() {
    return gulp.src(
        [
            'bower_components/jquery-ui-mp6/src/css/*',
            'bower_components/jquery-ui-mp6/src/images/*'
        ],
        {base: 'bower_components/jquery-ui-mp6/src'}
    )
        .pipe(gulp.dest('./assets'));
});

// copy time picker
gulp.task('copyTimePicker', function(){
        return gulp.src(
            [
                'bower_components/jquery-timepicker-addon/jquery-ui-timepicker-addon.css',
            ],
            {base: 'bower_components/jquery-timepicker-addon'}
        )
            .pipe(gulp.dest('./assets/css'));
});

// Copy
gulp.task('copy', ['copyJs', 'copyMp6', 'copyTimePicker']);

// watch
gulp.task('watch', function () {
    gulp.watch('./assets/sass/**/*.scss', ['compass']);
    gulp.watch('./assets/js/src/**/*.js', ['js', 'jshint']);
});

// Build
gulp.task('build', ['copy', 'jshint', 'js', 'compass', 'imagemin']);

// Default Tasks
gulp.task('default', ['watch']);
