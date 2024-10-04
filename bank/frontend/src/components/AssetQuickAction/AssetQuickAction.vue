<script setup>
import { computed } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const depositEnabled = computed(() => store.getters['asset/depositEnabled']);
const exchangeEnabled = computed(() => store.getters['asset/exchangeEnabled']);
const transferEnabled = computed(() => store.getters['asset/transferEnabled']);
const withdrawEnabled = computed(() => store.getters['asset/withdrawEnabled']);

const handleDepositPopupOpen = () => {
  store.commit('userDeposit/openPopup');
};

const handleWithdrawPopupOpen = () => {
  store.commit('userWithdraw/openPopup');
};

const handleExchangePopupOpen = () => {
  store.commit('userExchange/openPopup');
};

const handleTransferPopupOpen = () => {
  store.commit('userTransfer/openPopup', {});
};
</script>

<template>
  <div class="content py-2">
    <div class="d-flex text-center">
      <div
        v-if="depositEnabled"
        @click="handleDepositPopupOpen"
        class="m-auto"
        data-bs-toggle="offcanvas"
        data-bs-target="#menu-deposit"
      >
        <a href="#" class="icon icon-xxl rounded-m bg-theme shadow-m">
          <i class="font-28 color-green-dark bi bi-arrow-up-circle"></i>
        </a>
        <h6 class="font-13 opacity-80 font-500 mb-0 pt-2">{{ t('asset.deposit.name') }}</h6>
      </div>
      <div
        v-if="withdrawEnabled"
        @click="handleWithdrawPopupOpen"
        class="m-auto"
        data-bs-toggle="offcanvas"
        data-bs-target="#menu-withdrawal"
      >
        <a href="#" class="icon icon-xxl rounded-m bg-theme shadow-m">
          <i class="font-28 color-red-dark bi bi-arrow-down-circle"></i>
        </a>
        <h6 class="font-13 opacity-80 font-500 mb-0 pt-2">{{ t('asset.withdrawal.name') }}</h6>
      </div>
      <div
        v-if="exchangeEnabled"
        @click="handleExchangePopupOpen"
        data-bs-toggle="offcanvas"
        data-bs-target="#menu-exchange"
        class="m-auto"
      >
        <a href="#" class="icon icon-xxl rounded-m bg-theme shadow-m">
          <i class="font-28 color-blue-dark bi bi-arrow-repeat"></i>
        </a>
        <h6 class="font-13 opacity-80 font-500 mb-0 pt-2">{{ t('asset.exchange.name') }}</h6>
      </div>
      <div
        v-if="transferEnabled"
        @click="handleTransferPopupOpen"
        data-bs-toggle="offcanvas"
        data-bs-target="#menu-transfer"
        class="m-auto"
      >
        <a class="icon icon-xxl rounded-m bg-theme shadow-m">
          <i class="font-28 color-brown-dark bi bi-arrow-left-right"></i>
        </a>
        <h6 class="font-13 opacity-80 font-500 mb-0 pt-2">{{ t('asset.transfer.name') }}</h6>
      </div>
    </div>
  </div>
</template>
