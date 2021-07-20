<template>
  <k-grid>
    <k-column v-if="showSelect" :width="uiWidth.select">
      <k-select-field
        type="select"
        v-model="data.type"
        :options="types"
        :empty="false"
        @input="inputType"
      />
    </k-column>

    <k-column :width="uiWidth.field">
      <k-url-field
        v-if="data.type === 'url'"
        v-model="data.value"
        placeholder="https://example.com/"
      />

      <k-pages-field
        v-else-if="data.type === 'page'"
        v-model="data.value"
        :search="true"
        :endpoints="{
          field: this.endpoints.field + '/link-pages'
        }"
      ></k-pages-field>

      <k-files-field
        v-else-if="data.type === 'file'"
        v-model="data.value"
        :search="true"
        :endpoints="{
          field: this.endpoints.field + '/link-files'
        }"
      ></k-files-field>

      <k-box
        v-else
        theme="negative"
        :text="$t('error.type', { type: data.type })"
      />
    </k-column>
  </k-grid>
</template>

<script>
export default {
  props: {
    value: Object,
    options: Array,
    endpoints: Object,
    width: String
  },
  previousStoredUrl: null,
  data: function() {
    return {
      data: this.value,
      updating: false
    }
  },
  computed: {
    showSelect: function() {
      return this.types.length > 1
    },
    widthPercent: function() {
      var split = this.width.split('/')
      var numerator = split[0] || 1
      var denominator = split[1] || 1
      return (numerator / denominator) * 100
    },
    uiWidth: function() {
      var large = this.widthPercent >= 50

      return {
        select: large ? '1/4' : '1/1',
        field: this.showSelect ? (large ? '3/4' : '1/1') : null
      }
    },
    types: function() {
      return this.options.map(
        function(type) {
          return {
            value: type,
            text: this.$t(type)
          }
        }.bind(this)
      )
    }
  },
  methods: {
    inputType: function() {
      if (this.data.value) {
        if (
          this.data.value[0] &&
          this.data.value[0].id &&
          this.data.type === 'url'
        ) {
          this.data.value = this.data.value[0].id
        } else {
          this.data.value = undefined
        }
      }
    }
  },
  watch: {
    data: {
      deep: true,
      handler: function(value) {
        if (!this.updating) {
          this.$emit('input', value)
        }
      }
    },
    value: function(value) {
      this.updating = true

      Object.assign(this.data, value)

      this.$nextTick(
        function() {
          this.updating = false
        }.bind(this)
      )
    }
  }
}
</script>

<style lang="scss" scoped>
// .k-field-header {
//   display: none; // hides the Select buttons
// }
</style>
