const commentFormOverlay = document.querySelector('#commentForm')
const commentForm = document.querySelector('#commentForm > .comment-form')

document.querySelectorAll('.open-comment-form').forEach((item) => {
  item.addEventListener('click', (event) => {
    event.preventDefault()

    if (
      commentForm.style.display === 'none' ||
      commentForm.style.display === ''
    ) {
      commentForm.style.display = 'block'
    }
  })
})

document
  .querySelector('#closeCommentForm')
  .addEventListener('click', (event) => {
    event.preventDefault()

    if (commentForm.style.display === 'block') {
      commentForm.style.display = 'none'
    }
  })
