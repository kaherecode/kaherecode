import EditorJS from '@editorjs/editorjs'
import Header from '@editorjs/header'

const editor = new EditorJS({
  holder: 'editor',
  tools: {
    header: {
      class: Header,
      shortcut: 'CMD+SHIFT+H',
      config: {
        levels: [2, 3, 4, 5, 6],
        defaultLevel: 2,
      },
    },
  },
})
