const path = require('path');
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');

module.exports = {
    entry: {
        tracker: './assets/src/js/tracker.js',
        admin: './assets/src/js/admin.js'
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'assets/dist/js'),
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /\/node_modules\//,
                use: {
                    loader: 'babel-loader'
                }
           },
           {
             test: /\.css$/,
             exclude: /\/node_modules\//,
             use: [
               'style-loader',
               'css-loader',
             ],
           },
        ],
   },
    plugins: [
        new MomentLocalesPlugin(), // ditch all moment.js locales except EN
    ]
};