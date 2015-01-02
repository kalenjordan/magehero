// Get modules
var gulp = require('gulp');
var gulpLoadPlugins = require('gulp-load-plugins');
var plugins = gulpLoadPlugins();

// Task Styles
gulp.task('styles', function () {
    gulp.src('sass/app.scss')
        .pipe(plugins.plumber())
        .pipe(plugins.sass())
        .pipe(plugins.minifyCss({keepBreaks:true}))
        .pipe(gulp.dest('css'));

    gulp.src('sass/map.scss')
        .pipe(plugins.plumber())
        .pipe(plugins.sass())
        .pipe(plugins.minifyCss({keepBreaks:true}))
        .pipe(gulp.dest('css'));
});

// Task scripts
gulp.task('scripts', function() {
    gulp.src('js/src/**/*.js')
        .pipe(plugins.plumber())
        .pipe(plugins.concat('main.min.js'))
        .pipe(plugins.uglify())
        .pipe(gulp.dest('js'))
});

// Optimise images
gulp.task('images', function () {
    gulp.src('img-orig/**/*.{png,gif,jpg}')
        .pipe(plugins.plumber())
        .pipe(plugins.imagemin())
        .pipe(gulp.dest('img/'));
});

gulp.task('watch', function () {
    gulp.watch('sass/**/*.scss', ['styles']);
    gulp.watch('js/src/**/*.js', ['scripts']);
    gulp.watch('img-orig/**', ['images']);
});

// The default task (called when you run `gulp` from cli)
gulp.task('default', ['watch']);
