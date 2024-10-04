<script setup>
import {useGlobalStore} from "@/stores/global.store";

const globalStore = useGlobalStore();
</script>

<template>
  <van-overlay
    :show="globalStore.loading"
    z-index="9999"
  >
    <div class="wrapper" @click.stop>
      <van-loading type="spinner" />
    </div>
  </van-overlay>
</template>

<style scoped>
.wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
}
</style>
