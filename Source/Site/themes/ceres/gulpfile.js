var gulp = require('gulp'),
        sass = require('gulp-sass'),
        notify = require("gulp-notify"),
        uglify = require('gulp-uglify'),
        cssnano = require('gulp-cssnano'),
        rename = require('gulp-rename'),
        concat = require('gulp-concat'),
        dirSync = require('gulp-directory-sync'),
        watch = require('gulp-watch'),
        sourcemaps = require('gulp-sourcemaps'),
        imagemin = require('gulp-imagemin'),
        pngquant = require('imagemin-pngquant'),
        browserSync = require('browser-sync').create();
var config = {
    bowerDir: './bower_components',
    publicRootDir: './'
}, srcs = {
    scss: './src/scss',
    js: './src/js',
    fonts: './src/fonts',
    img: './src/images'
}, dests = {
    css: config.publicRootDir + '/assets/css',
    js: config.publicRootDir + '/assets/js',
    fonts: config.publicRootDir + '/assets/fonts',
    img: config.publicRootDir + '/assets/images',
    jplayer: config.publicRootDir + '/assets/jplayer'
};

gulp.task('sync-fonts', function () {
    return gulp.src(srcs.fonts + '/**/*', {base: srcs.fonts})
            //.pipe(dirSync(srcs.fonts, dests.fonts, {printSummary: false, ignore: '.gitignore'}))
            //.pipe(watch(srcs.fonts, {base: srcs.fonts}))
            .pipe(gulp.dest(dests.fonts))
            .on('error', notify.onError(function (error) {
                return "Error: " + error.message;
            }));
});

gulp.task('sync-images', function () {
    return gulp.src(srcs.img + '/**/*', {base: srcs.img})
//            .pipe(watch(srcs.img, {base: srcs.img}))
            .pipe(imagemin({
                progressive: true,
                use: [pngquant()]
            }))
            .pipe(gulp.dest(dests.img))
            .pipe(dirSync(srcs.img, dests.img, {printSummary: false, ignore: '.gitignore'}))
            .on('error', notify.onError(function (error) {
                return "Error: " + error.message;
            }));
});
gulp.task('sync-jplayer', function () {
    var skin = config.bowerDir + '/jplayer/dist/skin/blue.monday';
    return gulp.src([skin, config.bowerDir + '/jplayer/dist/jplayer/*'])
            .pipe(dirSync(skin, dests.jplayer + '/blue.monday', {printSummary: false, ignore: '.gitignore'}))
            .pipe(gulp.dest(dests.jplayer))
            .on('error', notify.onError(function (error) {
                return "Error: " + error.message;
            }));
});

gulp.task('icons', function () {
    return gulp.src(config.bowerDir + '/fontawesome/fonts/**.*').pipe(gulp.dest(dests.fonts));
});

gulp.task('browser-sync', ['css'], function () {
    browserSync.init({
        server: config.publicRootDir
    });
    gulp.watch(config.publicRootDir + "/**/*.html").on('change', browserSync.reload); // reload on html changes.
});

gulp.task('css', function () {
    return gulp.src([
//        srcs.sassPath + '/style-rtl.scss', // Use it with LTR/RTL styles
        srcs.scss + '/style.scss'
    ])
            .pipe(sourcemaps.init({loadMaps: true}))
            .pipe(sass({
                style: 'expanded',
                includePaths: [
                    srcs.scss,
                    config.bowerDir + '/bootstrap-sass/assets/stylesheets/bootstrap', // Use it with LTR only styles
                    config.bowerDir + '/mappy-breakpoints',
//            config.bowerDir + '/bootstrap-sass-rtl/src/stylesheets/bootstrap', // Use it with LTR/RTL styles
                    config.bowerDir + '/fontawesome/scss',
                    config.bowerDir + '/bootstrap-select/sass'
                ]
            }).on("error", notify.onError(function (error) {
                return "Error: " + error.message;
            })))
            .pipe(gulp.dest(dests.css))
            .pipe(rename({suffix: '.min'}))
            .pipe(cssnano({discardComments: {removeAll: true}}))
            .pipe(sourcemaps.write('.'))
            .pipe(gulp.dest(dests.css))
            .pipe(browserSync.stream());
});

gulp.task('scripts', function () {
    gulp.src([
//        ## Include jQuery to your grouped JavaScript file if it is not already included within your page context.
//        config.bowerDir + '/jquery/dist/jquery.js',
//
//        ## Include needed Bootstrap component from following scripts
        config.bowerDir + '/bootstrap-sass/assets/javascripts/bootstrap/affix.js',
        config.bowerDir + '/bootstrap-sass/assets/javascripts/bootstrap/alert.js',
//        config.bowerDir + '/bootstrap-sass/assets/javascripts/bootstrap/button.js',
//        config.bowerDir + '/bootstrap-sass/assets/javascripts/bootstrap/carousel.js',
        config.bowerDir + '/bootstrap-sass/assets/javascripts/bootstrap/collapse.js',
        config.bowerDir + '/bootstrap-sass/assets/javascripts/bootstrap/dropdown.js',
        config.bowerDir + '/bootstrap-sass/assets/javascripts/bootstrap/modal.js',
        config.bowerDir + '/bootstrap-sass/assets/javascripts/bootstrap/tooltip.js',
        config.bowerDir + '/bootstrap-sass/assets/javascripts/bootstrap/popover.js',
//        config.bowerDir + '/bootstrap-sass/assets/javascripts/bootstrap/scrollspy.js',
//        config.bowerDir + '/bootstrap-sass/assets/javascripts/bootstrap/tab.js',
        config.bowerDir + '/bootstrap-sass/assets/javascripts/bootstrap/transition.js',
//
//        ## To add swipe behavior to Bootstrap's Carousel if carousel.js included above.
//        config.bowerDir + '/bootstrap-carousel-swipe/carousel-swipe.js',
//
//        ## Make use of Bootstrap Modal more friendly. Include bootstrap-dialog.scss with your style.scss.
//        ## Examples: http://nakupanda.github.io/bootstrap3-dialog/
//        srcs.scriptsPath + '/bootstrap-dialog/src/js/bootstrap-dialog.js',

//        ## Responsive toolkit for flex-slider
        config.bowerDir + '/responsive-toolkit/dist/bootstrap-toolkit.min.js',
        config.bowerDir + '/bootstrap-select/js/bootstrap-select.js',
        config.bowerDir + '/jquery.dotdotdot/src/jquery.dotdotdot.js',
        srcs.js + '/flexslider/jquery.flexslider.js',
        srcs.js + '/jquery.loadmore.js',
        srcs.js + '/scripts.js'
    ])
            .pipe(concat('main.js'))
            .pipe(gulp.dest(dests.js))
            .pipe(rename({suffix: '.min'}))
            .pipe(uglify().on("error", notify.onError(function (error) {
                return "Error compiling JavaScript: " + error.message;
            })))
            .pipe(gulp.dest(dests.js));
});

gulp.task('watch', function () {
    gulp.watch(srcs.scss + '/**/*.scss', ['css']);
    gulp.watch(srcs.js + '/**/*.js', ['scripts']);
});

gulp.task('default', ['sync-fonts', 'sync-images', 'icons', 'css', 'scripts', 'sync-jplayer', 'watch']);
