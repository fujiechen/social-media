<script setup>
import { computed, onMounted, watch } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import InvestProductList from '@/components/InvestProductList/InvestProductList.vue';
import PageTitle from '@/components/PageTitle/PageTitle.vue';
import SuggestProductList from '@/components/SuggestProductList/SuggestProductList.vue';
import AlertConstants from '@/constants/Alert';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const showSuccessMessage = computed(() => store.state.invest.showSuccessMessage);
const successMessage = computed(() => store.state.invest.successMessage);

const handleSuccessAlertClose = () => {
  store.commit('invest/cleanUpSuccessMessage');
};

const countDownCleanMessage = () => {
  setTimeout(() => {
    store.commit('invest/cleanUpSuccessMessage');
  }, AlertConstants.INVEST_SUCCESS_MESSAGE_CLOSE_COUNTDOWN_MS);
};

watch(showSuccessMessage, (newValue) => {
  if (newValue) {
    countDownCleanMessage();
  }
});

onMounted(() => {
  if (showSuccessMessage.value) {
    countDownCleanMessage();
  }
});
</script>

<template>

  <PageTitle
    :name="t('invest.name')"
  />
  <div v-if="showSuccessMessage" class="card-style mb-3">
    <div
      class="alert bg-green-light shadow-bg shadow-bg-m alert-dismissible rounded-s fade show mb-0"
      role="alert">
      <i class="bi bi-check-circle-fill pe-2"></i>
      <strong>{{ successMessage.title }}</strong> <br> {{ successMessage.content }}
      <button
        @click="handleSuccessAlertClose"
        type="button"
        class="btn-close opacity-10"
        data-bs-dismiss="alert"
        aria-label="Close"
      ></button>
    </div>
  </div>
  <InvestProductList/>
  <SuggestProductList/>
</template>
