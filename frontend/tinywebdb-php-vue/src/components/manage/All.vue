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

    <b-card v-if="anyItemSelected" id="floating_card">
      <b-button variant="danger" size="sm" @click="onMultiDelete">删除</b-button>
    </b-card>

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
  computed: {
    anyItemSelected () {
      for (let index in this.items) {
        if (this.items[index].deleted !== true && this.items[index].selected === true) {
          return true
        }
      }
      return false
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
    },
    onMultiDelete () {
      this.$root.showConfirm('', '确认要删除这些标签吗', async () => {
        let deleteArr = []
        for (let index in this.items) {
          let item = this.items[index]
          if (item.selected === true && item.deleted !== true) {
            deleteArr.push('' + item.key)
          }
        }
        let { status, result } = (await this.$parent.service.post('mdelete', { keys: JSON.stringify(deleteArr) })).data
        switch (status) {
          case 0: {
            let map = {}
            this.items.forEach((val, index) => (map[val.key] = index))
            let failedKeys = []
            for (let key in result) {
              if (result[key] === true) {
                this.items[map[key]].deleted = true
              } else {
                failedKeys.push(key)
              }
            }
            if (failedKeys.length > 0) {
              this.$root.showInfo('', `以下标签删除失败：\`${failedKeys.join('`，`')}\``)
            }
            break
          }
          default: {
            this.$root.showInfo('', `删除失败，错误码${status}`)
          }
        }
      })
    }
  }
}
</script>

<style>
#floating_card {
  position: fixed;
  left: 50%;
  top: 130px;
  width: 82px;
  margin-left: -550px;
  text-align: center;
}
#floating_card .card-body {
  padding: 15px 0;
}
#floating_card .btn {
  margin: 3px;
}
</style>
