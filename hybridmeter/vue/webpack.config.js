const path = require('path');
const { VueLoaderPlugin } = require('vue-loader')

module.exports = {
    mode: 'development',
    entry: "./main.js",
    output: {
        path: path.resolve(__dirname, "../assets/test/build"),
        publicPath: "localhost/moodle/report/hybridmeter/assets/test/build",
        filename: 'test.min.js',
    },
    module: {
        rules: [
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
    plugins: [
        new VueLoaderPlugin()
    ],
};