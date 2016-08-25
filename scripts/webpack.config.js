/**
 * The base webpack configuration
 */

const webpack = require('webpack')
const path = require('path')
const autoprefixer = require('autoprefixer')
const WebpackCleanupPlugin = require('webpack-cleanup-plugin')
const ManifestPlugin = require('webpack-manifest-plugin')
const ExtractTextPlugin = require('extract-text-webpack-plugin')
var SingleModuleInstancePlugin = require('single-module-instance-webpack-plugin');

// Specific Laravel module implementation for resources
const Modules = require('./modules')

const context = (context) => process.env.WEBPACK_CONTEXT === context
const target = 'public/assets'

const extractStyles = new ExtractTextPlugin(context('production') ? 'styles/[name]-[hash].css' : 'styles/[name].css');

const config = {
    
    debug: ! context('production'),
    
    // Sourcemaps, etc.
    devtool: context('production') ? false : 'eval',
    
    // Context is an absolute path to the directory where webpack will be
    // looking for our entry points
    context: path.resolve(process.cwd(), 'modules'),
    
    entry: {
        // Currently not loading packages directly from what we pull in, because
        // webpack doesn't load all modules they contain. We have to explicitly
        // require them in our files.
        // vendors: Object.keys(pkg.dependencies),
        vendors: [],
    },
    
    // Our entry points, with a unique name as key and a relative path 
    // (starting from `context`) as value
    output: {
        // An absolute path to the desired output directory
        path: path.resolve(process.cwd(), target),
        
        // A filename pattern for the output files. This would create 
        // `global.js` and `portfolio.js`
        filename: 'scripts/' + (context('production') ? '[name]-[hash].js' : '[name].js'),
        
        // A filename pattern for generated chunks
        chunkFilename: 'scripts/' + (context('production') ? '[name]-[chunkhash].js' : '[name].js'),
        
        // Used by modules to define the root path of the assets
        publicPath: '/assets/',
    },
    
    // An array of extensions webpack should try to resolve in `require`, 
    // `import`, etc. statements
    resolve: {
        // Resolve modules from these directories. Allows to use 
        // vendor/module instead of referencing relatively (../../../)
        modulesDirectories: ['node_modules', 'modules'],
        extensions: ['', '.js', '.css', '.scss'],
    },
    
    module: {
        loaders: [
            
            // Build browser-safe JavaScript files from ES2015 modules
            {
                test: /\.jsx?$/i,
                // Don't (re)compile vendor JS files
                exclude: /(node_modules|bower_components)/,
                // 'babel-loader' is also a legal name to reference
                loader: 'babel',
                query: {
                    presets: ['es2015'],
                    plugins: ['transform-strict-mode'],
                }
            },
            
            {
                test: /\.vue$/,
                loader: 'vue',
            },
            
            // Compile SASS to CSS with sourcemaps enabled
            {
                test: /\.s?css$/i,
                loader: extractStyles.extract(['css?sourceMap!postcss!sass?sourceMap']),
            },
            
            // Extract fonts from stylesheets, optimize, and copy to public assets directory
            {
                test: /\.woff(\?v=\d+\.\d+\.\d+)?$/,
                loader: 'url?limit=10000&mimetype=application/font-woff&name=./fonts/[name]/[hash].[ext]'
            },
            {
                test: /\.woff2(\?v=\d+\.\d+\.\d+)?$/,
                loader: 'url?limit=10000&mimetype=application/font-woff&name=fonts/[name]/[hash].[ext]'
            },
            {
                test: /\.ttf(\?v=\d+\.\d+\.\d+)?$/,
                loader: 'url?limit=10000&mimetype=application/octet-stream&name=fonts/[name]/[hash].[ext]'
            },
            {
                test: /\.eot(\?v=\d+\.\d+\.\d+)?$/,
                loader: 'file?&name=fonts/[name]/[hash].[ext]'
            },
            {
                test: /\.svg(\?v=\d+\.\d+\.\d+)?$/,
                loader: 'url?limit=10000&mimetype=image/svg+xml&name=fonts/[name]/[hash].[ext]'
            },
            
            // Watch for changes in blade files
            {
                test: /\.blade.php|\.html/i,
                exclude: /(node_modules|bower_components)/,
            },
        ],
    },
    
    plugins: [
        new ManifestPlugin({fileName: 'rev-manifest.json'}),
        
        // Extract styles from scripts
        extractStyles,
        
        // This plugin looks for similar chunks and files and only
        // includes them once (and provides copies when used)
        new webpack.optimize.DedupePlugin(),
        
        // Required modules should be singletons (i.e. always
        // include the same instances) instead of copied modules
        new SingleModuleInstancePlugin(),
        
        // Split vendor from app resources
        new webpack.optimize.CommonsChunkPlugin({
            // Move dependencies to our vendor file
            name: 'vendors',
            // Look for common dependencies in all children,
            children: true,
            // How many times a dependency must come up before being extracted
            minChunks: Infinity,
        }),
        
        new webpack.NormalModuleReplacementPlugin(/\.(jpe?g|png|gif|svg)$/, 'node-noop'),
        
        function () {
            this.plugin('watch-run', function (watching, callback) {
                console.log('Begin compile at ' + new Date())
                callback()
            })
        },
    ],
    
    sassLoader: {
        // An array of paths that LibSass can look in to attempt to resolve your @import declarations
        includePaths: [
            path.resolve(process.cwd(), 'modules'),
            path.resolve(process.cwd(), 'node_modules'),
        ],
    },
    
    postcss() {
        return [autoprefixer]
    },
}

// Merge app-specific source modules into our source files
config.entry = Object.assign(config.entry, Modules())

// Don't run when watching or serving content to prevent errors
if (! context('watch') && ! context('hot')) {
    config.plugins = config.plugins.concat([
        new WebpackCleanupPlugin({
            exclude: ['.gitignore']
        })
    ])
}

// Optimize order and uglify JS in production
if (context('production')) {
    config.plugins = config.plugins.concat([
        new webpack.DefinePlugin({
            'process.env': {
                'NODE_ENV': 'production',
            },
        }),
        
        // This plugins optimizes chunks and modules by
        // how much they are used in your app
        new webpack.optimize.OccurenceOrderPlugin(),
        
        // This plugin prevents Webpack from creating chunks
        // that would be too small to be worth loading separately
        new webpack.optimize.MinChunkSizePlugin({
            // ~50kb
            minChunkSize: 51200,
        }),
        
        new webpack.optimize.UglifyJsPlugin({
            compress: {
                // Suppress uglification warnings
                warnings: false,
            },
            mangle: true,
            screw_ie8: true,
        }),
    ])
}

// https://github.com/kentcdodds/webpack-dev-server-issue/tree/master/node_modules/webpack-dev-server/node_modules/http-proxy
config.devServer = {
    port: process.env.SERVE_PORT || 8080,
    
    // Where to serve the bundled assets from
    publicPath: process.env.SERVE_PROXY_TARGET + '/assets',
    
    // Enable instant-reload and establish a secure connection
    hot: true,
    https: true,
    
    proxy: {
        '*': {
            target: process.env.SERVE_PROXY_TARGET,
            changeOrigin: true,
            autoRewrite: true,
            xfwd: true,
        },
    },
    
    watchOptions: {
        aggregateTimeout: 20,
        poll: 1000
    },
};

module.exports = config