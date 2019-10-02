<template>
  <b-form @submit.prevent="$refs.submit_button.onClick()">
    <div class="setting-header">
      <span v-text="config.header" />
      <small style="color:lightgray" v-text="'（由插件' + config.plugin + '提供）'" />
    </div>
    <b-form-group
        v-for="(childConfig, childName) in formGroups"
        :key="childName"
        :state="state(childName)"
        :valid-feedback="childConfig['valid-feedback'] || null"
        :invalid-feedback="childConfig['invalid-feedback'] || null">

      <template #label>
        <span v-text="childConfig.label || ''" />
        <b-link
            v-if="childConfig['disable-via-value'] === true"
            v-text="value[childName] === 'disabled' ? '启用' : '禁用'"
            @click="value[childName] === 'disabled' ? (value[childName] = '') : (value[childName] = 'disabled')" />
      </template>

      <!-- TYPE: checkbox -->
      <b-checkbox
          v-if="childConfig.type === 'checkbox'"
          v-model="value[childName]"
          :disabled="childConfig['disable-via-value'] === true && value[childName] === 'disabled'">
        <span v-text="childConfig['checkbox-label'] || ''" />
      </b-checkbox>

      <!-- TYPE: input -->
      <b-input
          v-else-if="childConfig.type === 'input'"
          v-model="value[childName]"
          :state="state(childName)"
          :placeholder="childConfig.placeholder || ''"
          :disabled="childConfig['disable-via-value'] === true && value[childName] === 'disabled'" />

      <template #description>
        <div
            v-show="childConfig['disable-via-value'] !== true || value[childName] !== 'disabled'"
            v-html="replaceAll(childConfig.description || '', '{{value}}', value[childName] || childConfig.placeholder || '')" />
      </template>
    </b-form-group>
    <SpinnerButton
        ref="submit_button"
        :variant="typeof submit_button.variant === 'string' ? submit_button.variant : 'secondary'"
        @click="onSubmit">
      <span v-text="typeof submit_button.text === 'string' ? submit_button.text : '保存'"/>
    </SpinnerButton>
  </b-form>
</template>

<script>
export default {
  name: 'SettingGroup',
  props: {
    id: {
      type: String,
      required: true
    },
    config: {
      type: Object,
      default: () => ({})
    }
  },
  data () {
    return {
      value: {},
      submit_button: {
        variant: undefined,
        text: undefined
      }
    }
  },
  computed: {
    formGroups () {
      if (this.config.type === 'group') {
        return this.config.children || {}
      } else {
        let rtn = {}
        rtn[this.id] = this.config
        return rtn
      }
    }
  },
  created () {
    let value = {}
    for (let name in this.formGroups) {
      value[name] = ''
    }
    this.value = value
  },
  methods: {
    /**
     * should be called by parent
     */
    setValue (val) {
      let keys = Object.keys(this.value)
      if (keys.length === 1) {
        this.value[keys[0]] = val || (this.formGroups[keys[0]].default)
      } else {
        val = JSON.parse(val || '{}')
        for (let name in this.value) {
          this.value[name] = name in val ? val[name] : (this.formGroups[name].default)
        }
      }
    },

    state (name) {
      if (this.formGroups[name].validator) {
        return (typeof this.value[name] === 'string' && this.value[name].match(new RegExp(this.formGroups[name].validator)))
          ? ('valid-feedback' in this.formGroups[name] ? true : null)
          : ('invalid-feedback' in this.formGroups[name] ? false : null)
      } else {
        return null
      }
    },

    onSubmit (onDone) {
      let val = this.value
      if (Object.keys(val).length === 1) {
        val = val[Object.keys(val)[0]]
      }
      this.$emit('submit', this.id, val, this.submit_button, onDone)
    },

    replaceAll (text, search, replacing) {
      if (typeof text !== 'string' ||
          typeof search !== 'string' ||
          typeof replacing !== 'string' ||
          replacing.includes(search)) {
        return text
      }
      let text_ = text
      do {
        text = text_
        text_ = text.replace(search, replacing)
      } while (text_ !== text)
      return text
    }
  }
}
</script>

<style>
.setting-header {
  font-weight: bold;
  margin-bottom: 10px;
}
</style>
