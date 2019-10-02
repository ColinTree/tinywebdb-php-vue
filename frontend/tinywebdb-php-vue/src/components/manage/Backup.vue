<template>
  <BaseCard>
    <template #header>备份/恢复</template>
    <div>
      <span style="font-size:15.5px"><b>数据导出</b></span>
      <hr />
      <b-form @submit.prevent>
        <b-form-group label="导出类型">
          <b-select v-model="export_.type" :options="[ 'JSON', 'XML', 'CSV', 'xlsx' ]" />
        </b-form-group>
        <b-form-group label="导出前缀">
          <b-input v-model="export_.prefix" />
        </b-form-group>
        <b-form-group>
          <b-checkbox v-model="export_.includeReserved">包含保留的标签</b-checkbox>
        </b-form-group>
        <b-button variant="primary" type="submit" :href="exportUrl" target="_blank">导出</b-button>
      </b-form>
    </div>
    <br />
    <div>
      <span style="font-size:15.5px"><b>数据导入</b></span>
      <hr />
      <b-form @submit.prevent="$refs.importJson.onClick()">
        <b-form-group label="导入数据（JSON）">
          <b-file
              v-model="importJson.file"
              :state="importJson.state"
              accept=".json"
              browse-text="浏览"
              placeholder="请选择要导入的JSON文件"
              drop-placeholder="请将文件拖至此处" />
        </b-form-group>
        <SpinnerButton ref="importJson" variant="primary" @click="onImportJson">导入</SpinnerButton>
      </b-form>
    </div>
  </BaseCard>
</template>

<script>
import qs from 'qs'

export default {
  name: 'ManageBackup',
  data () {
    return {
      export_: {
        type: 'JSON',
        prefix: '',
        includeReserved: false
      },
      importJson: {
        file: null,
        state: null
      }
    }
  },
  computed: {
    exportUrl () {
      return this.$parent.service.defaults.baseURL + 'export?' + qs.stringify({
        type: this.export_.type,
        prefix: this.export_.prefix,
        include_reserved: this.export_.includeReserved,
        token: this.$parent.token
      })
    }
  },
  methods: {
    async onImportJson (onDone) {
      if (this.importJson.file === null) {
        this.$root.showInfo('请选择一个文件')
        onDone()
        return
      }
      let formData = new FormData()
      formData.append('file', this.importJson.file)
      let { status, result } = (await this.$parent.service.post('import_json', formData,
        { headers: { 'Content-Type': 'multipart/form-data' } })).data
      onDone()
      switch (status) {
        case 0: {
          if (result.failed && result.failed.length > 0) {
            this.$root.showInfo(`以下标签导入失败：\`${result.failed.join('`，`')}\``)
          } else {
            this.$root.showInfo('所有标签都导入成功啦')
          }
          break
        }
        default: {
          this.$root.showInfo(`上传失败，错误码：${status}`)
        }
      }
    }
  }
}
</script>

<style>

</style>
