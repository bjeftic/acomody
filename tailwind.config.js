/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./node_modules/flowbite/**/*.js",
    "./node_modules/flowbite-vue/**/*.{js,jsx,ts,tsx,vue}",
  ],

  darkMode: 'class',

  theme: {
    extend: {

      // Typography
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },

      fontSize: {
        'xs':   ['0.75rem',  { lineHeight: '1rem' }],
        'sm':   ['0.875rem', { lineHeight: '1.25rem' }],
        'base': ['1rem',     { lineHeight: '1.5rem' }],
        'lg':   ['1.125rem', { lineHeight: '1.75rem' }],
        'xl':   ['1.25rem',  { lineHeight: '1.75rem' }],
        '2xl':  ['1.5rem',   { lineHeight: '2rem' }],
        '3xl':  ['1.875rem', { lineHeight: '2.25rem' }],
        '4xl':  ['2.25rem',  { lineHeight: '2.5rem' }],
        '5xl':  ['3rem',     { lineHeight: '1.1' }],
      },

      colors: {
        white: "#ffffff",
        "white-20": "rgba(255,255,255,0.2)",
        "white-60": "rgba(255,255,255,0.6)",

        // Neutral grays
        neutral: {
          50:  '#FAFAFA',
          100: '#F5F5F5',
          200: '#E5E5E5',
          300: '#D4D4D4',
          400: '#A3A3A3',
          500: '#737373',
          600: '#525252',
          700: '#404040',
          800: '#262626',
          900: '#171717',
        },

        // ─── PRIMARY ──────────────────────────────────────────
        // Coral-Red — CTAs, buttons, links, badges
        // Anchor: #F05035 (warmer/more orange than Airbnb's pink #FF5A5F)
        primary: {
          50:  '#FFF4F2',
          100: '#FFE6E2',
          200: '#FFCDC5',
          300: '#FFA898',
          400: '#FF7D68',
          500: '#F05035',
          600: '#D93D24',
          700: '#B52D19',
          800: '#8F2113',
          900: '#62160C',
          950: '#380B06',
          DEFAULT: '#F05035',
        },

        // ─── SURFACE ──────────────────────────────────────────
        // Dark surfaces for dark mode
        surface: {
          DEFAULT: '#1A0A08',
          light:   '#2E130E',
        },
      },

      // Gradients
      backgroundImage: {
        'brand-gradient': 'linear-gradient(135deg, #F05035 0%, #FF7D68 100%)',
        'hero-gradient':  'linear-gradient(135deg, #1A0A08 0%, #F05035 100%)',
      },

      // Shadows
      boxShadow: {
        'card':       '0 1px 3px rgba(0,0,0,0.08), 0 1px 2px -1px rgba(0,0,0,0.06)',
        'card-hover': '0 6px 20px rgba(0,0,0,0.12)',
        'dropdown':   '0 10px 40px rgba(0,0,0,0.15)',
      },

      // Radius
      borderRadius: {
        'xl':  '0.75rem',
        '2xl': '1rem',
        '3xl': '1.5rem',
      },

      // Transitions
      transitionTimingFunction: {
        DEFAULT: 'cubic-bezier(0.4, 0, 0.2, 1)',
      },
    },
  },

  plugins: [
    require('flowbite/plugin')
  ],
}
