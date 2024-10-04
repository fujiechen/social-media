<script setup>
import { computed, ref, watch } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n({ useScope: 'global' });
const store = useStore();

const popupOpenTrigger = computed(() => store.state.userExchange.popupOpenTrigger);
const accounts = computed(() => store.state.userExchange.accounts);
const exchangeInfo = computed(() => store.state.userExchange.exchangeInfo);

const assetPageAccountId = computed(() => store.state.asset.selectAccountId);

const loadingExchangeDescription = computed(() => store.state.settings.loadingExchangeDescription);
const exchangeHelp = computed(() => store.state.settings.exchangeHelp);

const loadingAccounts = ref(false);
const loadingExchangeInfo = ref(false);
const loadingExchange = ref(false);
const message = ref('');

const fromAccountId = ref('-1');
const toAccountId = ref('-1');
const amount = ref('');
const comment = ref('');

const fromAccountInfo = ref(null);
const toAccountInfo = ref(null);

const toAccounts = computed(() => accounts.value.filter((account) => account.id !== fromAccountId.value));
const toAmount = computed(() => {
  if (exchangeInfo.value.toAmount && amount.value) {
    return exchangeInfo.value.toAmount;
  }
  return null;
});

const adjustOffCanvasTop = () => {
  const notification = document.getElementById('top-notification');
  if (notification) {
    const notificationHeight = notification.offsetHeight;
    const offCanvasMenu = document.getElementById('menu-exchange');
    if (offCanvasMenu) {
      offCanvasMenu.style.top = `${notificationHeight}px`;
      offCanvasMenu.style['padding-bottom'] = `${notificationHeight}px`;
    }
  }
};

watch(popupOpenTrigger, () => {
  loadingAccounts.value = true;
  loadingExchangeInfo.value = false;
  loadingExchange.value = false;
  message.value = '';

  fromAccountId.value = assetPageAccountId.value;
  toAccountId.value = '-1';
  amount.value = '';
  comment.value = '';

  fromAccountInfo.value = null;
  toAccountInfo.value = null;

  store.dispatch('settings/getExchangeDescription', {
    locale: locale.value,
  });

  store.dispatch('userExchange/getAccounts')
    .then(() => {
      loadingAccounts.value = false;
    })
    .catch(() => {
      loadingAccounts.value = false;
      message.value = t('asset.errors.get_account');
    });

  adjustOffCanvasTop();
});

watch([fromAccountId, accounts], ([newAccountId, newAccounts]) => {
  if (newAccountId !== '-1' && newAccounts.length > 0) {
    fromAccountInfo.value = newAccounts.find((account) => account.id === newAccountId);
  } else {
    fromAccountInfo.value = null;
  }
});

watch([toAccountId, accounts], ([newAccountId, newAccounts]) => {
  if (newAccountId !== '-1' && newAccounts.length > 0) {
    toAccountInfo.value = newAccounts.find((account) => account.id === newAccountId);
  } else {
    toAccountInfo.value = null;
  }
});

const getNewExchangeRate = () => {
  message.value = '';
  if (fromAccountInfo.value && toAccountInfo.value && amount.value && !Number.isNaN(amount.value)) {
    loadingExchangeInfo.value = true;
    store.dispatch('userExchange/getExchangeInfo', {
      amount: amount.value,
      fromCurrencyId: fromAccountInfo.value.currency.id,
      toCurrencyId: toAccountInfo.value.currency.id,
    })
      .then(() => {
        loadingExchangeInfo.value = false;
      })
      .catch(() => {
        loadingExchangeInfo.value = false;
        message.value = t('asset.exchange.errors.getExchangeInfo');
      });
  } else if (fromAccountInfo.value && toAccountInfo.value) {
    loadingExchangeInfo.value = true;
    store.dispatch('userExchange/getExchangeInfo', {
      amount: 1,
      fromCurrencyId: fromAccountInfo.value.currency.id,
      toCurrencyId: toAccountInfo.value.currency.id,
    })
      .then(() => {
        loadingExchangeInfo.value = false;
      })
      .catch(() => {
        loadingExchangeInfo.value = false;
        message.value = t('asset.exchange.errors.getExchangeInfo');
      });
  }
};

