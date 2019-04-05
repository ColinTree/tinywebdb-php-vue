import Vue from 'vue'
import Router from 'vue-router'
import HelloWorld from '@/components/HelloWorld'
import ManageFrame from '@/components/manage/Frame'

import ManageAll from '@/components/manage/All'
import ManageBackup from '@/components/manage/Backup'
import ManageFile from '@/components/manage/File'
import ManageLogout from '@/components/manage/Logout'
import ManageSetting from '@/components/manage/Setting'

Vue.use(Router)

export default new Router({
  mode: 'hash',
  routes: [
    {
      path: '/',
      name: 'HelloWorld',
      component: HelloWorld
    },
    {
      path: '/manage',
      name: 'manage',
      component: ManageFrame,
      children: [
        { path: '', redirect: 'all' },
        { path: 'all', component: ManageAll },
        { path: 'backup', component: ManageBackup },
        { path: 'file', component: ManageFile },
        { path: 'setting', component: ManageSetting },
        { path: 'logout', component: ManageLogout },
        { path: '*', component: { template: '<BaseCard>page not found</BaseCard>' } }
      ]
    },
    {
      path: '*',
      name: '404',
      component: { template: '<div>Page not found</div>' }
    }
  ]
})
