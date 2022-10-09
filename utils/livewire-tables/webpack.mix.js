let mix = require('laravel-mix')

mix
  .postCss('resources/css/app.css', 'livewire-tables/app.css', [
    require('tailwindcss'),
  ])
  .setPublicPath('dist')
  .version()
