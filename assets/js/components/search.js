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

const searchInput = document.querySelector('#searchInput')
const resultsPreview = document.querySelector('#searchResults #results')
const seeAllResults = document.querySelector('#searchResults #seeAllResults')
const noResults = document.querySelector('#searchResults #noResults')

const searchURL = searchInput.getAttribute('data-search-url')

const renderSearchResult = ({ url, title, publishedAt, description }) => {
  return `
    <a href="${url}" class="block py-3 border-b hover:bg-gray-100 px-2">
      <div class="flex justify-between items-center">
        <h5 class="font-semibold">${title}</h5>
        <small class="text-xs">${publishedAt}</small>
      </div>
      <p class="text-sm hidden md:block">
        ${description}
      </p>
    </a>
  `
}

searchInput.addEventListener('keyup', (e) => {
  const query = e.target.value.trim().replace(/\s{2,}/g, ' ')

  if (query !== '') {
    fetch(`${searchURL}?q=${query}`, {
      headers: {
        Accept: 'application/json',
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length > 0) {
          resultsPreview.innerHTML = ''
          noResults.style.display = 'none'
          resultsPreview.style.display = 'block'
          seeAllResults.style.display = 'block'

          document
            .querySelector('#seeAllResults a')
            .setAttribute('href', `${searchURL}?q=${query}`)

          data.forEach((result) => {
            resultsPreview.insertAdjacentHTML(
              'beforeend',
              renderSearchResult(result)
            )
          })
        } else {
          resultsPreview.innerHTML = ''
          resultsPreview.style.display = 'none'
          seeAllResults.style.display = 'none'
          noResults.style.display = 'block'
        }
      })
  } else {
    resultsPreview.innerHTML = ''
    resultsPreview.style.display = 'none'
    seeAllResults.style.display = 'none'
    noResults.style.display = 'none'
  }
})
