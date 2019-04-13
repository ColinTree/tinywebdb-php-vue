<template>
  <BaseCard >
    <template slot="header">
      <span>
        标签 -
        <select v-model="currentCategory" class="transparent-dropdown">
          <option v-for="category in categories" :key="category.name" :value="category.name" v-text="category.text" />
        </select>
      </span>
      <span>
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
      <b-button variant="danger" size="sm" @click="onMultiDelete">删除</b-button>
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
        <td><b-input placeholder="标签名" v-model="editModal.key" @keypress.enter="$refs.createButton.click()" /></td>
        <td>
          <b-button
              ref="createButton"
              variant="primary"
              @click="editModal.isCreate = true; editModal.value = ''"
              v-b-modal.editModal>新建</b-button>
        </td>
      </template>

      <div slot="table-busy" class="text-center text-danger my-2">
        <b-spinner class="align-middle" />
        <strong>加载中……</strong>
      </div>

      <template slot="HEAD_checkbox">
        <b-checkbox @change="onSelectAll" v-model="selectAll" /><!-- don't use @input ! -->
      </template>

      <template slot="checkbox" slot-scope="row">
        <b-checkbox v-if="!row.item.deleted" v-model="row.item.selected" />
      </template>

      <template slot="keyNVal" slot-scope="row">
        <span v-html="itemToHtml(row.index)" />
      </template>

      <template slot="operations" slot-scope="row">
        <b-button v-if="!row.item.deleted" variant="primary" @click="onShow(row.item, row.index, $event)">查看</b-button>
        <b-button v-if="!row.item.deleted" variant="primary" @click="onEdit(row.item, row.index, $event)">修改</b-button>
        <b-button
            :disabled="row.item.deleted"
            :variant="row.item.deleted ? 'success' : 'danger'"
            v-text="row.item.deleted ? '已删除' : '删除'"
            @click="onDelete(row.item, row.index, $event)" />
      </template>
    </b-table>

    <b-pagination
        id="bottom_pagination"
        :total-rows="itemCount" :per-page="perPage"
        :limit="10"
        v-model="currentPage"
        align="center" />

    <b-modal
        id="editModal" ref="editModal"
        size="lg"
        centered
        hide-header-close
        :ok-variant="editModal.okVariant"
        cancel-title="取消"
        :cancel-disabled="editModal.inProgress"
        :ok-disabled="editModal.inProgress"
        :no-close-on-backdrop="editModal.inProgress"
        :no-close-on-esc="editModal.inProgress"
        @ok.prevent="onEditSubmit"
        @shown="$refs.editModal_value.focus()"
        @hidden="editModal.key = ''"
        lazy>
    <b-form ref="editModal_form" @submit.prevent="onEditSubmit">
      <b-form-group label="标签" label-for="editModal_key">
        <b-input id="editModal_key" ref="editModal_key" v-model="editModal.key" :disabled="!editModal.isCreate" />
      </b-form-group>
      <b-form-group label="值" label-for="editModal_value">
        <b-textarea
            id="editModal_value"
            ref="editModal_value"
            v-model="editModal.value"
            rows="8"
            no-resize
            @keydown.enter="e => (e.ctrlKey) ? onEditSubmit() : null" />
        </b-form-group>
      </b-form>

      <template slot="modal-header">
        <h5 class="modal-title" v-text="editModal.isCreate ? '创建标签' : '编辑标签'" />
        <b-button
            variant="text"
            size="sm"
            style="color:lightgray"
            v-text="editModal.isCreate ? '切换至编辑模式' : '切换至创建模式'"
            @click="editModal.isCreate = !editModal.isCreate" />
      </template>

      <template slot="modal-ok">
        <b-spinner v-if="editModal.inProgress" small />
        <span v-else v-text="editModal.okVariant == 'success' ? '完成' : '提交'" />
      </template>
    </b-modal>

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
      selectAll: false,
      editModal: { key: '', value: '', isCreate: true, inProgress: false, okVariant: 'primary' }
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
    },
    allItemSelected () {
      if (this.items.length === 0) {
        return false
      }
      let allDeleted = true
      for (let index in this.items) {
        if (this.items[index].deleted !== true) {
          allDeleted = false
          if (this.items[index].selected !== true) {
            return false
          }
        }
      }
      return !allDeleted
    }
  },
  watch: {
    currentPage () {
      if (this.currentPage <= 0) {
        this.currentPage = 1
        return
      }
      this.loadItems(true)
    },
    perPage () {
      this.currentPage = 1
      this.loadItems(true)
    },
    allItemSelected (val) {
      this.selectAll = val
    }
  },
  mounted () {
    this.loadItems()
  },
  methods: {
    async loadItems (cacheCount = false) {
      try {
        this.isLoading = true
        let fetchCount = cacheCount === true
          ? Promise.resolve({ data: { state: 0, result: this.itemCount } }) // mocking a count request result
          : this.$parent.service.get('count')
        let result = await fetchCount
        this.itemCount = result.data.state === 0 ? Number.parseInt(result.data.result) : 0

        if (this.itemCount === 0) {
          if (this.currentPage !== 1) {
            this.currentPage = 1
            return // change on currentPage will call loadItem(cacheCount = true)
          }
          this.items = []
          return
        } else if (this.itemCount <= (this.currentPage - 1) * this.perPage) {
          this.currentPage = Math.ceil(this.itemCount / this.perPage)
          return // same with above
        }

        result = await this.$parent.service.get('page', { data: { page: this.currentPage, perPage: this.perPage, prefix: '' } })
        let pageItems = result.data.state === 0 ? result.data.result : []
        pageItems.forEach(item => { item.selected = false; item.deleted = false })
        this.items = pageItems
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
    onShow (item, index, event) {
      item = { ...item }
      delete item.selected
      delete item.deleted
      this.showInfo('查看标签', JSON.stringify(item, null, 2))
    },
    onEdit (item, index, event) {
      this.editModal.key = item.key
      this.editModal.value = item.value
      this.editModal.isCreate = false
      this.$refs.editModal.show()
    },
    async onEditSubmit () {
      this.editModal.inProgress = true
      let result = await this.$parent.service.post(this.editModal.isCreate ? 'add' : 'update', { key: this.editModal.key, value: this.editModal.value })
      switch (result.data.state) {
        case 0: {
          this.editModal.okVariant = 'success'
          setTimeout(() => (this.editModal.okVariant = 'primary'), 1500)
          this.loadItems()
          break
        }
        case 10: {
          this.showInfo('', '编辑失败，目标标签不存在，自动切换为创建标签模式', () => this.$refs.editModal_key.focus())
          this.editModal.isCreate = true
          break
        }
        case 30: {
          this.showInfo('', '创建失败，目标标签已存在，自动切换为编辑标签模式', () => this.$refs.editModal_value.focus())
          this.editModal.isCreate = false
          break
        }
        default: {
          this.showInfo('', `${this.editModal.isCreate ? '创建' : '编辑'}失败，错误码${result.data.state}`)
        }
      }
      this.editModal.inProgress = false
    },
    onDelete (item, index, event) {
      this.showConfirm('', `确认要删除\`${item.key}\`吗`, async result => {
        if (result !== true) {
          return
        }
        let rst = await this.$parent.service.post('delete', { key: item.key })
        switch (rst.data.state) {
          case 0: {
            item.deleted = true
            break
          }
          default: {
            this.showInfo('', `删除失败，错误码${rst.data.state}`)
          }
        }
      })
    },
    onMultiDelete () {
      this.showConfirm('', '确认要删除这些标签吗', async result => {
        if (result !== true) {
          return
        }
        let deleteArr = []
        for (let index in this.items) {
          let item = this.items[index]
          if (item.selected === true && item.deleted !== true) {
            deleteArr.push('' + item.key)
          }
        }
        result = await this.$parent.service.post('mdelete', { keys: JSON.stringify(deleteArr) })
        if (result.data.state === 0) {
          let map = {}
          this.items.forEach((val, index) => (map[val.key] = index))
          let failedKeys = []
          for (let key in result.data.result) {
            if (result.data.result[key] === true) {
              this.items[map[key]].deleted = true
            } else {
              failedKeys.push(key)
            }
          }
          if (failedKeys.length > 0) {
            this.showInfo('', `以下标签删除失败：\`${failedKeys.join('`，`')}\``)
          }
        } else {
          this.showInfo('', `删除失败，错误码${result.data.state}`)
        }
      })
    },
    showConfirm (title, content, callback) {
      this.$refs.confirmModal.show(title, content, callback)
    },
    showInfo (title, content, hiddenCallback = null) {
      this.$refs.infoModal.show(title, content, hiddenCallback)
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
