const toggleIcon = (mode) => {
  const suns = document.querySelectorAll('.dark-mode-switcher > svg.sun-icon')
  const moons = document.querySelectorAll('.dark-mode-switcher > svg.moon-icon')

  if (mode === 'dark') {
    suns.forEach((item) => item.classList.remove('hidden'))
    moons.forEach((item) => item.classList.add('hidden'))
  } else {
    moons.forEach((item) => item.classList.remove('hidden'))
    suns.forEach((item) => item.classList.add('hidden'))
  }
}

if (
  localStorage.kaherecode_theme === 'dark' ||
  (!('kaherecode_theme' in localStorage) &&
    window.matchMedia('(prefers-color-scheme: dark)').matches)
) {
  document.documentElement.classList.add('dark')
  toggleIcon('dark')
} else {
  document.documentElement.classList.remove('dark')
  toggleIcon('light')
}

const darkModeSwitcher = document
  .querySelectorAll('.dark-mode-switcher > svg')
  .forEach((item) => {
    item.addEventListener('click', (event) => {
      if (!('kaherecode_theme' in localStorage)) {
        localStorage.kaherecode_theme = 'dark'
        document.documentElement.classList.add('dark')
        toggleIcon('dark')
      } else {
        if (localStorage.kaherecode_theme === 'dark') {
          localStorage.kaherecode_theme = 'light'
          document.documentElement.classList.remove('dark')
          toggleIcon('light')
        } else {
          localStorage.kaherecode_theme = 'dark'
          document.documentElement.classList.add('dark')
          toggleIcon('dark')
        }
      }
    })
  })
