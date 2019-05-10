<template>
  <div>
    <div class="sticky-top" style="background-color:#fcfcfc;display:block;border-bottom:1px solid #f9f9f9">
      <b-navbar toggleable="lg" class="app-width" style="padding:14px">
        <b-navbar-brand to="/manage">TinyWebDB后台</b-navbar-brand>
        <b-navbar-toggle target="nav_collapse" />
        <b-collapse is-nav id="nav_collapse" v-if="$route.path !== '/manage/init'">
          <b-navbar-nav v-if="token !== null">
            <b-nav-item  to="/manage/all">全部标签</b-nav-item>
            <b-nav-item to="/manage/backup">备份/恢复</b-nav-item>
            <b-nav-item to="/manage/file">文件目录</b-nav-item>
            <b-nav-item to="/manage/setting">设置</b-nav-item>
            <b-nav-item v-if="update_available" :href="update_pageUrl" target="_blank">管理系统有更新！</b-nav-item>
          </b-navbar-nav>
          <b-navbar-nav class="ml-auto">
            <b-nav-item v-if="token !== null" @click.stop.prevent="onLogout">登出后台</b-nav-item>
            <b-nav-item v-else-if="$route.path !== '/manage/login'" to="/manage/login">登录</b-nav-item>
          </b-navbar-nav>
        </b-collapse>
      </b-navbar>
    </div>

    <div class="app-width">
      <div style="margin-top:5px">
        <b-spinner v-if="pingDone !== true" />
        <router-view v-else />
      </div>

      <div id="footer">
        TinyWebDB MANAGE System By <b-link target="_blank" :href="$root.author">Colintree</b-link>.
        VERSION: <span v-text="$root.versionName" />
        <label :style="{ float: 'right' }">
          <b-link target="_blank" href="http://tsp.colintree.cn/使用手册">需要帮助？</b-link>
          &nbsp;
          <b-link target="_blank" href="http://tsp.colintree.cn/意见反馈">意见反馈</b-link>
          &nbsp;
          <b-link target="_blank" :href="$root.repo">查看TPV源码</b-link>
        </label>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import qs from 'qs'

export default {
  name: 'ManageFrame',
  data () {
    return {
      pingDone: false,
      update_available: false,
      update_pageUrl: null,
      service: null,
      token: null
    }
  },
  watch: {
    token (val) {
      if (val !== null) {
        this.$cookies.set('manage_session', val, 60 * 60)
      } else if (this.$cookies.isKey('manage_session')) {
        this.$cookies.remove('manage_session')
      }
    }
  },
  created () {
    let service = axios.create({
      baseURL: this.$root.SERVICE_BASE_URL + '/manage/',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      transformRequest: data => qs.stringify(data)
    })
    service.interceptors.request.use(config => {
      if (this.$cookies.isKey('manage_session')) {
        this.token = this.$cookies.get('manage_session')
      }
      config.headers['X-TPV-Manage-Token'] = this.token
      return config
    }, error => Promise.reject(error))
    service.interceptors.response.use(undefined, error => {
      if (error.response && error.response.data.state === 4) {
        this.$root.showInfo('', this.token === null ? '请先登录' : '登录已失效，请重新登录')
        this.token = null
        this.$router.push('/manage/login')
        error.handled = true
      }
      return Promise.reject(error)
    })
    this.service = service
    this.ping()
  },
  methods: {
    onLogout () {
      this.$root.showConfirm('', '是否要登出？', async () => {
        try {
          await this.service.get('logout')
          this.token = null
          this.$router.push('/manage/login')
        } catch (e) {
          this.$root.showInfo('', '登出失败')
        }
      })
    },
    async ping () {
      try {
        let { initialized, login } = (await this.service.get('ping')).data.result
        this.pingDone = true
        if (initialized === false) {
          this.$router.push('/manage/init')
          return
        }
        if (login !== true) {
          this.$router.push('/manage/login')
        } else if (this.$route.path === '/manage/login' || this.$route.path === '/manage/init') {
          this.$router.push('/manage/all')
        }
      } catch (e) {
        this.$root.showInfo('', '无法连接服务器，详细信息见console')
        console.error(e)
      }
    }
  }
}
</script>

<style>
.app-width {
  width: 900px;
  margin: 0 auto;
}
#footer {
  margin-top: 20px;
  width: 100%;
  padding-left: 10px;
  font-size: 14px;
}
#footer, #footer a {
  color: #bbb;
}
#footer a {
  text-decoration: underline;
}
</style>
