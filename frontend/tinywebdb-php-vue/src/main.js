// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Axios from 'axios'
import qs from 'qs'

import Vue from 'vue'
import App from './App'
import router from './router'

import BaseCard from '@/components/root/BaseCard'
import LiningCode from '@/components/root/LiningCode'

import TextWidthTester from '@/components/root/TextWidthTester'
import ConfirmModal from '@/components/root/ConfirmModal'
import InfoModal from '@/components/root/InfoModal'

import BootstrapVue from 'bootstrap-vue'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.use(BootstrapVue)

Vue.component('BaseCard', BaseCard)
Vue.component('LiningCode', LiningCode)

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  components: { App, TextWidthTester, ConfirmModal, InfoModal },
  template: '<div>' +
              '<App/>' +
              '<TextWidthTester ref="textWidthTester" />' +
              '<ConfirmModal ref="confirmModal" />' +
              '<InfoModal ref="infoModal" />' +
            '</div>',
  data () {
    return {
      SERVICE_BASE_URL: '/',
      service: null,
      versionName: '1.0.0-alpha',
      version: '100'
    }
  },
  created () {
    this.SERVICE_BASE_URL = window.location.origin + '/'
    this.SERVICE_BASE_URL = 'http://1.tpv0.applinzi.com/' // FIXME: remove when release

    let service = Axios.create({ baseURL: this.SERVICE_BASE_URL })
    service.interceptors.request.use(config => {
      config.data = qs.stringify(config.data)
      if (config.method === 'get') {
        config.url += '?'
        config.url += config.data
        config.data = ''
      }
      return config
    })
    this.service = service
  },
  methods: {
    showConfirm (title, content, callback) {
      this.$refs.confirmModal.show(title, content, callback)
    },
    showInfo (title, content, hiddenCallback = null) {
      this.$refs.infoModal.show(title, content, hiddenCallback)
    },
    testTextWidth (text) {
      return this.$root.$refs.textWidthTester.test(text)
    }
  }
})