watch([fromAccountInfo, toAccountInfo], () => {
  getNewExchangeRate();
});

const handleAmountOnBlue = () => {
  getNewExchangeRate();
};

const handleExchange = () => {
  loadingExchange.value = true;
  message.value = '';

  if (fromAccountId.value === '-1') {
    message.value = t('asset.exchange.errors.fromAccountId');
    loadingExchange.value = false;
  } else if (toAccountId.value === '-1') {
    message.value = t('asset.exchange.errors.toAccountId');
    loadingExchange.value = false;
  } else if (amount.value <= 0) {
    message.value = t('asset.exchange.errors.amount');
    loadingExchange.value = false;
  } else {
    store.dispatch('userExchange/exchange', {
      amount: amount.value,
      fromUserAccountId: fromAccountId.value,
      toUserAccountId: toAccountId.value,
      comment: comment.value,
    })
      .then(
        (response) => {
          const { data } = response;
          store.commit('asset/showSuccessMessage', {
            title: t('asset.exchange.successMessage.purchase_title'),
            content: `${t('asset.exchange.successMessage.purchase_message')} ${data.amount}`,
          });
          store.commit('asset/refreshAfterOrder');

          loadingExchange.value = false;
          document.getElementById('close-exchange-menu').click();
        },
        (error) => {
          if (error?.response?.data?.errors) {
            try {
              const { errors } = error.response.data;
              [message.value] = errors[Object.keys(errors)[0]];
            } catch {
              message.value = t('asset.loadingExchange.errors.general');
            }
          } else {
            message.value = t('asset.loadingExchange.errors.general');
          }
          loadingExchange.value = false;
        },
      );
  }
};
</script>
<template>
  <div
    id="menu-exchange"
    style="height:100%;"
    class="offcanvas offcanvas-bottom"
  >
    <div class="d-flex mx-3 mt-3 py-1">
      <div class="align-self-center">
        <h1 class="font-20 mb-0">{{ t('asset.exchange.name') }}</h1>
      </div>
      <div class="align-self-center ms-auto">
        <a href="#" class="ps-4 shadow-0 me-n2" data-bs-dismiss="offcanvas">
          <i class="bi bi-x color-red-dark font-26 line-height-xl"></i>
        </a>
      </div>
    </div>
    <div
      v-if="loadingExchangeDescription"
      class="text-center mb-3 mt-2"
    >
      <span class="spinner-border spinner-border-sm"></span>
    </div>
    <div
      v-else-if="exchangeHelp"
      class="card card-style bg-fade2-blue border border-fade-blue alert show fade p-0 mb-3 mt-2"
    >
      <div class="content my-3">
        <p class="color-blue-dark mb-0 ps-3 pe-4 line-height-s">
          <span v-html="exchangeHelp"></span>
        </p>
      </div>
    </div>
    <div class="divider divider-margins mt-3"></div>
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
    <template
      v-if="loadingAccounts"
    >
      <p
        class="font-12 mt-0 mb-0 text-center"
        style="margin-left:.25rem"
      >
      <span
        class="spinner-border spinner-border-sm"
      ></span>
      </p>
      <div class="pb-3"></div>
    </template>
    <template v-else>
      <div class="content mt-0">
        <div class="form-custom form-label form-icon">
          <i class="bi bi-wallet2 font-14"></i>
          <select
            v-model="fromAccountId"
            class="form-select rounded-xs"
            id="exchange_from_account"
            aria-label="Floating label select example"
          >
            <option value="-1" selected>{{ t('asset.choose_account_label') }}</option>
            <option
              v-for="account in accounts"
              v-bind:key="account.id"
              :value="account.id"
            >
              {{ account.account_number }}
            </option>
          </select>
          <label
            for="exchange_from_account"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.exchange.from_account') }}
          </label>
        </div>
        <p
          v-if="fromAccountInfo"
          class="font-12 mt-0 mb-0"
          style="margin-left:.25rem"
        >
          <i class="bi bi-cash-stack"></i>
          {{ t('asset.avail_fund') }}: {{ fromAccountInfo.balance }}
          ({{ t('asset.account_num') }}:{{ fromAccountInfo.account_number }})
        </p>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-wallet2 font-14"></i>
          <select
            v-model="toAccountId"
            class="form-select rounded-xs"
            id="exchange_to_account"
            aria-label="Floating label select example"
          >
            <option value="-1" selected>{{ t('asset.choose_account_label') }}</option>
            <option
              v-for="account in toAccounts"
              v-bind:key="account.id"
              :value="account.id"
            >
              {{ account.account_number }}
            </option>
          </select>
          <label
            for="exchange_to_account"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.exchange.to_account') }}
          </label>
        </div>
        <p
          v-if="toAccountInfo"
          class="font-12 mt-0 mb-0"
          style="margin-left:.25rem"
        >
          <i class="bi bi-cash-stack"></i>
          {{ t('asset.avail_fund') }}: {{ toAccountInfo.balance }}
          ({{ t('asset.account_num') }}:{{ toAccountInfo.account_number }})
        </p>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="amount"
            v-on:blur="handleAmountOnBlue"
            type="number"
            class="form-control rounded-xs"
            id="exchange_amount"
          />
          <label
            for="exchange_amount"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.exchange.from_amount') }}
          </label>
          <span v-if="fromAccountInfo" class="font-10">( {{ t('asset.currency') }}: {{
              fromAccountInfo.currency.name
            }} )</span>
        </div>
        <div class="pb-3"></div>
        <p
          v-if="loadingExchangeInfo"
          class="font-12 mt-0 mb-0 text-center"
          style="margin-left:.25rem"
        >
          <span
            class="spinner-border spinner-border-sm"
          ></span>
        </p>
        <template v-else>
          <div class="form-custom form-label form-icon">
            <i class="bi bi-code-square font-14"></i>
            <input
              readonly
              type="text"
              class="form-control rounded-xs"
              id="exchange_exchange_rate"
              :value="exchangeInfo.rate"
            />
            <label
              for="exchange_exchange_rate"
              class="form-label-always-active color-highlight font-11"
            >
              {{ t('asset.exchange.rate') }}
            </label>
          </div>
          <div class="pb-3"></div>
          <div class="form-custom form-label form-icon">
            <i class="bi bi-code-square font-14"></i>
            <input
              readonly
              type="number"
              step=".01"
              class="form-control rounded-xs"
              id="exchange_exchange_amount"
              :value="toAmount"
            />
            <label
              for="exchange_exchange_amount"
              class="form-label-always-active color-highlight font-11"
            >
              {{ t('asset.exchange.to_amount') }}
            </label>
          </div>
        </template>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon mb-3">
          <i class="bi bi-pencil-fill font-12"></i>
          <textarea
            v-model="comment"
            id="exchange_comment"
            class="form-control rounded-xs"
            style="height:100px!important;">
          </textarea>
          <label
            for="exchange_comment"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.comment') }}
          </label>
        </div>
      </div>
      <a
        v-if="loadingExchange"
        disabled
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        <span class="spinner-border spinner-border-sm"></span>
      </a>
      <a
        v-else
        @click="handleExchange"
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        {{ t('asset.exchange.confirm_button') }}
      </a>
      <a
        id="close-exchange-menu"
        data-bs-dismiss="offcanvas"
        style="display: none"
      ></a>
    </template>
  </div>
</template>
