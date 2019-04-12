<template>
  <BaseCard>
    <template slot="header">
      标签 -
      <select v-model="currentCategory" class="transparent-dropdown">
        <option v-for="category in categories" :key="category.name" :value="category.name" v-text="category.text" />
      </select>
      <span style="float:right">
        一页显示数量
        <select v-model="perPage" class="transparent-dropdown">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
        &nbsp;
        <b-link @click="loadItems">刷新</b-link>
      </span>
    </template>

    <b-card v-if="anyItemSelected" id="floating_card">
      <b-button variant="danger" size="sm">删除</b-button>
    </b-card>

    <b-pagination
        :total-rows="itemCount" :per-page="perPage"
        :limit="10"
        v-model="currentPage"
        align="center" />

    <b-table
        ref="table"
        class="unselectable"
        :busy="isLoading"
        :show-empty="true" empty-text="没有数据"
        :fields="fields" :items="items"
        @head-clicked="onSelectAll(!selectAll)"
        @row-clicked="(item, index) => { item.selected = !item.selected }"
        :per-page="perPage">
      <template slot="top-row">
        <td />
        <td><b-input class="form-control" placeholder="标签名" /></td>
        <td><b-button variant="primary" @click="onCreate">新建</b-button></td>
      </template>

      <div slot="table-busy" class="text-center text-danger my-2">
        <b-spinner class="align-middle" />
        <strong>加载中……</strong>
      </div>

      <template slot="HEAD_checkbox">
        <b-checkbox @change="onSelectAll" v-model="selectAll" /><!-- don't use @input ! -->
      </template>

      <template slot="checkbox" slot-scope="row">
        <b-checkbox v-model="row.item.selected" />
      </template>

      <template slot="keyNVal" slot-scope="row">
        <span v-html="itemToHtml(row.index)" />
      </template>

      <template slot="operations" slot-scope="row">
        <b-button variant="primary" @click="onShow(row.item, row.index, $event)">查看</b-button>
        <b-button variant="primary" @click="onEdit(row.item, row.index, $event)">修改</b-button>
        <b-button variant="danger"  @click="onDelete(row.item, row.index, $event)">删除</b-button>
      </template>
    </b-table>

    <b-pagination
        id="bottom_pagination"
        :total-rows="itemCount" :per-page="perPage"
        :limit="10"
        v-model="currentPage"
        align="center" />

    <ConfirmModal ref="confirmModal" />
    <InfoModal ref="infoModal" />
    <TextWidthTester ref="textWidthTester" />
  </BaseCard>
</template>

<script>
import ConfirmModal from '@/components/ConfirmModal'
import InfoModal from '@/components/InfoModal'
import TextWidthTester from '@/components/TextWidthTester'

export default {
  name: 'ManageAll',
  components: { ConfirmModal, InfoModal, TextWidthTester },
  data () {
    return {
      currentCategory: 'all',
      categories: [ { name: 'all', text: '显示所有' } ],
      perPage: 10,
      currentPage: 1,
      fields: [
        { key: 'checkbox', label: '' },
        { key: 'keyNVal', label: '标签-值', class: 'key-and-val' },
        { key: 'operations', label: '操作' }
      ],
      isLoading: false,
      items: [],
      itemCount: 0,
      selectAll: false
    }
  },
  computed: {
    anyItemSelected () {
      for (let index in this.items) {
        if (this.items[index].selected === true) {
          return true
        }
      }
      return false
    },
    allItemSelected () {
      if (this.items.length === 0) {
        return false
      }
      for (let index in this.items) {
        if (this.items[index].selected !== true) {
          return false
        }
      }
      return true
    }
  },
  watch: {
    currentPage () {
      this.loadItems()
    },
    perPage () {
      this.currentPage = 1
      this.loadItems()
    },
    allItemSelected (val) {
      this.selectAll = val
    }
  },
  mounted () {
    this.loadItems()
  },
  methods: {
    async loadItems () {
      this.isLoading = true
      let fetchCurrPage = this.$parent.service.get(`/page/${this.currentPage};;${this.perPage};;` /* TODO: prefix */)
      let fetchCount = this.$parent.service.get('/count')
      try {
        let result = await Promise.all([ fetchCurrPage, fetchCount ])
        let pageItems = result[0].data.state === 0 ? JSON.parse(result[0].data.result) : []
        pageItems.forEach(item => { item.selected = false })
        this.items = pageItems
        this.itemCount = result[1].data.state === 0 ? Number.parseInt(result[1].data.result) : 0
      } catch (e) {
        console.error(e)
        this.showInfo('', '数据拉取失败, 错误信息见console')
      } finally {
        this.isLoading = false
      }
    },
    itemToHtml (index) {
      const ACCEPTED_WIDTH = 530
      let item = this.items[index]
      let val = item.value.toString()
      if (val.length > 100 || this.testTextWidth(val) > ACCEPTED_WIDTH) {
        let pointer = 1
        while (this.testTextWidth(val.substring(0, pointer)) < ACCEPTED_WIDTH) {
          pointer++
        }
        return `${item.key}<br/><div class="item-value">${val.substring(0, pointer - 1)}……</div>`
      } else {
        return `${item.key}<br/><div class="item-value">${val}</div>`
      }
    },
    onSelectAll (val) {
      this.items.forEach(item => (item.selected = val))
      this.$refs.table.refresh()
    },
    onRowClicked (item, index, event) {
      this.onShow(item, index, event)
    },
    onShow (item, index, event) {
      item = { ...item }
      delete item.selected
      this.showInfo('查看标签', JSON.stringify(item, null, 2))
    },
    onCreate () {
      // TODO:
    },
    onEdit (item, index, event) {
      // TODO:
    },
    onDelete (item, index, event) {
      this.showConfirm('', `确认要删除${item.key}吗`, async result => {
        if (result) {
          let rst = await this.$parent.service.get(`/delete/${item.key}`)
          switch (rst.data.state) {
            case 0: {
              this.showInfo('', '删除完成')
              this.loadItems()
              break
            }
            default: {
              this.showInfo('', `删除失败，错误码${rst.data.state}`)
            }
          }
        }
      })
    },
    showConfirm (title, content, callback) {
      this.$refs.confirmModal.show(title, content, callback)
    },
    showInfo (title, content) {
      this.$refs.infoModal.show(title, content)
    },
    testTextWidth (text) {
      return this.$refs.textWidthTester.test(text)
    }
  }
}
</script>

<style>
.key-and-val {
  width: 603px;
}
.transparent-dropdown {
  background-color: transparent;
  border: 0;
  outline-color: lightgray;
}
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
.item-value {
  color: #aaa;
  font-size: 16px;
}
#bottom_pagination {
  margin: 0;
  padding: 0;
}
</style>
