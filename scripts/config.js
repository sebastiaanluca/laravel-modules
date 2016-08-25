/**
 * Our main application configuration variables
 */

module.exports = {
    context: 'default', // default|watch|hot|production
    app: {
        name: process.env.APP_NAME,
        url: process.env.APP_URL,
        description: process.env.APP_NAME,
        developer: '-',
        developerUrl: process.env.SERVE_PROXY_TARGET,
    },
    
    // INFO: see https://github.com/spatie-custom/blender-gulp/blob/master/config/index.js
    
    // TODO: linting for JS, CSS, and SASS
    //     lint: {
    //         js: ['resources/assets/js/**/*.js'],
    //         sass: [
    //             'resources/assets/sass/front/**/*.scss',
    //             '!resources/assets/sass/front/utility/*.scss',
    //             '!resources/assets/sass/front/vendor-custom/*.scss',
    //         ],
    //     },
};