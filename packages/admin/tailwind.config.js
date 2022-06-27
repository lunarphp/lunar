module.exports = {
  content: [
    "./resources/assets/**/*.js",
    "./resources/views/**/*.blade.php",
    "./resources/views/**/**/*.blade.php",
    "./vendor/livewire-ui/modal/resources/views/**/*.blade.php",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/**/*.blade.php",
  ],
  theme: {
    fontFamily: {
      sans: ["Nunito", "ui-sans-serif", "system-ui"],
      serif: ["ui-serif", "Georgia"],
      mono: ["ui-monospace", "SFMono-Regular"],
    },
    extend: {
      zIndex: {
        75: 75,
      },
      cursor: {
        grab: "grab",
      },
    },
  },
  variants: {
    extend: {
      opacity: ["disabled"],
      cursor: ["disabled"],
      backgroundColor: ["even"],
    },
  },
  plugins: [
    require("@tailwindcss/forms")({
      strategy: "class",
    }),
  ],
};
