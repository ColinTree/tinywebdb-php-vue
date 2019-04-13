<template>
  <div>
    <div class="sticky-top" style="background-color:#fcfcfc;display:block;border-bottom:1px solid #f9f9f9">
      <b-navbar toggleable="lg" class="app-width" style="padding:14px">
        <b-navbar-brand to="/">TinyWebDB后台</b-navbar-brand>
        <b-navbar-toggle target="nav_collapse" />
        <b-collapse is-nav id="nav_collapse">
          <b-navbar-nav>
            <b-nav-item  to="/manage/all">全部标签</b-nav-item>
            <b-nav-item to="/manage/backup">备份/恢复</b-nav-item>
            <b-nav-item to="/manage/file">文件目录</b-nav-item>
            <b-nav-item to="/manage/setting">设置</b-nav-item>
            <b-nav-item v-if="update_available" :href="update_pageUrl" target="_blank">管理系统有更新！</b-nav-item>
          </b-navbar-nav>
          <b-navbar-nav class="ml-auto">
            <b-nav-item to="/manage/logout">登出后台</b-nav-item>
          </b-navbar-nav>
        </b-collapse>
      </b-navbar>
    </div>

    <div class="app-width">
      <div style="margin-top:5px">
        <router-view/>
      </div>

      <div id="footer">
        TinyWebDB MANAGE System By <b-link target="_blank" href="http://github.com/Colintree">Colintree</b-link>.
        VERSION: {{ version }}
        <label :style="{ float: 'right' }">
          <b-link target="_blank" href="http://tsp.colintree.cn/使用手册">需要帮助？</b-link>
          &nbsp;
          <b-link target="_blank" href="http://tsp.colintree.cn/意见反馈">意见反馈</b-link>
          &nbsp;
          <b-link target="_blank" href="https://github.com/ColinTree/tinywebdb-php-vue">查看TPV源码</b-link>
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
      version: '20190322_1',
      update_available: false,
      update_pageUrl: null,
      service: null
    }
  },
  created () {
    let service = axios.create({
      baseURL: 'http://1.tpv0.applinzi.com/Manage/'
    })
    service.defaults.headers['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8'
    service.interceptors.request.use(config => {
      // TODO: add auth data
      config.data = qs.stringify(config.data)
      if (config.method === 'get') {
        config.url += '?'
        config.url += config.data
        config.data = ''
      }
      return config
    })
    this.service = service
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
