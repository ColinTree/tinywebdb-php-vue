<template>
  <b-card>
    <h6 slot="header" style="display:flex;margin:0;justify-content:space-between">
      <slot name="header" />
    </h6>
    <slot/>
    <template #footer>
      <slot name="footer" />
    </template>
  </b-card>
</template>

<script>
export default {
  name: 'BaseCard'
}
</script>

<style>
.card-header {
  padding: 10px 15px;
}
.card-footer {
  padding: 0;
}
</style>
