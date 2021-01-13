const childrenMatches = function (elem, selector) {
  return Array.prototype.filter.call(elem.children, function (child) {
    return child.matches(selector)
  })
}

document.querySelectorAll('.image-picker').forEach((item) => {
  const input = childrenMatches(item, 'input[type=file]')[0]
  const placeholder = childrenMatches(item, 'span')[0]
  let img = childrenMatches(item, 'img')[0]

  if (
    img &&
    (img.getAttribute('src') === '' ||
      typeof img.getAttribute('src') === 'undefined')
  ) {
    img.style.display = 'none'
  } else {
    if (placeholder) {
      placeholder.style.display = 'block'
    }
  }

  input.addEventListener('change', (event) => {
    if (!img) {
      img = document.createElement('img')
      img.className = 'object-cover object-center w-full h-full'
      item.appendChild(img)
    }
    img.setAttribute('src', URL.createObjectURL(event.target.files[0]))
    img.style.display = 'block'
    placeholder.style.display = 'none'
  })
})
