const path = require('path');
const DEBUG =  process.env.NODE_ENV === 'development';

module.exports = {
    mode: DEBUG? 'development' : 'production',
    entry: {
        script: './assets/src/js/script.js',
        admin: './assets/src/js/admin.js'
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'assets/dist/js'),
    },
    devtool: DEBUG ? 'inline-source-map' : false,
    module: {
        rules: [
            {
                test: /\.js$/i,
                exclude: /\/node_modules\//,
                use: {
                    loader: 'babel-loader'
                }
           },
           {
             test: /\.css$/i,
             use: [
               'style-loader',
               'css-loader',
             ],
           },
        ],
   },
    externals: {
        moment: 'moment'
    }
};