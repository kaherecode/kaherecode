import marked from 'marked'

marked.setOptions({
  highlight: function (code, language) {
    const prism = require('./prism')
    if (prism.languages[language]) {
      return prism.highlight(code, prism.languages[language], language)
    } else {
      return code
    }
  },
})

const textarea = document.querySelector('#editor > textarea')
const output = document.querySelector('#editor > #output')
const htmlContent = document.querySelector('#editor > #htmlContent')
output.innerHTML = marked(textarea.innerHTML)

textarea.addEventListener('input', (event) => {
  let markedHTML = marked(event.target.value)
  output.innerHTML = markedHTML
  htmlContent.value = markedHTML
})
