/**
 * The gulp tasks to run webpack or the webpack hot-reload server
 */

const gulp = require('gulp');

const spawn = require('./spawn');
const config = require('./config');

gulp.task('webpack', callback => {
    if (config.context === 'production') {
        process.env.NODE_ENV = 'production';
    }
    
    process.env.WEBPACK_CONTEXT = config.context;
    
    if (config.context === 'hot') {
        runWebpackDevServer(callback);
        
        return;
    }
    
    const options = [];
    
    if (config.context === 'watch') {
        options.push('-w');
    }
    
    spawn('node_modules/.bin/webpack', options, callback);
});

const runWebpackDevServer = callback => {
    const options = [
        // The inline option does not work well with HTTPS enabled
        // '--inline',
        '--hot',
    ];
    
    spawn('node_modules/.bin/webpack-dev-server', options, callback);
};