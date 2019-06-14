<template>
  <div>
    <b-pagination
        :total-rows="itemCount" :per-page="perPage"
        :limit="10"
        :value="currentPage" @input="to => $emit('currentPageChanged', to)"
        align="center" />

    <b-table
        ref="table"
        class="unselectable"
        :busy="busy"
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
        <b-button
            v-if="!row.item.deleted"
            variant="primary"
            @click="showModal.key = row.item.key; showModal.inProgress = showModal.jsonMode = false; onShowModalRefresh()"
            v-b-modal.showModal>查看</b-button>
        <b-button v-if="!row.item.deleted" variant="primary" @click="onEdit(row.item, row.index, $event)">编辑</b-button>
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
        :value="currentPage" @input="to => $emit('currentPageChanged', to)"
        align="center" />

    <b-modal
        id="showModal" ref="showModal"
        size="lg"
        centered
        title="查看标签"
        hide-footer
        @hidden="showModal.key = ''"
        scrollable
        lazy>
      <b-form-group label="标签" label-for="showModal_key">
        <b-input id="showModal_key" v-model="showModal.key" readonly />
      </b-form-group>
      <div role="group">
        <div style="display:flex; justify-content:space-between">
          <label>值</label>
          <div>
            <b-button
                variant="info"
                size="sm"
                :disabled="!isShownValueJson"
                @click="showModal.jsonMode = !showModal.jsonMode"
                v-text="showModal.jsonMode ? '显示原文' : '解码JSON'" />
            <b-button
                variant="info"
                size="sm"
                @click="onShowModalRefresh">刷新</b-button>
          </div>
        </div>
        <b-spinner v-if="showModal.inProgress" />
        <LiningCode v-else v-model="computedShowModalValue" />
      </div>
    </b-modal>

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
  </div>
</template>

<script>
export default {
  name: 'DataTable',
  props: {
    itemCount: Number,
    currentPage: Number,
    busy: Boolean,
    items: Array,
    perPage: Number
  },
  data () {
    return {
      fields: [
        { key: 'checkbox', label: '' },
        { key: 'keyNVal', label: '标签-值', class: 'key-and-val' },
        { key: 'operations', label: '操作' }
      ],
      selectAll: false,
      showModal: { key: '', value: '', jsonMode: false, inProgress: false },
      editModal: { key: '', value: '', isCreate: true, inProgress: false, okVariant: 'primary' }
    }
  },
  computed: {
    inheritedService () {
      let parent = this.$parent
      while (!parent.service) {
        parent = parent.$parent
      }
      return parent.service
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
    },
    isShownValueJson () {
      let val = this.showModal.value
      // case of json string directly
      if ([ '[', '{' ].indexOf(val.charAt(0)) >= 0) {
        return true
      }
      // case of json quoted by double quotes
      if (val.length > 4 && val.charAt(0) === '"' && val.charAt(val.length - 1) === '"') {
        return [ '[', '{' ].indexOf(val.charAt(1)) >= 0
      }
      return false
    },
    computedShowModalValue () {
      if (!this.showModal.jsonMode) {
        return this.showModal.value
      }
      try {
        let val = this.showModal.value
        // case of json as string like "[\"item 0\",1]"
        if (val.length > 4 && val.charAt(0) === '"' && val.charAt(val.length - 1) === '"') {
          val = JSON.parse(val)
        }
        val = JSON.parse(val)
        return JSON.stringify(val, null, 2)
      } catch (e) {
        console.error('Cannot parse json:', e)
        this.$root.showInfo('', '无法解码json，详情见console')
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.showModal.jsonMode = false
        return this.showModal.value
      }
    }
  },
  watch: {
    allItemSelected (val) {
      this.selectAll = val
    }
  },
  methods: {
    itemToHtml (index) {
      const ACCEPTED_WIDTH = 530
      let item = this.items[index]
      let val = item.value.toString()
      if (val.length > 100 || this.$root.testTextWidth(val) > ACCEPTED_WIDTH) {
        let pointer = 1
        while (this.$root.testTextWidth(val.substring(0, pointer)) < ACCEPTED_WIDTH) {
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
    async onShowModalRefresh () {
      this.showModal.inProgress = true
      let { status, result } = (await this.inheritedService.get('get', { params: { key: this.showModal.key } })).data
      switch (status) {
        case 0: {
          this.showModal.value = result
          this.items.forEach(item => {
            if (item.key === this.showModal.key) {
              item.value = result
            }
          })
          break
        }
        case 10: {
          this.$root.showInfo('', '标签不存在')
          break
        }
        default: {
          this.$root.showInfo('', `获取标签信息失败，错误码${status}`)
        }
      }
      this.showModal.inProgress = false
    },
    onEdit (item, index, event) {
      this.editModal.key = item.key
      this.editModal.value = item.value
      this.editModal.isCreate = false
      this.$refs.editModal.show()
    },
    async onEditSubmit () {
      this.editModal.inProgress = true
      let { status } = (await this.inheritedService.post(this.editModal.isCreate ? 'add' : 'update',
        { key: this.editModal.key, value: this.editModal.value })).data
      switch (status) {
        case 0: {
          this.editModal.okVariant = 'success'
          setTimeout(() => (this.editModal.okVariant = 'primary'), 1500)
          this.$emit('requestLoadItems', !this.editModal.isCreate)
          break
        }
        case 10: {
          this.$root.showInfo('', '编辑失败，目标标签不存在，自动切换为创建标签模式', () => this.$refs.editModal_key.focus())
          this.editModal.isCreate = true
          break
        }
        case 30: {
          this.$root.showInfo('', '创建失败，目标标签已存在，自动切换为编辑标签模式', () => this.$refs.editModal_value.focus())
          this.editModal.isCreate = false
          break
        }
        default: {
          this.$root.showInfo('', `${this.editModal.isCreate ? '创建' : '编辑'}失败，错误码${status}`)
        }
      }
      this.editModal.inProgress = false
    },
    onDelete (item, index, event) {
      this.$root.showConfirm('', `确认要删除\`${item.key}\`吗`, async () => {
        let { status } = (await this.inheritedService.post('delete', { key: item.key })).data
        switch (status) {
          case 0: {
            item.deleted = true
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
.key-and-val {
  width: 603px;
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
