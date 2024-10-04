<script setup>
import {
  computed, onMounted, ref, watch,
} from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import SendMoneyQuickSlide from '@/components/SendMoneyQuickSlide/SendMoneyQuickSlide.vue';
import AssetQuickAction from '@/components/AssetQuickAction/AssetQuickAction.vue';
import PageTitle from '@/components/PageTitle/PageTitle.vue';
import RecentActivities from '@/components/UserRecentActivities/UserRecentActivities.vue';
import AlertConstants from '@/constants/Alert';

const route = useRoute();
const router = useRouter();
const { t, locale } = useI18n({ useScope: 'global' });
const store = useStore();

const showSuccessMessage = computed(() => store.state.asset.showSuccessMessage);
const successMessage = computed(() => store.state.asset.successMessage);
const refreshTrigger = computed(() => store.state.asset.refreshTrigger);

const accountInfo = computed(() => store.getters['asset/selectedAccount']);
const accounts = computed(() => store.state.asset.accounts);
const transferEnabled = computed(() => store.getters['asset/transferEnabled']);
const loadingAssetCardImage = computed(() => store.state.settings.loadingAssetCardImage);
const assetCardImage = computed(() => store.state.settings.assetCardImage);

const loadingAccounts = ref(true);
const message = ref('');

const initLoad = () => {
  loadingAccounts.value = true;

  store.dispatch('asset/getAccounts')
    .then(() => {
      loadingAccounts.value = false;

      if (route.query.currency && accounts.value.length > 0) {
        const selectAccount = accounts.value.find((account) => account?.currency?.name === route.query.currency);
        if (selectAccount) {
          store.commit('asset/selectAccount', {
            id: selectAccount.id,
          });
        }
      }
    })
    .catch(() => {
      loadingAccounts.value = false;
      message.value = t('asset.errors.get_account');
    });

  store.dispatch('settings/getAssetCardImage', {
    locale: locale.value,
  });
};

const countDownCleanMessage = () => {
  setTimeout(() => {
    store.commit('asset/cleanUpSuccessMessage');
  }, AlertConstants.ASSET_SUCCESS_MESSAGE_CLOSE_COUNTDOWN_MS);
};

watch(refreshTrigger, () => {
  initLoad();
});

watch(showSuccessMessage, (newValue) => {
  if (newValue) {
    countDownCleanMessage();
  }
});

onMounted(() => {
  initLoad();
  countDownCleanMessage();
});

const handleSuccessAlertClose = () => {
  store.commit('asset/cleanUpSuccessMessage');
};

const handleOpenRecentTransactions = () => {
  router.push({ name: 'asset-recent-transaction' });
};
</script>

<template>

  <PageTitle
    :name="t('asset.name')"
  />

  <!-- Main Card Slider-->
  <div v-if="message" class="content mt-0">
    <div
      class="alert bg-fade-red color-red-dark alert-dismissible rounded-s fade show"
      role="alert"
    >
      <i class="bi bi-exclamation-triangle pe-2"></i>
      {{ message }}
      <button
        type="button"
        class="btn-close opacity-20 font-11 pt-3 mt-1"
        data-bs-dismiss="alert"
        aria-label="Close"></button>
    </div>
  </div>
  <div v-else-if="loadingAccounts || loadingAssetCardImage" class="content text-center">
    <span
      class="spinner-border spinner-border-sm"
    ></span>
  </div>
  <div v-else class="content">
    <div
      class="card card-style m-0 shadow-card shadow-card-m"
      :style="`height:200px; background-image: url(${assetCardImage});`"
    >
      <div class="card-top p-3">
        <a
          href="#"
          data-bs-toggle="offcanvas"
          data-bs-target="#asset-account-select-menu"
          class="bg-white color-black float-end rounded-1"
        >
          <strong class="font-12 m-2">{{ t('asset.switch_account') }}</strong>
        </a>
      </div>
      <div class="card-center" @click="handleOpenRecentTransactions">
        <div class="bg-theme px-3 py-2 rounded-end d-inline-block">
          <h1 class="color-theme font-13 my-n1">{{ t('asset.avail_balance') }}</h1>
          <h2 class="color-theme font-26">{{ accountInfo?.balance }}</h2>
        </div>
      </div>
      <strong
        @click="handleOpenRecentTransactions"
        class="card-top no-click font-12 p-3 color-white font-monospace"
      >
        {{ accountInfo?.currency?.name }}
      </strong>
      <strong
        @click="handleOpenRecentTransactions"
        class="card-bottom no-click p-3 text-start color-white font-monospace"
      >
        {{ accountInfo?.account_number }}
      </strong>
      <div
        @click="handleOpenRecentTransactions"
        class="card-overlay bg-black opacity-50"
      ></div>
    </div>
  </div>

  <AssetQuickAction/>

  <SendMoneyQuickSlide v-if="transferEnabled"/>

  <!-- Success Alert -->
  <div v-if="showSuccessMessage" class="card-style mb-3 mt-3">
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

  <!-- User Recent Activities-->
  <RecentActivities/>
</template>
