let mix = require('laravel-mix');

mix.js('resources/js/opayo.js', 'dist').setPublicPath('dist').version();