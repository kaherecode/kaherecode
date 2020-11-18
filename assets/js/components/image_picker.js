const childrenMatches = function (elem, selector) {
  return Array.prototype.filter.call(elem.children, function (child) {
    return child.matches(selector)
  })
}

document.querySelectorAll('.image-picker').forEach((item) => {
  const input = childrenMatches(item, 'input[type=file]')[0]
  const placeholder = childrenMatches(item, 'span')[0]
  const img = childrenMatches(item, 'img')[0]

  if (
    img.getAttribute('src') === '' ||
    typeof img.getAttribute('src') === 'undefined'
  ) {
    img.style.display = 'none'
  } else {
    placeholder.style.display = 'none'
  }

  input.addEventListener('change', (event) => {
    img.setAttribute('src', URL.createObjectURL(event.target.files[0]))
    img.style.display = 'block'
    placeholder.style.display = 'none'
  })
})
