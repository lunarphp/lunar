const preset = require('./vendor/filament/filament/tailwind.config.preset')

module.exports = {
    presets: [preset],
    content: [
        './packages/admin/resources/views/**/*.blade.php',
    ],
}
