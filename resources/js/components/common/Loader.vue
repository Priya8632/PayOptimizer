<template>
  <div class="loader">
    <Spinner size="large" />
  </div>
</template>

<script>
export default {
  name: 'Loader'
};
</script>

<style scoped>
.loader {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
</style>
