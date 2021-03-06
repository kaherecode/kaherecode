module.exports = {
  darkMode: 'class',
  future: {
    removeDeprecatedGapUtilities: true,
    purgeLayersByDefault: true,
  },
  purge: {
    enabled: true,
    content: ['./templates/**/*.html.twig', './templates/**/*.html'],
  },
  theme: {
    extend: {
      colors: {
        darkGray: '#393939',
        darkerGray: '#323232',
        lightGray: '#f9f9f9',
      },
    },
  },
  variants: {},
  plugins: [],
}
