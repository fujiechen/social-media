<script setup>
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';

const router = useRouter();
const { t } = useI18n({ useScope: 'global' });
</script>
<template>
  <a
    @click="router.back()"
    class="btn btn-full mx-3 gradient-red shadow-bg shadow-bg-s mb-4"
  >
    {{ t('back_button') }}
  </a>
</template>
