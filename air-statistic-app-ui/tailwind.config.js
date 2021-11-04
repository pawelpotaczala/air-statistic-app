const colors = require('tailwindcss/colors')

module.exports = {
  prefix: '',
  purge: {
    content: [
      './src/**/*.{html,ts}',
    ]
  },
  darkMode: 'class', // or 'media' or 'class'
  variants: {
    extend: {},
  },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
  theme: {
    colors: {
      transparent: 'transparent',
      current: 'currentColor',
      black: colors.black,
      white: colors.white,
      gray: '#F3F4F6',
      indigo: colors.indigo,
      red: '#FCA5A5',
      yellow: '#FDE047',
      blue: '#12CCE5',
      purple: '#A855F7',
      green: '#86EFAC',
      'green:200': '#E6F4F1',
    }
  }
};
