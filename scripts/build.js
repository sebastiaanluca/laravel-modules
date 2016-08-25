/**
 * The main application entry file
 */

const dotenv = require('dotenv')
const gulp = require('gulp')
const gutil = require('gulp-util')
const path = require('path')
const del = require('del')
const browserSync = require('browser-sync').create()

require('./help')
require('./webpack')

// Load environment variables
dotenv.load({path: path.resolve(process.cwd(), '.env')})

// Configure app
const config = require('./config')

// Load our main module files
require('./build')

//

gulp.task('default', callback => {
    if (gutil.env.production) {
        config.context = 'production'
    }
    
    gulp.start(['clean', 'webpack'], callback)
})

gulp.task('watch', callback => {
    config.context = 'watch'
    
    gulp.start(['clean', 'webpack'], callback)
})

gulp.task('hot', callback => {
    config.context = 'hot'
    
    gulp.start(['webpack'], callback)
})

gulp.task('serve', callback => {
    config.context = 'watch'
    
    // Serve files from the root of this project
    browserSync.init({
        proxy: process.env.SERVE_PROXY_TARGET,
        port: process.env.SERVE_PORT || 8080,
        open: false,
    });
    
    // Watch compiled file and template changes and do a full refresh
    gulp.watch('public/**/*').on('change', browserSync.reload);
    gulp.watch('modules/**/*.blade.php').on('change', browserSync.reload);
    
    gulp.start(['webpack'], callback)
})

gulp.task('clean', function (cb) {
    del(['public/assets'], cb)
})