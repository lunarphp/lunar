const mix = require("laravel-mix");

mix.setPublicPath("public");
mix.options({
  terser: {
    extractComments: false,
  }
});

mix.postCss("resources/assets/hub.css", "public/app.css", [
  require("tailwindcss"),
]);
mix.js("resources/assets/hub.js", "public/app.js");

if (mix.inProduction()) {
  mix.version();
}
