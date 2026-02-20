// Dependencies
var gulp = require('gulp');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var sass = require('gulp-dart-sass');
var sourcemaps = require('gulp-sourcemaps');
//var filesExist = require('files-exist');
const { watch } = require('gulp');

/**
 *  CSS
 */
gulp.task('styles', function ()
{
    return gulp.src('public_html/css/src/styles.scss')
    .pipe(sourcemaps.init())
    .pipe(concat('styles.css'))
    .pipe(sass({ quietDeps: true }))
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('public_html/css'));
});

gulp.task('css-prod', function ()
{
    return gulp.src('public_html/css/src/styles.scss')
    //.pipe(sourcemaps.init())
    .pipe(concat('styles.css'))
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(gulp.dest('public_html/css'));
});


// Compile and watch
gulp.task('watch-sass', function()
{
    gulp.watch('public_html/css/src/scss/*/*.scss', gulp.series('styles'));
});

// Landing pages
// gulp.task('watch-sass-duckduckgo', function()
// {
//     gulp.watch('public_html/css/src/pages/*.scss', gulp.series('css-landing-duckduckgo'));
// });


/**
 *  JavaScript
 */
const sourcePath = 'public_html/js/src/';
var jsFiles =
[
    // libraries
    sourcePath + '/libraries/jquery/jquery-3.7.1.min.js',
    sourcePath + '/libraries/bootstrap-5.3.6/bootstrap.bundle.min.js',
    // application code
    sourcePath + 'login.js',
    sourcePath + 'request.js',
    sourcePath + 'new.js',
    sourcePath + 'checklist.js',
    sourcePath + 'app.js',
];

var jsDest = 'public_html/js/dist';

// Concatenate and minify scripts
gulp.task('scripts', function()
{
    return gulp.src(jsFiles)
        .pipe(concat('app.js'))
        .pipe(gulp.dest(jsDest))
        .pipe(rename('app.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(jsDest));
});

// Watch JS files
gulp.task('watch-js', function()
{
    gulp.watch('public_html/js/src/*.js', gulp.series('scripts'));
});


gulp.task('watch-sass-js', function()
{
    gulp.watch('public_html/js/src/*.js', gulp.series('scripts'));
    gulp.watch('public_html/css/src/scss/*/*.scss', gulp.series('styles'));
});

// Default task
function defaultTask(cb)
{
    // place code for your default task here
    cb();
}
exports.default = defaultTask
