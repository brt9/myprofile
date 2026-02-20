/** @type {import('tailwindcss').Config} */
module.exports = {
  presets: [require('./vendor/tallstackui/tallstackui/tailwind.config.js')],
  content: [
    './resources/views/**/*.blade.php',
    './storage/framework/views/*.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/tallstackui/tallstackui/src/**/*.php',
  ],
  theme: { extend: {} },
  plugins: [require('@tailwindcss/forms')],
}
