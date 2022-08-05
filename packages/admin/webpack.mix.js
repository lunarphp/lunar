const mix = require('laravel-mix');

const fs = require('fs')

mix.options({
  terser: {
    extractComments: false,
  }
});

mix.postCss("resources/assets/hub.css", "public/app.css", [
  require("tailwindcss"),
]);

mix.js("resources/assets/body.js", "public/body.js");

mix.js("resources/assets/head.js", "public/head.js");
