<template>
  <div style="margin-top: 5px">
    <code class="lining-code">
      <ol>
        <li v-for="(line, index) in lines" :key="index">
          <span
              class="unselectable lining-code-line-number"
              :style="{ width: lineNumWidth + 'px' }"
              v-text="index + 1"
          /><span v-text="line" />
          <!-- span need to be placed together without even a space, in order to keep the text shown correct -->
        </li>
      </ol>
    </code>
  </div>
</template>

<script>
export default {
  name: 'LiningCode',
  props: {
    value: String,
    lineNumPadding: {
      type: Number,
      default: 7
    }
  },
  computed: {
    lines () {
      return this.value.split('\n')
    },
    lineNumWidth () {
      return this.$root.testTextWidth('' + (this.lines.length - 1)) + this.lineNumPadding * 2
    }
  }
}
</script>

<style>
code.lining-code {
  color: black;
  display: block;
  border: 1px solid #ccc;
  border-radius: 4px;
  min-height: 200px;
  white-space: pre-wrap;
}
code.lining-code ol {
  padding: 0;
  margin-bottom: 0;
  width: auto;
}
code.lining-code li {
  display: flex;
}
code.lining-code li:nth-child(odd) {
  background-color: #e6e6e628;
}
code.lining-code span {
  color: #444;
  line-height: 1.6;
}
code.lining-code .lining-code-line-number {
  background-color: #f8f8f8;
  text-align: center;
  font-weight: bold;
  flex: none;
}
code.lining-code span:nth-child(2) {
  padding-left: 8px;
}
</style>
