const mix = require('laravel-mix');
const path = require('path');

mix.js('resources/src/main.js', 'public/js/main.min.js')
    .js('resources/src/login.js', 'public/js/login.min.js')
    .vue({ version: 2 })
    .sass('resources/src/styles/app.scss', 'public/css/app.min.css')
    .alias({ '@': path.resolve(__dirname, 'resources/src') })
    .webpackConfig({
        output: { chunkFilename: 'js/chunks/[name].[contenthash].js', publicPath: '/' },
    })
    .options({ processCssUrls: false })
    .disableNotifications();

if (mix.inProduction()) {
    mix.version();
}
