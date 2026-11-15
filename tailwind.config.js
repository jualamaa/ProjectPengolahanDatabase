/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class', // <--- INI KUNCI UTAMA UNTUK TOGGLE DARK/LIGHT MODE
  content: [
    "./*.php",         // Membaca semua file PHP di folder utama (index.php, login.php, dll)
    "./src/**/*.js"    // Membaca file JavaScript di dalam folder src
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}