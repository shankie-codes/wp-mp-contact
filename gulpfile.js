// Include gulp
var gulp = require('gulp');

module.exports = gulp;

// Include Our Plugins
var jshint = require('gulp-jshint');
var compass = require('gulp-compass');
var concat = require('gulp-concat');
// var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var browserSync = require('browser-sync');
var filter = require('gulp-filter');
var uglify = require('gulp-uglifyjs');
var minifyCSS = require('gulp-minify-css');
var autoprefixer = require('gulp-autoprefixer');
var plumber = require('gulp-plumber');
var onError = function (err) {  
    console.log(err);
};

// Compile Our Sass/Compass
gulp.task('sass', function() {
    return gulp.src('css/*.scss')
        .pipe(plumber({
            errorHandler: onError
        }))
        .pipe(compass({
            config_file: './config.rb',
            css: 'css',
            sass: 'css'
        }))
        .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'ff 17', 'opera 12.1', 'ios 6', 'android 4'))
        .pipe(gulp.dest('./css/'));
});

// Browsersync
gulp.task('browser-sync', function() {
    browserSync({
        proxy: "localhost/",
        files: ["style.css", "js/*.js", "*.php", "*.html"]
    });
});


// Watch Files For Changes
gulp.task('watch', function() {
    gulp.watch('css/*.scss', ['sass']);
});

// Default Task
gulp.task('default', ['sass', 'watch']);
