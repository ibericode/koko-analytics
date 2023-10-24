/* eslint-env node */
const path = require('path')
const CopyPlugin = require("copy-webpack-plugin");

module.exports = {
  entry: {
    admin: './assets/src/js/admin.js',
    'dashboard-widget': './assets/src/js/dashboard-widget.js',
    script: './assets/src/js/script.js',
    'koko-analytics-script-test': './assets/src/js/koko-analytics-script-test.js'
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'assets/dist/js')
  },
  module: {
    rules: [
      {
        test: /\.js$/i,
        exclude: [/\/node_modules\//],
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              '@babel/preset-env'
            ]
          }
        }
      }
    ]
  },
  externals: {
    '@wordpress/i18n': 'wp.i18n',
  },
    plugins: [
        new CopyPlugin({
            patterns: [
              { from: './assets/src/img', to: path.resolve(__dirname, './assets/dist/img') },
              { from: './assets/src/css', to: path.resolve(__dirname, './assets/dist/css') }
            ],
        }),
    ],
}
