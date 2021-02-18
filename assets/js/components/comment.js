import marked from 'marked'

marked.setOptions({
  highlight: function (code, language) {
    const prism = require('../prism')
    if (prism.languages[language]) {
      return prism.highlight(code, prism.languages[language], language)
    } else {
      return code
    }
  },
})
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

const textarea = document.querySelector('#commentForm > .comment-form textarea')
const togglePreview = document.querySelector(
  '#commentForm > .comment-form #togglePreview'
)
const output = document.querySelector('#commentForm > .comment-form #output')
const htmlContent = document.querySelector(
  '#commentForm > .comment-form #htmlContent'
)
if (output) {
  output.innerHTML = marked(textarea.innerHTML)
}

if (textarea) {
  textarea.addEventListener('input', (event) => {
    let markedHTML = marked(event.target.value)
    output.innerHTML = markedHTML
    htmlContent.value = markedHTML
  })
}

if (togglePreview) {
  togglePreview.addEventListener('click', (event) => {
    event.preventDefault()

    if (textarea.style.display !== 'none') {
      textarea.style.display = 'none'
      output.style.display = 'block'
    } else {
      textarea.style.display = 'block'
      output.style.display = 'none'
    }
  })
}
