<script setup>
import { useI18n } from 'vue-i18n';
import UserOrderConstants from '@/constants/UserOrder';

defineProps({
  status: String,
});

const { t } = useI18n({ useScope: 'global' });
</script>

<template>
  <span
    v-if="status===UserOrderConstants.STATUS.SUCCESS"
    class="badge rounded-xl bg-green-dark"
  >
    {{ t('asset.history.success') }}
  </span>
  <span
    v-else-if="status===UserOrderConstants.STATUS.PENDING"
    class="badge rounded-xl bg-blue-dark"
  >
    {{ t('asset.history.pending') }}
  </span>
  <span
    v-else-if="status===UserOrderConstants.STATUS.FAILED"
    class="badge rounded-xl bg-red-dark">
    {{ t('asset.history.declined') }}
  </span>
  <span
    v-else
    class="badge rounded-xl bg-green-dark">
    {{ status }}
  </span>
</template>
