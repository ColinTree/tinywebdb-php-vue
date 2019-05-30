<template>
  <b-button
      :variant="variant"
      :disabled="submiting || disabled"
      @click="onClick">
    <b-spinner v-if="submiting && spinnerPosition === 'left'" small :variant="spinnerVariant" :type="spinnerType" />
    <slot v-if="!submiting || keepTextWhenSpin" />
    <b-spinner v-if="submiting && spinnerPosition === 'right'" small :variant="spinnerVariant" :type="spinnerType" />
  </b-button>
</template>

<script>
export default {
  name: 'SpinnerButton',
  model: {
    prop: 'submiting'
  },
  props: {
    spinnerPosition: {
      type: String,
      default: 'left',
      validator: function (value) {
        return [ 'left', 'right' ].indexOf(value) >= 0
      }
    },
    spinnerVariant: {
      type: String,
      default: undefined
    },
    spinnerType: {
      type: String,
      default: 'border'
    },
    keepText: {
      type: Boolean,
      default: true
    },
    disabled: {
      type: Boolean,
      default: false
    },
    variant: {
      type: String,
      default: 'primary'
    }
  },
  watch: {
    keepText (val) {
      this.keepTextWhenSpin = val
    }
  },
  data () {
    return {
      submiting: false,
      keepTextWhenSpin: true
    }
  },
  methods: {
    toggle (to) {
      this.submiting = (to !== undefined && typeof to === 'boolean') ? to : (!this.submiting)
    },
    onClick () {
      this.toggle(true)
      this.$emit('click', /* onDone = */ () => this.toggle(false))
    }
  }
}
</script>

<style>

</style>
