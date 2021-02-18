const commentFormOverlay = document.querySelector('#commentForm')
const commentForm = document.querySelector('#commentForm > .comment-form')

const openComments = document.querySelectorAll('.open-comment-form')
const closeComment = document.querySelector('#closeCommentForm')

if (openComments) {
  openComments.forEach((item) => {
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
}

if (closeComment) {
  closeComment.addEventListener('click', (event) => {
    event.preventDefault()

    if (commentForm.style.display === 'block') {
      commentForm.style.display = 'none'
    }
  })
}
