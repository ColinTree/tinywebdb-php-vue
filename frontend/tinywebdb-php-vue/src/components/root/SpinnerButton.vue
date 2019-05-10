<template>
  <div>
    <b-button
        :variant="variant"
        :disabled="submiting || disabled"
        @click="onClick">
      <b-spinner v-if="submiting && spinnerPosition === 'inside-left'" small :variant="spinnerVariant" :type="spinnerType" />
      <slot v-if="!submiting || keepTextWhenSpin" />
      <b-spinner v-if="submiting && spinnerPosition === 'inside-right'" small :variant="spinnerVariant" :type="spinnerType" />
    </b-button>
    <b-spinner v-if="submiting && spinnerPosition === 'outside-right'" small :variant="spinnerVariant" :type="spinnerType" />
  </div>
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
      default: 'inside-left',
      validator: function (value) {
        return [ 'inside-left', 'inside-right', 'outside-right' ].indexOf(value) >= 0
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
      this.toggle()
      this.$emit('click', () => this.toggle(false))
    }
  }
}
</script>

<style>

</style>
