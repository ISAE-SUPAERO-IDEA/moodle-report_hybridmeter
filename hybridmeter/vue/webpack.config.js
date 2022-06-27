const path = require('path');
const { VueLoaderPlugin } = require('vue-loader')

module.exports = {
    entry: "./main.js",
    output: {
        path: path.resolve(__dirname, "../amd/build"),
        filename: 'test.min.js',
        chunkFilename: "[id].test.min.js?v=[hash]",
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
        'core/ajax': {
            amd: 'core/ajax'
        },
        'core/str': {
            amd: 'core/str'
        },
        'core/modal_factory': {
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
        }
    },
    performance: {
        hints: false
    },
    plugins: [
        new VueLoaderPlugin()
    ],
};