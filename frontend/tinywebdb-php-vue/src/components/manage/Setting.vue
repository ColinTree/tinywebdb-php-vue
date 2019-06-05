<template>
  <BaseCard>
    <template slot="header">设置</template>
    <SpinnerButton @click="deletepwd" variant="danger">清除登录密码</SpinnerButton>
  </BaseCard>
</template>

<script>
export default {
  name: 'ManageSetting',
  methods: {
    deletepwd (done) {
      this.$root.showConfirm('', '确认要清除登录密码？', async () => {
        await this.$parent.service.post('deletepwd')
        this.$parent.token = null
        this.$router.push('/manage/init')
        this.$root.showToast('系统密码已重置，请重新设置新的密码')
        done()
      }, () => done())
    }
  }
}
</script>

<style>

</style>
