const webpackConfig = require('@nextcloud/webpack-vue-config')
const path = require('path')

webpackConfig.entry = {
    'kursumstufung-main': './src/main.js',
}

webpackConfig.output = {
    path: path.join(__dirname, 'js'),
    filename: '[name].js',
}

module.exports = webpackConfig
