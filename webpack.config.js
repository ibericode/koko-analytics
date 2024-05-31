const path = require('path')
const CopyPlugin = require("copy-webpack-plugin");

module.exports = {
  entry: {
    dashboard: './assets/src/js/dashboard.js',
    'dashboard-widget': './assets/src/js/dashboard-widget.js',
    script: './assets/src/js/script.js',
    sw: './assets/src/js/sw.js',
    'koko-analytics-script-test': './assets/src/js/koko-analytics-script-test.js'
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'assets/dist/js')
  },
  plugins: [
      new CopyPlugin({
          patterns: [
            { from: './assets/src/img', to: path.resolve(__dirname, './assets/dist/img') },
            { from: './assets/src/css', to: path.resolve(__dirname, './assets/dist/css') },
            { from: './assets/src/manifest.json', to: path.resolve(__dirname, './assets/dist/manifest.json') }
          ],
      }),
  ],
}
