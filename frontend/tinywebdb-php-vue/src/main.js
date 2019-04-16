// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'

import BaseCard from '@/components/BaseCard'
import TextWidthTester from '@/components/TextWidthTester'
import LiningCode from '@/components/LiningCode'

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
  components: { App, TextWidthTester },
  template: '<div><App/><TextWidthTester ref="textWidthTester" /></div>'
})
