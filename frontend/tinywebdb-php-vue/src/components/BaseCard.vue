<template>
  <b-card>
    <h6 slot="header" style="margin:0">
      <slot name="header" />
    </h6>
    <slot/>
    <template slot="footer">
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
