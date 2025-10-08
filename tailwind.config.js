/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./node_modules/flowbite/**/*.js",
    "./node_modules/flowbite-vue/**/*.{js,jsx,ts,tsx,vue}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
      colors: {
        white: "#ffffff",
        "white-20": "rgba(255, 255, 255, 0.2)",
        "white-60": "rgba(255, 255, 255, 0.6)",
      }
    },
  },
  darkMode: 'class',
  plugins: [
    require('flowbite/plugin')
  ],
}

