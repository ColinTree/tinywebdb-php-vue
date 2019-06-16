import Vue from 'vue'
import Router from 'vue-router'
import Index from '@/components/Index'
import ManageFrame from '@/components/manage/Frame'

import ManageAll from '@/components/manage/All'
import ManageBackup from '@/components/manage/Backup'
import ManageFile from '@/components/manage/File'
import ManageInit from '@/components/manage/Init'
import ManageLogin from '@/components/manage/Login'
import ManageSearch from '@/components/manage/Search'
import ManageSetting from '@/components/manage/Setting'

Vue.use(Router)

export default new Router({
  mode: 'hash',
  routes: [
    {
      path: '/',
      component: Index
    },
    {
      path: '/manage',
      component: ManageFrame,
      children: [
        { path: '', redirect: 'all' },
        { path: 'all', component: ManageAll },
        { path: 'backup', component: ManageBackup },
        { path: 'file', component: ManageFile },
        { path: 'init', component: ManageInit },
        { path: 'login', component: ManageLogin },
        { path: 'search', component: ManageSearch },
        { path: 'setting', component: ManageSetting },
        { path: '*', component: { template: '<BaseCard>page not found</BaseCard>' } }
      ]
    },
    {
      path: '*',
      component: { template: '<div>Page not found</div>' }
    }
  ]
})
