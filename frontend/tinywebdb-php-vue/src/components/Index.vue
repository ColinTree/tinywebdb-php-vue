<template>
  <div id="content">
    <b-card title="TinyWebDB 可视化操作页面">
      <b-card-text style="display:flex; flex-wrap:wrap">
        <span>您的网络微数据库地址是：</span>
        <b-input ref="service_url" style="box-shadow:none; border:0" readonly size="sm"
            :value="$root.SERVICE_BASE_URL" @click="selectServiceUrl" />
      </b-card-text>

      <hr>

      <b-form @submit.prevent="$refs.get_btn.onClick()">
        <h5>获取标签</h5>
        <b-form-group label="标签" label-for="get_key" label-cols="1">
          <b-input id="get_key" v-model="get_key" autocomplete="off" />
        </b-form-group>
        <b-form-group label="值" label-for="get_value" label-cols="1">
          <b-input id="get_value" ref="get_value" v-model="get_value" readonly @click="selectGetValue" />
        </b-form-group>
        <b-spinner-button ref="get_btn" @click="onGet" :variant="get_succeed ? 'success' : 'primary'">
          <span v-text="get_succeed ? '查询成功' : '查询'" />
        </b-spinner-button>
        <b-button type="submit" v-show="false" />
      </b-form>

      <hr>

      <b-form @submit.prevent="$refs.store_btn.onClick()">
        <h5>储存标签</h5>
        <b-form-group label="标签" label-for="store_key" label-cols="1">
          <b-input id="store_key" v-model="store_key" autocomplete="off" />
        </b-form-group>
        <b-form-group label="值" label-for="store_value" label-cols="1">
          <b-input id="store_value" v-model="store_value" autocomplete="off" />
        </b-form-group>
        <b-spinner-button ref="store_btn" @click="onStore" :variant="store_succeed ? 'success' : 'primary'">
          <span v-text="store_succeed ? '保存成功' : '保存'" />
        </b-spinner-button>
        <b-button type="submit" v-show="false" />
      </b-form>

      <hr>

      <div style="display:flex; justify-content:space-between">
        <b-link to="/manage">前往服务器后台</b-link>
        <label style="color:gray; font-size:80%">
          <b-link target="_blank" :href="$root.repo">Project TPV</b-link>
          By
          <b-link target="_blank" :href="$root.author">ColinTree</b-link>,
          version <span v-text="$root.versionName" />
        </label>
      </div>
    </b-card>
  </div>
</template>

<script>
export default {
  name: 'Index',
  data () {
    return {
      get_key: '',
      get_value: '',
      get_succeed: false,
      store_key: '',
      store_value: '',
      store_succeed: false
    }
  },
  methods: {
    async onGet (done) {
      try {
        this.get_value = (await this.$root.service.get('/getvalue', { data: { tag: this.get_key } })).data[2]
        this.get_succeed = true
        setTimeout(() => (this.get_succeed = false), 800)
      } catch (e) {
        if (e.response && e.response.status === 403) {
          this.$root.showInfo('', '获取失败，该标签属于系统保留标签')
        } else {
          console.error(e)
          this.$root.showInfo('', '获取失败，错误信息见console')
        }
      } finally {
        done()
      }
    },
    async onStore (done) {
      try {
        await this.$root.service.post('/storeavalue', { tag: this.store_key, value: this.store_value })
        this.store_succeed = true
        setTimeout(() => (this.store_succeed = false), 800)
      } catch (e) {
        if (e.response && e.response.status === 403) {
          this.$root.showInfo('', '保存失败，该标签属于系统保留标签')
        } else {
          console.error(e)
          this.$root.showInfo('', '保存失败，错误信息见console')
        }
      } finally {
        done()
      }
    },
    selectServiceUrl () {
      this.$refs.service_url.select()
    },
    selectGetValue () {
      this.$refs.get_value.select()
    }
  }
}
</script>

<style>
#content {
  margin-top: 50px;
  margin-bottom: 50px;
  margin-left: -25%;
  position: absolute;
  left: 50%;
  width: 50%;
}
@media (min-width: 2000px) {
  #content {
    width: 1000px;
    margin-left: -500px;
  }
}
@media (max-width: 1200px) {
  #content {
    width: 600px;
    margin-left: -300px;
  }
}
@media (max-width: 600px) {
  #content {
    width: 100%;
    margin: 0;
    position: unset;
  }
  .card {
    margin: 0;
  }
}
</style>
