<template>
  <div :style="computedStyle" />
</template>

<script>
export default {
  name: 'TextWidthTester',
  data () {
    return {
      defaultStyle: {
        position: 'absolute',
        visibility: 'hidden',
        height: 'auto',
        width: 'auto',
        'white-space': 'nowrap'
      }
    }
  },
  props: {
    fontSize: {
      type: Number,
      default: 16
    },
    propStyle: Object
  },
  computed: {
    computedStyle () {
      return {
        ...this.defaultStyle,
        ...this.propStyle,
        'font-size': this.fontSize
      }
    }
  },
  methods: {
    test (text) {
      this.$el.innerText = text
      let width = this.$el.clientWidth
      this.$el.innerText = ''
      return width
    }
  }
}
</script>
