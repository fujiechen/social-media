<script setup>
import UserOrderConstants from '@/constants/UserOrder';

defineProps({
  status: String,
});

</script>

<template>
  <i
    v-if="status===UserOrderConstants.STATUS.SUCCESS"
    class="font-20 color-green-dark bi bi-check-circle"
  ></i>
  <i
    v-else-if="status===UserOrderConstants.STATUS.FAILED"
    class="font-20 color-red-dark bi bi-x-circle">
  </i>
</template>
