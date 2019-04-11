<template>
  <b-modal
      :id="id"
      :title="title"
      @hidden="onHidden"
      ok-only centered lazy>
    <pre v-text="content" />
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
      content: ''
    }
  },
  methods: {
    show (title, content) {
      this.title = title
      this.content = content
      this.$children[0].show()
    },
    onHidden () {
      this.title = ''
      this.content = ''
    }
  }
}
</script>
