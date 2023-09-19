/* eslint-env node */
const path = require('path')
const CopyPlugin = require('copy-webpack-plugin')
const lightningcss = require('lightningcss')

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
                                "@babel/preset-react",
                                '@babel/preset-env'
                            ]
                        }
                    }
            },
            {
                test: /\.css$/i,
                use: [
                    'style-loader',
                    'css-loader',
                ]
            }
                ]
    },
    externals: {
        moment: 'moment',
        '@wordpress/i18n': 'wp.i18n',
        react: 'React',
        'react-dom': 'ReactDOM',
    },
    plugins: [
        new CopyPlugin({
            patterns: [
              { from: './assets/src/img', to: path.resolve(__dirname, './assets/dist/img') },
              { from: './assets/src/css', to: path.resolve(__dirname, './assets/dist/css'), transform: (content, path) => {
                  const { code } = lightningcss.transform({
                    filename: path.split('/').pop(),
                    code: Buffer.from(content),
                    minify: true,
                    sourceMap: false
                  })
                  return code
                } }
            ]
        })
    ],

}
