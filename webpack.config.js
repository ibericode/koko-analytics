const path = require('path');

module.exports = {
    entry: {
        tracker: './assets/src/js/tracker.js',
        admin: './assets/src/js/admin.js'
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'assets/dist/js'),
    },
};