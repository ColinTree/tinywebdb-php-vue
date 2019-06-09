<template>
  <BaseCard>
    <template slot="header" v-text="'登录后台'" />
    <b-form @submit.prevent="$refs.login_button.onClick()">
      <b-form-group :state="state" :valid-feedback="feedback" :invalid-feedback="feedback">
        <b-input-group prepend="后台密码" class="mt-2">
          <b-input type="password" :state="state" v-model="pwd" autocomplete="current-password" @input="state = null" />
          <b-input-group-append>
            <SpinnerButton ref="login_button" @click="onLogin" variant="primary">登录</SpinnerButton>
          </b-input-group-append>
        </b-input-group>
      </b-form-group>
    </b-form>
  </BaseCard>
</template>

<script>
export default {
  name: 'ManageLogin',
  data () {
    return {
      pwd: '',
      state: null,
      feedback: null
    }
  },
  methods: {
    async onLogin (onDone) {
      let { status, result } = (await this.$parent.service.post('login', { pwd: this.pwd })).data
      switch (status) {
        case 0: {
          if (result.succeed === true) {
            this.$parent.token = result.token
            this.state = true
            this.feedback = '登录成功，将于5秒后为您跳转。此次登录将于1小时后过期，您也可以选择提前登出'
            onDone()
            await this.$root.sleep(5000)
            if (this.$route.path === '/manage/login') {
              this.$router.push('/manage/all')
            }
            return
          } else {
            this.state = false
            this.feedback = '密码错误'
          }
          break
        }
        default: {
          this.state = false
          this.feedback = `登录失败，错误码${status}`
        }
      }
      onDone()
    }
  }
}
</script>
