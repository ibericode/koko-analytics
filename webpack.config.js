const path = require('path');

module.exports = {
    mode: process.env.NODE_ENV === 'development' ? 'development' : 'production',
    entry: {
        tracker: './assets/src/js/tracker.js',
        admin: './assets/src/js/admin.js'
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'assets/dist/js'),
    },
    devtool: process.env.NODE_ENV === 'development' ? 'inline-source-map' : false,
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
             use: [
               'style-loader',
               'css-loader',
             ],
           },
        ],
   }
};