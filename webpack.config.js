'use strict'

const path = require('path')
const CopyPlugin = require('copy-webpack-plugin')
const Terser = require('terser')

module.exports = {
  watchOptions: {
    aggregateTimeout: 300,
    poll: 1000,
    ignored: ['node_modules']
  },
  entry: {
    admin: './assets/src/js/admin.js',
    'dashboard-widget': './assets/src/js/dashboard-widget.js'
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
              ['@babel/preset-env', {
                targets: '> 0.2%, last 2 versions, not dead'
              }]
            ],
            plugins: [
              [
                '@babel/plugin-transform-react-jsx',
                {
                  pragma: 'h'
                }
              ]
            ]
          }
        }
      },
      {
        test: /\.s?[ca]ss$/i,
        use: [
          'style-loader',
          'css-loader',
          'sass-loader'
        ]
      }
    ]
  },
  externals: {
    moment: 'moment'
  },
  plugins: [
    new CopyPlugin([
      { from: './assets/src/js/script.js', to: path.resolve(__dirname, './assets/dist/js/script.js'), transform: content => Terser.minify(content.toString()).code },
      { from: './assets/src/img', to: path.resolve(__dirname, './assets/dist/img') }
    ])
  ]
}
