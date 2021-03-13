document.querySelectorAll('.bookmark-tutorial').forEach((bookmark) => {
  bookmark.addEventListener('click', async (event) => {
    const href = bookmark.getAttribute('data-href')

    if (href) {
      const res = await fetch(href)

      if (res.status === 200) {
        const fillAttr = bookmark.children[0].getAttribute('fill')

        if (fillAttr !== 'none') {
          bookmark.children[0].setAttribute('fill', 'none')
        } else {
          bookmark.children[0].setAttribute('fill', '#374151')
        }
      }
    }
  })
})
