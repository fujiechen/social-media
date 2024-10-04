<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n({ useScope: 'global' });

const handleClick = () => {
  window.close();
};
</script>
<template>
  <a
    @click="handleClick"
    class="btn btn-full mx-3 gradient-red shadow-bg shadow-bg-s"
  >
    {{ t('close_button') }}
  </a>
</template>
