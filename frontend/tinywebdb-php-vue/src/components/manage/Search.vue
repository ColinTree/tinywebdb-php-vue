<template>
  <div>
    <b-input-group>
      <b-input v-model="text" @keypress.enter="() => { currentPage = 1; onSearch() }" />
      <b-input-group-append>
        <b-button variant="primary" @click="onSearch">搜索</b-button>
      </b-input-group-append>
    </b-input-group>
    <b-card style="display:flex">
      <template slot="header">
        <b-form-group>
          <b-checkbox v-model="ignoreCase">忽略大小写</b-checkbox>
        </b-form-group>
        搜索范围
        <b-form-radio-group
            v-model="range"
            :options="[{value:'key',text:'标签'},{value:'value',text:'值'},{value:'both',text:'标签和值'}]" />
      </template>

      <DataTable
          :show-create="false"
          :item-count="itemCount"
          :current-page="currentPage"
          :busy="isLoading"
          :items="items"
          :per-page="20"
          @switchPage="onSwitchPage"
          @requestLoadItems="loadItems" />
    </b-card>
  </div>
</template>

<script>
import DataTable from '@/components/manage/functional/DataTable.vue'

import qs from 'qs'

export default {
  name: 'ManageSearch',
  components: { DataTable },
  data () {
    return {
      text: '',
      ignoreCase: false,
      range: 'key',
      itemCount: 0,
      currentPage: 1,
      isLoading: false,
      items: []
    }
  },
  computed: {
    searchParams () {
      return {
        text: this.text,
        ignore_case: this.ignoreCase,
        range: this.range,
        page: this.currentPage
      }
    }
  },
  watch: {
    '$route.query' () {
      this.readQuery()
    }
  },
  mounted () {
    this.readQuery()
    this.onSearch()
  },
  methods: {
    readQuery () {
      if (this.$route.query.text !== undefined) {
        this.text = this.$route.query.text
      }
      if (this.$route.query.ignore_case !== undefined) {
        this.ignoreCase = this.$route.query.ignore_case
      }
      if (this.$route.query.range !== undefined) {
        this.range = this.$route.query.range
      }
    },
    async onSearch () {
      this.isLoading = true
      this.page = 1
      this.$router.push('?' + qs.stringify(this.searchParams))
      let { status, result } = (await this.$parent.service.post('search', this.searchParams)).data
      this.isLoading = false
      switch (status) {
        case 0: {
          this.items = result.content.map(item => ({ selected: false, deleted: false, ...item }))
          this.itemCount = result.count
          if (this.currentPage > 1 && this.items.length === 0) {
            this.currentPage = 1
            this.onSearch()
          }
          return
        }
        default: {
          this.$root.showInfo(`搜索失败，错误码${status}`)
        }
      }
    },
    onSwitchPage (to) {
      if (to === this.currentPage) {
        return
      }
      this.currentPage = to
      this.onSearch()
    },
    loadItems () {
      this.onSearch()
    }
  }
}
</script>

<style>

</style>
