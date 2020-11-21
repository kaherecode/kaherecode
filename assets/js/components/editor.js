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

const textarea = document.querySelector('#editor > textarea')
const output = document.querySelector('#editor > #output')
output.innerHTML = marked('# Contenu de l\'article.')

textarea.addEventListener('input', (event) => {
  output.innerHTML = marked(event.target.value)
})
