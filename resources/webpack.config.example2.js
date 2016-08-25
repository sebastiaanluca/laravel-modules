/**
 * Webpack entry point, loading default settings. Can be adjusted as desired.
 */

const webpack = require('webpack')
const _ = require('lodash')

// Our base webpack configuration file
const config = require('./vendor/nwidart/laravel-modules/scripts/webpack.config')

// Exclude non-JS modules from being used in vendors.js
const excludedVendors = [
    'font-awesome',
]

config.entry.vendors = _.pullAll(config.entry.vendors, excludedVendors);

// Provide global support for vendor libraries
config.plugins.push(new webpack.ProvidePlugin({
    $: 'jquery',
    jQuery: 'jquery',
}))

config.plugins.push(new webpack.ProvidePlugin({
    _: 'lodash',
}))

module.exports = config