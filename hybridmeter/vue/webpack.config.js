/*
 * This file is part of Moodle - http://moodle.org/
 *
 *  Moodle is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Moodle is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 */

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