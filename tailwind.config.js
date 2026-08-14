/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        bg: {
          primary: '#FAFAFA',
          secondary: '#F4F4F5',
          tertiary: '#E4E4E7',
          accent: '#EEF2FF',
          card: '#FFFFFF',
        },
        brand: {
          50: '#EEF2FF',
          100: '#E0E7FF',
          200: '#C7D2FE',
          300: '#A5B4FC',
          400: '#818CF8',
          500: '#6366F1',
          600: '#4F46E5',
          700: '#4338CA',
          800: '#3730A3',
          900: '#312E81',
        },
        text: {
          primary: '#18181B',
          secondary: '#71717A',
          muted: '#A1A1AA',
        },
        border: {
          DEFAULT: '#E4E4E7',
          light: '#F4F4F5',
        }
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        heading: ['Rubik', 'sans-serif'],
        mono: ['JetBrains Mono', 'monospace'],
      },
    },
  },
  plugins: [],
}
