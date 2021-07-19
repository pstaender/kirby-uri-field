<template>
  <k-field v-bind="$props" class="k-link-field">
    <LinkSelect
      v-if="isMainScreen"
      v-model="link"
      :width="width"
      :options="options"
      :endpoints="endpoints"
      @input="emitInput"
    ></LinkSelect>
  </k-field>
</template>

<script>
import LinkSelect from './LinkSelect.vue'

export default {
  components: {
    LinkSelect
  },
  props: {
    value: {
      type: Object,
      default: function () {
        // If the field is inside a Structure, it'll have an `undefined` initial
        // value, so this gives a valid default value.
        return {
          type: 'url',
          value: undefined
        }
      }
    },
    endpoints: Object,

    width: String,
    label: String,
    help: String,
    disabled: Boolean,
    required: Boolean,

    options: Array,
  },
  data: function () {
    return {
      data: this.value,
      screen: 'link'
    }
  },
  computed: {
    link: {
      get: function () {
        return {
          type: this.data.type,
          value: this.data.value
        }
      },
      set: function (input) {
        Object.assign(this.data, input)
      }
    },
    isMainScreen: function () {
      return this.screen === 'link'
    },
  },
  methods: {
    emitInput: function () {
      this.$emit('input', this.data)
    },
    switchScreen: function () {
      this.screen = this.isMainScreen ? 'options' : 'link'
    }
  },
  watch: {
    value: function (value) {
      this.data = Object.assign({}, value)
    }
  }
}
</script>
