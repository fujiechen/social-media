<script setup>
import { useRouter } from 'vue-router';

defineProps({
  title: String,
  showBack: Boolean,
});
const router = useRouter();
</script>
<template>
  <div class="pt-3">
    <div class="page-title d-flex">
      <div v-if="showBack" class="align-self-center">
        <a
          @click="router.back()"
          class="me-3 ms-0 icon icon-xxs bg-theme rounded-s shadow-m"
        >
          <i class="bi bi-chevron-left color-theme font-14"></i>
        </a>
      </div>
      <div class="align-self-center me-auto">
        <h1 class="color-theme mb-0 font-18">{{ title }}</h1>
      </div>
    </div>
  </div>
</template>
