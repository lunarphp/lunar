const defaultTheme = require("tailwindcss/defaultTheme");

module.exports = {
  content: [
    "./resources/assets/**/*.js",
    "./resources/views/**/*.blade.php",
    "./resources/views/**/**/*.blade.php",
    "./vendor/livewire-ui/modal/resources/views/**/*.blade.php",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/**/*.blade.php",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ["Nunito", ...defaultTheme.fontFamily.sans],
      },
      zIndex: {
        75: 75,
      },
    },
  },
  plugins: [
    require("@tailwindcss/forms")({
      strategy: "class",
    }),
  ],
};
