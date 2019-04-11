<template>
  <BaseCard>
    <template slot="header">
      标签 -
      <select v-model="currentCategory" class="transparent-dropdown">
        <option v-for="category in categories" :key="category.name" :value="category.name">{{ category.text }}</option>
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
        :busy="isLoading"
        :show-empty="true" empty-text="没有数据"
        :fields="fields" :items="items"
        @row-clicked="onRowClicked"
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
        <b-checkbox @input="checkSelectAll(); checkAnySelected()" v-model="row.item.selected" />
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

    <!-- Info modal -->
    <b-modal id="showModalInfo" @hide="onShowModalClosed" :title="showModalInfo.title" ok-only>
      <pre v-text="showModalInfo.content" />
    </b-modal>

    <div id="text-width-tester" class="item-value" style="position:absolute;visibility:hidden;height:auto;width:auto;white-space:nowrap"/>
  </BaseCard>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ManageAll',
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
      anyItemSelected: false,
      selectAll: false,
      showModalInfo: { title: '', content: '' }
    }
  },
  watch: {
    currentPage () {
      this.loadItems()
    },
    perPage () {
      this.currentPage = 1
      this.loadItems()
    }
  },
  mounted () {
    this.loadItems()
  },
  methods: {
    async loadItems () {
      this.isLoading = true
      let fetchCurrPage = axios.get('http://1.tpv0.applinzi.com/Manage/page/' + this.currentPage + ';;' + this.perPage + ';;' + '')
      let fetchCount = axios.get('http://1.tpv0.applinzi.com/Manage/count')
      let result = [ { data: { state: -1 } }, { data: { state: -1 } } ]
      try {
        result[0] = await fetchCurrPage
      } catch (e) { console.error(e) }
      try {
        result[1] = await fetchCount
      } catch (e) { console.error(e) }
      this.items = result[0].data.state === 0 ? JSON.parse(result[0].data.result) : []
      this.itemCount = result[1].data.state === 0 ? Number.parseInt(result[1].data.result) : 0
      this.isLoading = false
    },
    itemToHtml (index) {
      const ACCEPTED_WIDTH = 530
      let item = this.items[index]
      let val = item.value.toString()
      if (val.length > 100 || this.determineTextWidth(val) > ACCEPTED_WIDTH) {
        let pointer = 1
        while (this.determineTextWidth(val.substring(0, pointer)) < ACCEPTED_WIDTH) {
          pointer++
        }
        return `${item.key}<br/><div class="item-value">${val.substring(0, pointer - 1) + '……'}</div>`
      } else {
        return `${item.key}<br/><div class="item-value">${val}</div>`
      }
    },
    determineTextWidth (text) {
      let test = document.getElementById('text-width-tester')
      test.innerText = text
      let width = test.clientWidth
      test.innerText = ''
      return width
    },
    onSelectAll (val) {
      this.items.forEach(item => (item.selected = val))
      this.checkAnySelected()
      this.$refs.table.refresh()
    },
    checkSelectAll () {
      let selectAll = true
      this.items.forEach(item => (selectAll = selectAll && item.selected === true))
      this.selectAll = selectAll
    },
    checkAnySelected () {
      let anyItemSelected = false
      this.items.forEach(item => (anyItemSelected = anyItemSelected || item.selected === true))
      this.anyItemSelected = anyItemSelected
    },
    onRowClicked (item, index, event) {
      this.onShow(item, index, event)
    },
    onShow (item, index, event) {
      this.showModalInfo.title = `Row index: ${index}`
      item = { ...item }
      delete item.selected
      this.showModalInfo.content = JSON.stringify(item, null, 2)
      this.$root.$emit('bv::show::modal', 'showModalInfo', event.target)
    },
    onShowModalClosed () {
      this.showModalInfo.title = ''
      this.showModalInfo.content = ''
    },
    onCreate () {
      // TODO:
    },
    onEdit (item, index, event) {
      // TODO:
    },
    onDelete (item, index, event) {
      // TODO:
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
