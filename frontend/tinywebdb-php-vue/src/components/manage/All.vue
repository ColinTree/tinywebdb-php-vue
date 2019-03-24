<template>
  <manage-base>
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
      </span>
    </template>

    <b-card v-if="anyItemSelected" id="floating_card">
      <b-button variant="danger" size="sm">删除</b-button>
    </b-card>

    <b-table
        ref="table"
        :busy="isLoading"
        :show-empty="true" empty-text="没有数据"
        :fields="fields" :items="items"
        @row-clicked="onRowClicked"
        :current-page="currentPage" :per-page="perPage">
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
        <b-checkbox @input="checkSelectAll" v-model="row.item.selected" />
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

    <template slot="footer">
      <b-pagination
          v-if=" items.length > perPage"
          id="pagination"
          :total-rows="items.length" :per-page="perPage"
          :limit="10"
          v-model="currentPage"
          align="center" />
    </template>

    <!-- Info modal -->
    <b-modal id="showModalInfo" @hide="onShowModalClosed" :title="showModalInfo.title" ok-only>
      <pre v-text="showModalInfo.content" />
    </b-modal>

    <div id="text-width-tester" class="item-value" style="position:absolute;visibility:hidden;height:auto;width:auto;white-space:nowrap"/>
  </manage-base>
</template>

<script>
import Base from './Base'

export default {
  name: 'ManageAll',
  components: { 'manage-base': Base },
  data () {
    return {
      currentCategory: 'all',
      categories: [ { name: 'all', text: '显示所有' } ],
      perPage: 10,
      currentPage: 1,
      fields: [
        { key: 'checkbox',
          label: '' },
        { key: 'keyNVal',
          label: '标签-值',
          class: 'key-and-val' },
        { key: 'operations',
          label: '操作' }
      ],
      isLoading: false,
      items: [],
      anyItemSelected: false,
      selectAll: false,
      showModalInfo: { title: '', content: '' }
    }
  },
  watch: {
    currentPage () {
      this.selectAll = false
      this.onSelectAll(false)
    },
    perPage () {
      this.selectAll = false
      this.onSelectAll(false)
      this.currentPage = 1
    }
  },
  mounted () {
    this.loadItems()
  },
  methods: {
    loadItems () {
      this.isLoading = true
      // TODO:
      setTimeout(() => {
        this.items = [
          { key: '1', value: '123456789 123456789 123456789 123456789 123456789 123456789 1234567890 70' },
          { key: '2', value: '一二三四五六七八九十一二三四五六七八九十一二三四五六七八九十一二三四五六七八九十一二三四五六七八九十一二三四五六七八九十 60' },
          { key: '3', value: '3' },
          { key: '4', value: '4' },
          { key: '5', value: '5' },
          { key: '6', value: '6' },
          { key: '7', value: '7' },
          { key: '8', value: '8' },
          { key: '9', value: '9' },
          { key: '10', value: '10' },
          { key: '11', value: '11' },
          { key: '12', value: '12' },
          { key: '13', value: '13' },
          { key: '14', value: '14' },
          { key: '15', value: '15' },
          { key: '16', value: '16' },
          { key: '17', value: '17' },
          { key: '18', value: '18' },
          { key: '19', value: '19' },
          { key: '20', value: '20' },
          { key: '21', value: '21' },
          { key: '22', value: '22' },
          { key: '23', value: '23' },
          { key: '24', value: '24' },
          { key: '25', value: '25' },
          { key: '26', value: '26' },
          { key: '27', value: '27' },
          { key: '28', value: '28' },
          { key: '29', value: '29' },
          { key: '30', value: '30' }
        ]
        this.isLoading = false
      }, 1300)
    },
    itemToHtml (index) {
      const ACCEPTED_WIDTH = 530
      index += (this.currentPage - 1) * this.perPage
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
      return test.clientWidth
    },
    onSelectAll (val) {
      let start = this.perPage * (this.currentPage - 1)
      let end = Math.min(start + this.perPage, this.items.length)
      for (let i = start; i < end; i++) {
        this.items[i].selected = val
      }
      this.checkAnySelected()
      this.$refs.table.refresh()
    },
    checkSelectAll () {
      let start = this.perPage * (this.currentPage - 1)
      let end = Math.min(start + this.perPage, this.items.length)
      let selectAll = true
      for (let i = start; i < end; i++) {
        if (this.items[i].selected !== true) {
          selectAll = false
          break
        }
      }
      this.selectAll = selectAll
      this.checkAnySelected()
    },
    checkAnySelected () {
      let start = this.perPage * (this.currentPage - 1)
      let end = Math.min(start + this.perPage, this.items.length)
      let anyItemSelected = false
      for (let i = start; i < end; i++) {
        if (this.items[i].selected === true) {
          anyItemSelected = true
          break
        }
      }
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
#pagination {
  margin: 0;
  padding: 0;
}
</style>
