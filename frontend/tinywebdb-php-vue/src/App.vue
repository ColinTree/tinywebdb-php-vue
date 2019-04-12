<template>
  <router-view/>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style>
.unselectable {
   -moz-user-select: -moz-none;
   -khtml-user-select: none;
   -webkit-user-select: none;
   -ms-user-select: none;
   user-select: none;
}
</style>
