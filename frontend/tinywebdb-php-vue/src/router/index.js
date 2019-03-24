import Vue from 'vue'
import Router from 'vue-router'
import HelloWorld from '@/components/HelloWorld'
import ManageAll from '@/components/manage/All'
import ManageBackup from '@/components/manage/Backup'
import ManageFile from '@/components/manage/File'
import ManageSetting from '@/components/manage/Setting'
import ManageLogout from '@/components/manage/Logout'

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
      path: '/manage/backup',
      name: 'manage_backup',
      component: ManageBackup
    },
    {
      path: '/manage/file',
      name: 'manage_file',
      component: ManageFile
    },
    {
      path: '/manage/setting',
      name: 'manage_setting',
      component: ManageSetting
    },
    {
      path: '/manage/logout',
      name: 'manage_logout',
      component: ManageLogout
    },
    {
      path: '/manage*',
      name: 'manage_all',
      component: ManageAll
    },
    {
      path: '*',
      name: '404',
      component: HelloWorld
    }
  ]
})
