<template>
  <div>
    <div class="sticky-top" style="background-color:#fcfcfc;display:block;border-bottom:1px solid #f9f9f9">
      <b-navbar toggleable="lg" class="app-width" style="padding:14px">
        <b-navbar-brand to="/manage">TinyWebDB后台</b-navbar-brand>
        <b-navbar-toggle target="nav_collapse" />
        <b-collapse is-nav id="nav_collapse" v-if="$route.path !== '/manage/init'">
          <b-navbar-nav v-if="token !== null">
            <b-nav-item to="/manage/all">全部标签</b-nav-item>
            <b-nav-item to="/manage/backup">备份/恢复</b-nav-item>
            <b-nav-item to="/manage/setting">设置</b-nav-item>
            <b-nav-item
                v-if="update_available"
                :href="$root.REPO_URL + '/releases/latest'"
                target="_blank">
              <span style="color:red">管理系统有更新！</span>
            </b-nav-item>
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
        <div v-if="pingDone !== true">
          <b-spinner />
          连接服务器中
        </div>
        <div v-else-if="typeof pingMessage === 'string'">
          <span v-text="pingMessage" />
        </div>
        <router-view v-else />
      </div>

      <div id="footer">
        TinyWebDB MANAGE System By <b-link target="_blank" :href="$root.AUTHOR_URL">Colintree</b-link>.
        VERSION: <span v-text="$root.VERSION_NAME" />
        <label :style="{ float: 'right' }">
          <b-link target="_blank" :href="$root.REPO_URL + '/blob/master/README.md'">需要帮助？</b-link>
          &nbsp;
          <b-link target="_blank" :href="$root.REPO_URL + '/issues/new/choose'">意见反馈</b-link>
          &nbsp;
          <b-link target="_blank" :href="$root.REPO_URL">查看TPV源码</b-link>
        </label>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import qs from 'qs'
import semver from 'semver'

export default {
  name: 'ManageFrame',
  data () {
    return {
      pingDone: false,
      pingMessage: null,
      update_available: false,
      service: null,
      token: null
    }
  },
  watch: {
    token (val) {
      if (val !== null) {
        this.$cookies.set('manage_session', val)
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
      transformRequest: data => data instanceof FormData ? data : qs.stringify(data)
    })
    service.interceptors.request.use(config => {
      this.updateTokenFromCookie()
      config.headers['X-TPV-Manage-Token'] = this.token
      return config
    }, error => Promise.reject(error))
    service.interceptors.response.use(undefined, error => {
      if (error.response) {
        if (error.response.data.status === 4) {
          this.$root.showInfo(this.token === null ? '请先登录' : '登录已失效，请重新登录')
          this.token = null
          this.$router.push('/manage/login')
          return Promise.reject(new Error('请先登录'))
        }
        return Promise.resolve(error.response)
      }
      return Promise.reject(error)
    })
    this.service = service
    this.ping()
    this.checkUpdate()
  },
  methods: {
    onLogout () {
      this.$root.showConfirm('', '是否要登出？', async () => {
        try {
          await this.service.get('logout')
          this.token = null
          this.$router.push('/manage/login')
        } catch (e) {
          this.$root.showInfo('登出失败')
        }
      })
    },
    updateTokenFromCookie () {
      if (this.$cookies.isKey('manage_session')) {
        this.token = this.$cookies.get('manage_session')
      }
    },
    async ping () {
      try {
        let { status, result } = (await this.service.get('ping')).data
        this.pingDone = true
        if (status === 6) {
          this.pingMessage = '系统配置文件尚未被创建！请参阅帮助文档中的“生产环境的配置”部分配置config.php文件'
          return
        }
        let { initialized, login } = result
        if (initialized === false) {
          this.$router.push('/manage/init')
          return
        }
        if (login !== true) {
          this.token = null
          this.$router.push('/manage/login')
        } else if (this.$route.path === '/manage/login' || this.$route.path === '/manage/init') {
          this.$router.push('/manage/all')
        }
      } catch (e) {
        this.$root.showInfo('无法连接服务器，详细信息见console')
        console.error(e)
      }
    },
    async checkUpdate () {
      try {
        let { data } = await axios.get(this.$root.REPO_API_URL + '/releases/latest')
        let tagName = data.tag_name
        if (!tagName.startsWith('v')) {
          console.log(`latest tag not a formal release: ${tagName}`)
          return
        }
        if (!semver.valid(tagName)) {
          console.log(`latest tag is invalid: ${tagName}`)
          return
        }
        if (semver.gt(tagName, this.$root.VERSION_NAME)) {
          this.update_available = true
        }
      } catch (e) {
        console.error('Can\'t load latest release info', e)
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
