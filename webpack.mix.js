const mix = require('laravel-mix');
var path = require('path');


/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .react()
    .sass('resources/sass/app.scss', 'public/css');

      mix.webpackConfig({
        resolve: {
            alias: {
                '@assets': path.resolve(__dirname, 'resources/js/assets/'),
                'auth': path.resolve(__dirname, 'resources/js/auth/'),
                'components': path.resolve(__dirname, 'resources/js/components/'),
                'configs': path.resolve(__dirname, 'resources/js/configs/'),
                'constants': path.resolve(__dirname, 'resources/js/constants/'),
                'lang': path.resolve(__dirname, 'resources/js/lang/'),
                'layouts': path.resolve(__dirname, 'resources/js/layouts/'),
                '@redux': path.resolve(__dirname, 'resources/js/redux/'),
                'services': path.resolve(__dirname, 'resources/js/services/'),
                'utils': path.resolve(__dirname, 'resources/js/utils/'),
                'views': path.resolve(__dirname, 'resources/js/views/'),
            }
        }
    });

    //   mix.alias( "Components", 'resources/js/components');
    //   mix.alias('@', '/resources/js/components');