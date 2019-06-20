// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Axios from 'axios'
import qs from 'qs'

import Vue from 'vue'
import router from './router'

import BaseCard from '@/components/root/BaseCard'
import LiningCode from '@/components/root/LiningCode'
import SpinnerButton from '@/components/root/SpinnerButton'

import TextWidthTester from '@/components/root/TextWidthTester'
import ConfirmModal from '@/components/root/ConfirmModal'
import InfoModal from '@/components/root/InfoModal'

import VueCookies from 'vue-cookies'

import BootstrapVue from 'bootstrap-vue'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

import { library } from '@fortawesome/fontawesome-svg-core'
import { faSearch } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(faSearch)

Vue.use(VueCookies)
Vue.use(BootstrapVue)

Vue.component('font-awesome-icon', FontAwesomeIcon)

Vue.component('BaseCard', BaseCard)
Vue.component('LiningCode', LiningCode)
Vue.component('SpinnerButton', SpinnerButton)

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  components: { TextWidthTester, ConfirmModal, InfoModal },
  template: '<div>' +
              '<router-view/>' +
              '<TextWidthTester ref="textWidthTester" />' +
              '<ConfirmModal ref="confirmModal" />' +
              '<InfoModal ref="infoModal" />' +
            '</div>',
  data () {
    return {
      AUTHOR_URL: 'https://github.com/ColinTree',
      REPO_URL: 'https://github.com/ColinTree/tinywebdb-php-vue',
      REPO_API_URL: 'https://api.github.com/repos/ColinTree/tinywebdb-php-vue',
      SERVICE_BASE_URL: '/',
      VERSION_NAME: '1.0.0',
      VERSION_CODE: [ 1, 0, 0 ],

      service: null
    }
  },
  created () {
    this.SERVICE_BASE_URL = window.location.origin + '/'

    let service = Axios.create({ baseURL: this.SERVICE_BASE_URL })
    service.interceptors.request.use(config => {
      config.data = qs.stringify(config.data)
      return config
    }, error => Promise.reject(error))
    // TODO: make only status === 0 resolve, and reject all other statuss
    service.interceptors.response.use(undefined, error => Promise.reject(error))
    this.service = service
  },
  methods: {
    // use await this.$root.sleep(ms) in async functions
    sleep (ms) {
      return new Promise(resolve => setTimeout(resolve, ms))
    },
    showConfirm (title, content, okCallback, cancelCallback) {
      this.$refs.confirmModal.show(title, content, okCallback, cancelCallback)
    },
    showInfo (content, hiddenCallback = null) {
      this.$refs.infoModal.show('', content, hiddenCallback)
    },
    showToast (message) {
      this.$bvToast.toast(message, {
        toaster: 'b-toaster-top-right',
        appendToast: true,
        solid: true
      })
    },
    testTextWidth (text) {
      return this.$refs.textWidthTester.test(text)
    }
  }
})
