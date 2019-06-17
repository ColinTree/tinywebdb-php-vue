<template>
  <b-modal
      :id="id"
      :title="title"
      @shown="onShown"
      @hidden="onHidden"
      centered lazy>
    <pre v-text="content" style="white-space:pre-wrap" />
    <template slot="modal-footer">
      <b-button ref="ok" variant="primary" @click="onOk">好的</b-button>
    </template>
  </b-modal>
</template>

<script>
export default {
  name: 'InfoModal',
  props: {
    id: String
  },
  data () {
    return {
      title: '',
      content: '',
      hiddenCallback: null
    }
  },
  methods: {
    show (title, content, hiddenCallback = null) {
      this.title = title
      this.content = content
      this.hiddenCallback = hiddenCallback
      this.$children[0].show()
    },
    async onShown () {
      await this.$root.sleep(100)
      this.$refs.ok.focus()
    },
    onHidden () {
      this.title = ''
      this.content = ''
      if (typeof this.hiddenCallback === 'function') {
        this.hiddenCallback()
      }
    },
    onOk () {
      this.$children[0].hide()
    }
  }
}
</script>
