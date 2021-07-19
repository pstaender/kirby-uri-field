/* global panel */

import App from './App.vue'
import Preview from './Preview.vue'

panel.plugin('pstaender/uri-field', {
  fields: {
    uri: App
  },
  components: {
    'k-link-field-preview': Preview
  }
})
