if (
  localStorage.kaherecode_theme === 'dark' ||
  (!('kaherecode_theme' in localStorage) &&
    window.matchMedia('(prefers-color-scheme: dark)').matches)
) {
  document.documentElement.classList.add('dark')
} else {
  document.documentElement.classList.remove('dark')
}

const darkModeSwitcher = document
  .querySelectorAll('.dark-mode-switcher > svg')
  .forEach((item) => {
    item.addEventListener('click', (event) => {
      if (!('kaherecode_theme' in localStorage)) {
        localStorage.kaherecode_theme = 'dark'
        document.documentElement.classList.add('dark')
        updateSvgColor('dark')
      } else {
        if (localStorage.kaherecode_theme === 'dark') {
          localStorage.kaherecode_theme = 'light'
          document.documentElement.classList.remove('dark')
          updateSvgColor('light')
        } else {
          localStorage.kaherecode_theme = 'dark'
          document.documentElement.classList.add('dark')
          updateSvgColor('dark')
        }
      }
    })
  })

const updateSvgColor = (mode) => {
  document.querySelectorAll('.dark-mode-switcher > svg').forEach((item) => {
    if (mode === 'dark') {
      item.classList.remove('text-gray-400')
      item.classList.add('text-red-400')
    } else {
      item.classList.remove('text-red-400')
      item.classList.add('text-gray-400')
    }
  })
}
