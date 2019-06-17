<template>
  <b-modal
      :id="id"
      :title="title"
      :ok-title="okTitle"
      :ok-variant="okVariant"
      :cancel-title="cancelTitle"
      @ok="okCallback"
      @cancel="cancelCallback"
      no-close-on-backdrop
      no-close-on-esc
      hide-header-close
      centered lazy>
    <pre v-text="content" style="white-space:pre-wrap" />
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
      okCallback: () => {},
      cancelCallback: () => {}
    }
  },
  methods: {
    show (title, content, okCallback = () => {}, cancelCallback = () => {}) {
      this.title = title
      this.content = content
      this.okCallback = okCallback
      this.cancelCallback = cancelCallback
      this.$children[0].show()
    }
  }
}
</script>
