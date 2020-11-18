function toggleVisibility(id) {
  let e = document.getElementById(id)

  if (e.style.display === 'none' || e.style.display === '') {
    e.style.display = 'block'
  } else {
    e.style.display = 'none'
  }
}

// add click event listener on toggle-item elements
document.querySelectorAll('.toggle-item').forEach((item) => {
  item.addEventListener('click', (event) => {
    toggleVisibility(item.getAttribute('data-target'))
  })
})
