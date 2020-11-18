import marked from 'marked'

const textarea = document.querySelector('#editor > textarea')
const output = document.querySelector('#editor > #output')
output.innerHTML = marked('# Marked in the browser\n\nRendered by **marked**.')

textarea.addEventListener('input', (event) => {
  output.innerHTML = marked(event.target.value)
})
