<template>
  <BaseCard>
    <template slot="header">设置</template>

    <div v-if="loaded === true">
      <div class="setting-header">标签浏览页·分类列表</div>
      <b-form @submit.prevent="$refs.all_category_button.onClick()">
        <b-form-group>
          <b-input v-model="all_category.value" />
          <template slot="description">
            使用井号#分隔每一项（重复项会被隐藏）<br>
            例如：默认显示全部，可选显示“student_”或者“teacher_”开头的标签，则应当这么填：
            <b-link @click="all_category.value = '#student_#teacher_'">#student_#teacher_（点击预览）</b-link>
            <br>
            例如：默认显示“student_”，可选显示全部和开头为“teacher_”的标签，则应当这么填：
            <b-link @click="all_category.value = 'student_##teacher_'">student_##teacher_（点击预览）</b-link>
          </template>
        </b-form-group>
        <b-form-group label="预览">
          <b-select
              :value="all_category_preview[0].value"
              :options="all_category_preview"
              :select-size="Math.min(5, all_category_preview.length)" />
        </b-form-group>
        <SpinnerButton
            ref="all_category_button"
            :variant="typeof all_category.variant === 'string' ? all_category.variant : 'secondary'"
            @click="onDone => save('all_category', onDone)">
          <span v-text="typeof all_category.text === 'string' ? all_category.text : '保存'"/>
        </SpinnerButton>
      </b-form>

      <hr>

      <div class="setting-header">数据安全</div>
      <b-form @submit.prevent="$refs.allow_browser_button.onClick()">
        <b-form-group description="说明：本功能对于数据防修改能力较弱，仅做简单的浏览器级别防御，请不要过度依赖">
          <b-checkbox v-model="allow_browser.value">允许来自浏览器的读写</b-checkbox>
        </b-form-group>
        <SpinnerButton
            ref="allow_browser_button"
            :variant="typeof allow_browser.variant === 'string' ? allow_browser.variant : 'secondary'"
            @click="onDone => save('allow_browser', onDone)">
          <span v-text="typeof allow_browser.text === 'string' ? allow_browser.text : '保存'"/>
        </SpinnerButton>
      </b-form>

      <hr>

      <div class="setting-header">特殊标签</div>
      <b-form @submit.prevent="$refs.special_tags_button.onClick()">
        <b-form-group label="返回总标签数量" :state="special_count_state" invalid-feedback="不能包含#，空格">
          <b-input v-model="special_tags.value.count" placeholder="special_count" :state="special_count_state" />
          <template slot="description">
            使用方法：请求获取标签“<span v-text="special_count" />[#计数前缀]”。如果不包含前缀，默认为空<br />
            返回：一个文本形式的数字<br />
            例子：
            <ul>
              <li>统计所有标签数量：“<span v-text="special_count" />”</li>
              <li>统计所有以a开头的标签的数量：“<span v-text="special_count" />#a”</li>
            </ul>
          </template>
        </b-form-group>
        <b-form-group label="获取指定前缀的所有标签和值" :state="special_getall_state" invalid-feedback="不能包含，空格，空格">
          <b-input v-model="special_tags.value.getall" placeholder="special_getall" :state="special_getall_state" />
          <template slot="description">
            使用方法：请求获取标签“<span v-text="special_getall" />#获取前缀[#起始偏移][#数量限制]”。
            <b>***如数据库内的数据数量较大，请慎重使用***</b><br />
            <ul>
              <li>如果不包含前缀，默认为空</li>
              <li>如果不包含起始偏移，默认为0</li>
              <li>如果不包含数量限制，默认为无限制</li>
            </ul>
            返回：一个json编码的文本，格式如<code>[ 结果列表 ]</code>，每个列表项为<code>{ key: '标签', value: '值' }</code><br />
            例子：
            <ul>
              <li>获取所有标签：“<span v-text="special_getall" />”</li>
              <li>获取所有以a开头的标签：“<span v-text="special_getall" />#a”</li>
              <li>获取所有标签中的前100个：“<span v-text="special_getall" />#a#0#100”</li>
              <li>获取所有标签中的第101-150个：“<span v-text="special_getall" />#a#100#50”</li>
            </ul>
          </template>
        </b-form-group>
        <b-form-group label="一次性获取多个标签的值" :state="special_listget_state" invalid-feedback="不能包含，空格">
          <b-input v-model="special_tags.value.listget" placeholder="special_listget" :state="special_listget_state" />
          <template slot="description">
            使用方法：请求获取标签“<span v-text="special_listget" />[#标签1][#标签2][……]”<br />
            返回：一个json编码的文本，格式如<code>[ 结果列表 ]</code>，每个列表项为<code>{ key: '标签', value: '值' }</code><br />
            例子：获取"id_2455_pwd"和"id_2455_money"：“<span v-text="special_listget" />#id_2455_pwd#id_2455_money”
          </template>
        </b-form-group>
        <b-form-group label="搜索数据库的标签和值" :state="special_search_state" invalid-feedback="不能包含，空格">
          <b-input v-model="special_tags.value.search" placeholder="special_search" :state="special_search_state" />
          <template slot="description">
            使用方法：请求获取标签“<span v-text="special_search" />#搜索关键词”<br />
            返回：一个json编码的文本，格式如<code>{ "count": 总共多少项, "result": [ 搜索结果列表 ] }</code>，其中每个列表项为<code>{ key: '标签', value: '值' }</code><br />
            例子：搜索"john"：“<span v-text="special_search" />#john”
          </template>
        </b-form-group>
        <SpinnerButton
            ref="special_tags_button"
            :disabled="special_count_state === false || special_getall_state === false || special_listget_state === false || special_search_state === false"
            :variant="typeof special_tags.variant === 'string' ? special_tags.variant : 'secondary'"
            @click="onDone => save('special_tags', onDone)">
          <span v-text="typeof special_tags.text === 'string' ? special_tags.text : '保存'"/>
        </SpinnerButton>
      </b-form>

      <hr>

      <div class="setting-header">危险选项</div>
      <div>
        <b-form-group>
          <SpinnerButton @click="eraseData" variant="danger">清除数据库</SpinnerButton>
          <span style="margin-left:10px" class="vertical-auto-margin text-danger">执行清空数据库，设置将被保留</span>
        </b-form-group>
        <b-form-group>
          <SpinnerButton @click="erasePwd" variant="danger">清除登录密码</SpinnerButton>
          <span style="margin-left:10px" class="vertical-auto-margin text-danger">清除设置将会重置已有密码，请及时设置新密码</span>
        </b-form-group>
        <b-form-group>
          <SpinnerButton @click="eraseAll" variant="danger">清除数据库和所有设置</SpinnerButton>
          <span style="margin-left:10px" class="vertical-auto-margin text-danger">完全重置TPV</span>
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
      all_category: { value: '', variant: undefined, text: undefined },
      allow_browser: { value: true, variant: undefined, text: undefined },
      special_tags: {
        value: { count: '', getall: '', listget: '', search: '' },
        variant: undefined,
        text: undefined
      }
    }
  },
  computed: {
    all_category_preview () {
      return Array.from(new Set(this.all_category.value.split('#')))
        .map(item => ({value: item, text: item.length === 0 ? '显示所有' : `前缀\`${item}\``}))
    },
    special_count   () { return this.special_tags.value.count || 'special_count' },
    special_getall  () { return this.special_tags.value.getall || 'special_getall' },
    special_listget () { return this.special_tags.value.listget || 'special_listget' },
    special_search  () { return this.special_tags.value.search || 'special_search' },
    special_count_state   () { return this.vaidSpecialTag(this.special_count) },
    special_getall_state  () { return this.vaidSpecialTag(this.special_getall) },
    special_listget_state () { return this.vaidSpecialTag(this.special_listget) },
    special_search_state  () { return this.vaidSpecialTag(this.special_search) }
  },
  mounted () {
    this.loadSettings()
  },
  methods: {
    vaidSpecialTag (val) {
      if (val.indexOf('#') >= 0 || val.indexOf(' ') >= 0) {
        return false
      }
      return null
    },
    async loadSettings () {
      this.loaded = false
      let { status, result } = (await this.$parent.service.get('settings')).data
      switch (status) {
        case 0: {
          this.all_category.value = result.all_category || ''
          this.allow_browser.value = result.allow_browser !== 'false'
          if (result.special_tags) {
            result.special_tags = JSON.parse(result.special_tags)
            this.special_tags.value.count = typeof result.special_tags.count === 'string' ? result.special_tags.count : ''
            this.special_tags.value.getall = typeof result.special_tags.getall === 'string' ? result.special_tags.getall : ''
            this.special_tags.value.listget = typeof result.special_tags.listget === 'string' ? result.special_tags.listget : ''
            this.special_tags.value.search = typeof result.special_tags.search === 'string' ? result.special_tags.search : ''
          }
          this.loaded = true
          break
        }
        default: {
          this.loaded = '拉取设置失败'
        }
      }
    },
    async save (settingId, onDone = () => {}) {
      let value = (this[settingId] || {}).value
      if (value === undefined || value === null) {
        this.$root.showInfo('设置内容为undefined或null，请刷新页面后重试。如反复出现请联系作者排查问题')
        return
      }
      value = Array.isArray(value) || typeof value === 'object' ? JSON.stringify(value) : value
      let { status } = (await this.$parent.service.post('setting_update', { settingId, value })).data
      onDone()
      switch (status) {
        case 0: {
          this[settingId].text = '保存成功'
          this[settingId].variant = 'success'
          await this.$root.sleep(1000)
          this[settingId].text = undefined
          this[settingId].variant = undefined
          break
        }
        default: {
          this.$root.showInfo(`保存设置'${settingId}'失败，错误码${status}`)
        }
      }
    },

    eraseData (onDone) {
      this.$root.showConfirm('', '确认要清空数据库吗？该操作无法逆转！请提前做好数据备份', async () => {
        let { status } = (await this.$parent.service.post('erase_data')).data
        switch (status) {
          case 0: {
            this.$root.showInfo('数据已清空')
            break
          }
          default: {
            this.$root.showInfo(`重置密码失败，错误码${status}`)
          }
        }
        onDone()
      }, onDone)
    },
    erasePwd (onDone) {
      this.$root.showConfirm('', '确认要清除登录密码？', async () => {
        let { status } = (await this.$parent.service.post('erase_pwd')).data
        switch (status) {
          case 0: {
            this.$parent.token = null
            this.$router.push('/manage/init')
            this.$root.showInfo('系统密码已重置，请重新设置新的密码')
            break
          }
          default: {
            this.$root.showInfo(`重置密码失败，错误码${status}`)
          }
        }
        onDone()
      }, onDone)
    },
    eraseAll (onDone) {
      this.$root.showConfirm('', '确认要清空数据库并且清除所有设置（包括后台密码）吗？该操作无法逆转！请提前做好数据备份', async () => {
        let { status } = (await this.$parent.service.post('erase_all')).data
        switch (status) {
          case 0: {
            this.$parent.token = null
            this.$router.push('/manage/init')
            this.$root.showInfo('数据库和所有设置已清除')
            break
          }
          default: {
            this.$root.showInfo(`清除失败，错误码${status}`)
          }
        }
        onDone()
      }, onDone)
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
