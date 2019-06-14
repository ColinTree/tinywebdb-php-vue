<template>
  <BaseCard>
    <template slot="header">
      <div>
        <b-input-group>
          <div class="vertical-auto-margin">标签 -</div>
          <b-select plain style="padding:0" v-model="currentCategory" :options="categories" />
        </b-input-group>
      </div>
      <div>
        <b-input-group>
          <div class="vertical-auto-margin">一页显示数量</div>
          <b-select plain style="padding:0" v-model="perPage" :options="[ 5, 10, 20, 50, 100 ]" />
          <b-link class="vertical-auto-margin" @click="loadItems">刷新</b-link>
        </b-input-group>
      </div>
    </template>

    <DataTable
        :item-count="itemCount"
        :current-page="currentPage"
        :busy="isLoading"
        :items="items"
        :per-page="perPage"
        @currentPageChanged="onCurrentPageChanged"
        @requestLoadItems="loadItems" />
  </BaseCard>
</template>

<script>
import DataTable from '@/components/manage/functional/DataTable.vue'

export default {
  name: 'ManageAll',
  components: { DataTable },
  data () {
    return {
      currentCategory: '',
      categories: [ { value: '', text: '显示所有' } ],
      perPage: 10,
      currentPage: 1,
      isLoading: false,
      items: [],
      itemCount: 0
    }
  },
  watch: {
    currentCategory () {
      this.loadItems()
    },
    perPage () {
      this.currentPage = 1
      this.loadItems(true)
    }
  },
  async mounted () {
    this.isLoading = true
    let { status, result } = (await this.$parent.service.get('settings')).data
    switch (status) {
      case 0: {
        this.categories = Array.from(
          new Set((result.hasOwnProperty('all_category') ? result.all_category : '').split('#')))
          .map(value => value === '' ? ({ value: '', text: '显示所有' }) : { value, text: `前缀\`${value}\`` })
        this.currentCategory = this.categories[0].value
        this.loadItems()
        return
      }
      default: {
        this.$root.showInfo('', `拉取失败，错误码${status}`)
      }
    }
  },
  methods: {
    async loadItems (cacheCount = false) {
      this.isLoading = true

      let response = (await this.$parent.service.get('page', {
        params: {
          count: !cacheCount,
          page: this.currentPage,
          perPage: this.perPage,
          prefix: this.currentCategory,
          valueLengthLimit: 200
        }
      })).data
      let { status, result } = response
      if (status === 0 && response.count !== undefined) {
        this.itemCount = response.count
        if (this.itemCount <= (this.currentPage - 1) * this.perPage) {
          let targetPage = Math.max(1, Math.ceil(this.itemCount / this.perPage))
          if (targetPage !== this.currentPage) {
            this.currentPage = targetPage
            return // change on currentPage will call loadItem(cacheCount = true)
          }
        }
      }
      let pageItems = status === 0 ? result : []
      this.items = pageItems.map(item => ({ selected: false, deleted: false, ...item }))

      this.isLoading = false
    },
    onCurrentPageChanged (to) {
      this.currentPage = to
      if (this.currentPage <= 0) {
        this.currentPage = 1
        return
      }
      this.loadItems(true)
    }
  }
}
</script>

<style>

</style>
