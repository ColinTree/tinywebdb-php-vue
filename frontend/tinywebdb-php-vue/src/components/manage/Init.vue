<template>
  <b-jumbotron bg-variant="light">
    <template slot="header">欢迎使用TPV</template>

    <template slot="lead">
      TPV（Tinywebdb-PHP-Vue），是一个高度可拓展的tinywebdb开源管理系统，由ColinTree主要负责开发和维护，具体见<b-link target="_blank" :href="$root.REPO_URL">TPV源码</b-link>
    </template>

    <hr class="my-4">

    <p><b>设置后台密码</b></p>

    <b-form @submit.prevent="$refs.submit_btn.onClick()">
      <b-form-group label-cols="2" label="后台密码" label-for="pwd">
        <b-input id="pwd" type="password" autocomplete="new-password" v-model="pass" :state="pwdState" />
        <template slot="description">
          <span :style="{ color: pwdTooShort ? 'red' : 'green' }">长度大于8位</span>
          <span :style="{ color: pwdInvalid ? 'red' : 'green' }">由数字、字母（区分大小写）、标点符号组成，且至少包含数字，字母，标点符号中的两种</span>
        </template>
      </b-form-group>
      <b-form-group label-cols="2" label="确认密码" label-for="pwd2">
        <b-input id="pwd2" type="password" autocomplete="new-password" v-model="pass2" :state="pwd2State" />
      </b-form-group>
      <SpinnerButton ref="submit_btn" @click="onSubmit" variant="primary" :disabled="!pwdState || !pwd2State">提交</SpinnerButton>
      <b-button type="submit" v-show="false" />
    </b-form>
  </b-jumbotron>
</template>

<script>
export default {
  name: 'ManageInit',
  data () {
    return {
      pass: '',
      pass2: ''
    }
  },
  computed: {
    pwdTooShort () {
      return this.pass.length < 8
    },
    pwdInvalid () {
      if (this.pass.length === 0) return true
      return this.pass.match(/^\d+$/) !== null ||
            this.pass.match(/^[a-z]+$/i) !== null ||
            this.pass.match(/^[0-9a-z!@#$%^&*]+$/i) === null
    },
    pwdState () {
      return !(this.pwdTooShort || this.pwdInvalid)
    },
    pwd2State () {
      if (this.pass === '') {
        return undefined
      }
      return this.pass === this.pass2
    }
  },
  methods: {
    async onSubmit (onDone) {
      let { status, result } = (await this.$parent.service.post('init', { pwd: this.pass })).data
      switch (status) {
        case 0: {
          this.$parent.token = result
          this.$router.push('/manage/all')
          break
        }
        case 4: {
          this.$router.push('/manage/login')
          this.$root.showInfo(`系统已经被初始化过了`)
          break
        }
        case 40: {
          this.$root.showInfo(`密码太短`)
          break
        }
        case 41: {
          this.$root.showInfo(`密码太简单，至少要包含数字，字母，符号（!@#$%^&*）中的两种`)
          break
        }
        default: {
          this.$root.showInfo(`系统初始化失败，错误码${status}`)
        }
      }
      onDone()
    }
  }
}
</script>

<style>

</style>
