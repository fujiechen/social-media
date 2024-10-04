<script setup>
import { onMounted } from 'vue';
import { initTemplate } from '@/custom';
/* eslint-disable */
onMounted(() => {
  initTemplate();
});
</script>
<template>
  <!-- Page Wrapper-->
  <div id="page" style="display: block">

    <!-- Page Content - Only Page Elements Here-->
    <div class="page-content my-0 py-0">
      <slot></slot>
    </div>
  </div>
  <!-- End of Page ID-->
</template>
