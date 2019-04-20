<template>
  <BaseCard>
    <template slot="header" v-text="'登录后台'" />
    <b-form @submit.prevent="onLogin">
      <b-form-group :state="state" :valid-feedback="feedback" :invalid-feedback="feedback">
        <b-input-group prepend="后台密码" class="mt-2">
          <b-input type="password" :state="state" v-model="pwd" autocomplete="current-password" @input="state = null" />
          <b-input-group-append>
            <b-button variant="primary" type="submit">登录</b-button>
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
    async onLogin () {
      try {
        let { state, result } = (await this.$parent.service.post('login', { pwd: this.pwd })).data
        this.state = result.succeed
        if (state === 0) {
          if (result.succeed === true) {
            this.$parent.token = result.token
            setTimeout(() => this.$router.push('/manage/all'), 5000)
            this.state = true
            this.feedback = '登录成功，将于5秒后为您跳转。此次登录将于1小时后过期，您也可以选择提前登出'
          } else {
            this.feedback = '密码错误'
          }
        } else {
          this.feedback = '登录失败，错误码' + state
        }
      } catch (e) {
        console.error(e)
        this.state = false
        this.feedback = '登录失败，错误信息见console'
      }
    }
  }
}
</script>
