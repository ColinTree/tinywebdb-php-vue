<template>
  <b-modal
      :id="id"
      :title="title"
      :ok-title="okTitle"
      :ok-variant="okVariant"
      :cancel-title="cancelTitle"
      @ok="callback(true)"
      @hidden="callback(false)"
      centered lazy>
    <pre v-text="content" />
  </b-modal>
</template>

<script>
export default {
  name: 'ConfirmModal',
  props: {
    id: String,
    okTitle: {
      type: String,
      default: '确认'
    },
    okVariant: {
      type: String,
      default: 'danger'
    },
    cancelTitle: {
      type: String,
      default: '不了'
    }
  },
  data () {
    return {
      title: '',
      content: '',
      callback: result => {}
    }
  },
  methods: {
    show (title, content, callback) {
      this.title = title
      this.content = content
      this.callback = callback
      this.$children[0].show()
    }
  }
}
</script>
