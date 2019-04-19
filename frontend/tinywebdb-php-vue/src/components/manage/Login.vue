<template>
  <BaseCard>
    <template slot="header" v-text="'登录后台'" />
    <b-form @submit.prevent="onLogin">
      <b-input-group prepend="后台密码" class="mt-2">
        <b-input type="password" v-model="pwd" autocomplete="current-password" />
        <b-input-group-append>
          <b-button variant="primary" type="submit">登录</b-button>
        </b-input-group-append>
      </b-input-group>
    </b-form>
  </BaseCard>
</template>

<script>
export default {
  name: 'ManageLogin',
  data () {
    return {
      pwd: ''
    }
  },
  methods: {
    async onLogin () {
      try {
        let result = await this.$parent.service.post('login', { pwd: this.pwd })
        if (result.data.state === 0) {
          if (result.data.result.succeed === true) {
            this.$parent.token = result.data.result.token
            this.$router.push('/manage/all')
          } else {
            this.$root.showInfo('登录失败', '密码错误')
          }
        } else {
          this.$root.showInfo('登录失败', '错误码' + result.data.state)
        }
      } catch (e) {
        console.error(e)
        this.$root.showInfo('登录失败', '错误信息见console')
      }
    }
  }
}
</script>
