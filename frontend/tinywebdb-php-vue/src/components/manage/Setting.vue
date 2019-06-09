<template>
  <BaseCard>
    <template slot="header">设置</template>

    <div v-if="loaded === true">
      <div style="font-weight:bold; margin-bottom:10px" disabled>标签浏览页·分类列表</div>
      <div>
        <b-form-group>
          <b-input v-model="setting.all_category" />
          <template slot="description">
            使用井号#分隔每一项（重复项会被隐藏）<br>
            例如：默认显示全部，可选显示“student_”或者“teacher_”开头的标签，则应当这么填：
            <b-link @click="setting.all_category = '#student_#teacher_'">#student_#teacher_（点击预览）</b-link>
            <br>
            例如：默认显示“student_”，可选显示全部和开头为“teacher_”的标签，则应当这么填：
            <b-link @click="setting.all_category = 'student_##teacher_'">student_##teacher_（点击预览）</b-link>
          </template>
        </b-form-group>
        <b-form-group label="预览">
          <b-select
              :value="all_category_preview[0].value"
              :options="all_category_preview"
              :select-size="Math.min(5, all_category_preview.length)" />
        </b-form-group>
        <SpinnerButton
            :variant="typeof buttonVariant.all_category === 'string' ? buttonVariant.all_category : 'secondary'"
          @click="updateCategory">
          <span v-text="typeof buttonText.all_category === 'string' ? buttonText.all_category : '保存'"/>
        </SpinnerButton>
      </div>

      <hr>

      <div style="font-weight:bold; margin-bottom:10px" disabled>危险选项</div>
      <div>
        <b-form-group>
          <b-input-group>
            <SpinnerButton @click="deleteData" variant="danger">清除数据库</SpinnerButton>
            <span style="margin:auto 10px" class="text-danger">执行清空数据库，设置将被保留</span>
          </b-input-group>
        </b-form-group>
        <b-form-group>
          <b-input-group>
            <SpinnerButton @click="deletePwd" variant="danger">清除登录密码</SpinnerButton>
            <span style="margin:auto 10px" class="text-danger">清除设置将会重置已有密码，请及时设置新密码</span>
          </b-input-group>
        </b-form-group>
        <b-form-group>
          <SpinnerButton @click="deleteAll" variant="danger">清除数据库和所有设置</SpinnerButton>
        </b-form-group>
      </div>
    </div>
    <div v-else>
      <div v-if="loaded === false">
        <b-spinner />
        设置加载中
      </div>
      <div v-else>
        <span v-text="loaded" />
        <b-button @click="loadSettings">重试</b-button>
      </div>
    </div>
  </BaseCard>
</template>

<script>
export default {
  name: 'ManageSetting',
  data () {
    return {
      loaded: false,
      setting: {
        all_category: ''
      },
      buttonVariant: {
        all_category: undefined
      },
      buttonText: {
        all_category: undefined
      }
    }
  },
  computed: {
    all_category_preview () {
      return Array.from(new Set(this.setting.all_category.split('#')))
        .map(item => ({value: item, text: item.length === 0 ? '显示所有' : '前缀：' + item}))
    }
  },
  mounted () {
    this.loadSettings()
  },
  methods: {
    async loadSettings () {
      this.loaded = false
      try {
        let { status, result } = (await this.$parent.service.get('settings')).data
        if (status === 0) {
          if (result.hasOwnProperty('all_category')) this.setting.all_category = result.all_category
          this.loaded = true
          return
        }
      } catch (e) {
        console.error(e)
      }
      this.loaded = '拉取设置失败'
    },
    async save (settingId, value) {
      try {
        let { status } = (await this.$parent.service.post('setting_update', { settingId, value })).data
        if (status !== 0) {
          throw new Error(`保存设置'${settingId}'失败，错误码${status}`)
        }
        return true
      } catch (e) {
        if (e.handled !== true) {
          console.error(e)
          this.$root.showInfo('', `${settingId}保存失败，具体错误信息见console`)
        }
        return false
      }
    },

    async updateCategory (onDone) {
      let result = await this.save('all_category', this.setting.all_category)
      onDone()
      this.buttonText.all_category = result ? '保存成功' : '保存失败'
      this.buttonVariant.all_category = result ? 'success' : 'danger'
      await this.$root.sleep(1000)
      this.buttonText.all_category = undefined
      this.buttonVariant.all_category = undefined
    },
    deleteData (onDone) {
    },
    deletePwd (onDone) {
      this.$root.showConfirm('', '确认要清除登录密码？', async () => {
        await this.$parent.service.post('deletepwd')
        this.$parent.token = null
        this.$router.push('/manage/init')
        this.$root.showToast('系统密码已重置，请重新设置新的密码')
        onDone()
      }, onDone)
    },
    deleteAll (onDone) {
    }
  }
}
</script>

<style>

</style>
