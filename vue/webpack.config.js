/**
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 */
const path = require('path');
const webpack = require('webpack');
const { VueLoaderPlugin } = require('vue-loader')

module.exports = {
    mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
    entry: "./src/main.js",
    devtool : 'source-map',
    output: {
        path: path.resolve(__dirname, "../amd/build"),
        filename: 'management.min.js',
        chunkFilename: "[id].management.min.js?v=[hash]",
        libraryTarget: 'amd',
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    'vue-style-loader',
                    'css-loader'
                ],
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader',
            },
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node-modules/,
            },
        ]
    },
    watchOptions: {
        ignored: /node_modules/
    },
    performance: {
        hints: false
    },
    externals: {
        /*'core/ajax': {
            amd: 'core/ajax'
        },*/
        'core/str': {
            amd: 'core/str'
        },
        /*'core/modal_factory': {
            amd: 'core/modal_factory'
        },
        'core/modal_events': {
            amd: 'core/modal_events'
        },
        'core/fragment': {
            amd: 'core/fragment'
        },
        'core/yui': {
            amd: 'core/yui'
        },
        'core/localstorage': {
            amd: 'core/localstorage'
        },
        'core/notification': {
            amd: 'core/notification'
        },
        'jquery': {
            amd: 'jquery'
        }*/
    },
    performance: {
        hints: false
    },
    plugins: [
        new VueLoaderPlugin(),
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__ : true,
            __VUE_PROD_DEVTOOLS__ : false,
        })
    ],
};