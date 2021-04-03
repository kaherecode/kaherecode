const searchModal = document.getElementById('searchModal')

document.querySelectorAll('.toggleSearch').forEach((item) => {
  item.addEventListener('click', (e) => {
    e.preventDefault()

    if (
      searchModal.style.display === 'none' ||
      searchModal.style.display === ''
    ) {
      searchModal.style.display = 'flex'
      document.body.classList.add('search-modal-open')
    } else {
      closeSearchModal()
    }
  })
})

document.addEventListener('keyup', (e) => {
  if (e.key.toLowerCase() === 'escape') {
    closeSearchModal()
  }
})

searchModal.addEventListener('click', (e) => {
  if (!e.target.closest('input' || e.target.closest('div'))) {
    closeSearchModal()
  }
})

const closeSearchModal = () => {
  searchModal.style.display = 'none'
  document.body.classList.remove('search-modal-open')
}
