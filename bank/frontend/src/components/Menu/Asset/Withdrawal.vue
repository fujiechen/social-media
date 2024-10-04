<script setup>
import { computed, ref, watch } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n({ useScope: 'global' });
const store = useStore();

const popupOpenTrigger = computed(() => store.state.userWithdraw.popupOpenTrigger);
const accounts = computed(() => store.state.userWithdraw.accounts);
const addresses = computed(() => store.state.userWithdraw.addresses);
const banks = computed(() => store.state.userWithdraw.banks);

const assetPageAccountId = computed(() => store.state.asset.selectAccountId);

const loadingWithdrawDescription = computed(() => store.state.settings.loadingWithdrawDescription);
const withdrawHelp = computed(() => store.state.settings.withdrawHelp);

const loadingAccounts = ref(false);
const loadingAddresses = ref(false);
const loadingBanks = ref(false);
const loadingWithdraw = ref(false);
const message = ref('');

const accountId = ref('-1');
const selectedType = ref('0'); // 0:address || 1:bank
const addressId = ref('-1');
const bankId = ref('-1');
const amount = ref('');
const comment = ref('');

const accountInfo = ref(null);

const adjustOffCanvasTop = () => {
  const notification = document.getElementById('top-notification');
  if (notification) {
    const notificationHeight = notification.offsetHeight;
    const offCanvasMenu = document.getElementById('menu-withdrawal');
    if (offCanvasMenu) {
      offCanvasMenu.style.top = `${notificationHeight}px`;
      offCanvasMenu.style['padding-bottom'] = `${notificationHeight}px`;
    }
  }
};

watch(popupOpenTrigger, () => {
  loadingAccounts.value = true;
  loadingAddresses.value = true;
  loadingBanks.value = true;
  loadingWithdraw.value = false;
  message.value = '';

  accountId.value = assetPageAccountId.value;
  selectedType.value = '0';
  addressId.value = '-1';
  bankId.value = '-1';
  amount.value = '';
  comment.value = '';

  store.dispatch('settings/getWithdrawDescription', {
    locale: locale.value,
  });

  store.dispatch('userWithdraw/getAccounts')
    .then(() => {
      loadingAccounts.value = false;
    })
    .catch(() => {
      loadingAccounts.value = false;
      message.value = t('asset.errors.get_account');
    });

  store.dispatch('userWithdraw/getBanksList')
    .then(() => {
      loadingBanks.value = false;
    })
    .catch(() => {
      loadingBanks.value = false;
      message.value = t('asset.errors.get_account');
    });

  store.dispatch('userWithdraw/getAddressList')
    .then(() => {
      loadingAddresses.value = false;
    })
    .catch(() => {
      loadingAddresses.value = false;
      message.value = t('asset.errors.get_account');
    });

  adjustOffCanvasTop();
});

watch([accountId, accounts], ([newAccountId, newAccounts]) => {
  if (newAccountId !== '-1' && newAccounts.length > 0) {
    accountInfo.value = newAccounts.find((account) => account.id === newAccountId);
  } else {
    accountInfo.value = null;
  }
});

const handleWithdraw = () => {
  loadingWithdraw.value = true;
  message.value = '';

  if (accountId.value === '-1') {
    message.value = t('asset.withdrawal.errors.accountId');
    loadingWithdraw.value = false;
  } else if (selectedType.value === '0' && addressId.value === '-1') {
    message.value = t('asset.withdrawal.errors.addressId');
    loadingWithdraw.value = false;
  } else if (selectedType.value === '1' && bankId.value === '-1') {
    message.value = t('asset.withdrawal.errors.bankId');
    loadingWithdraw.value = false;
  } else if (amount.value <= 0) {
    message.value = t('asset.withdrawal.errors.amount');
    loadingWithdraw.value = false;
  } else {
    store.dispatch('userWithdraw/withdraw', {
      amount: amount.value,
      accountId: accountId.value,
      addressId: addressId.value !== '-1' ? addressId.value : undefined,
      bankId: bankId.value !== '-1' ? bankId.value : undefined,
      comment: comment.value,
    })
      .then(
        (response) => {
          const { data } = response;
          store.commit('asset/showSuccessMessage', {
            title: t('asset.withdrawal.successMessage.purchase_title'),
            content: `${t('asset.withdrawal.successMessage.purchase_message')} ${data.amount}`,
          });
          store.commit('asset/refreshAfterOrder');

          loadingWithdraw.value = false;
          document.getElementById('close-withdrawal-menu').click();
        },
        (error) => {
          if (error?.response?.data?.errors) {
            try {
              const { errors } = error.response.data;
              [message.value] = errors[Object.keys(errors)[0]];
            } catch {
              message.value = t('asset.withdrawal.errors.general');
            }
          } else {
            message.value = t('asset.withdrawal.errors.general');
          }
          loadingWithdraw.value = false;
        },
      );
  }
};
</script>
<template>
  <div
    id="menu-withdrawal"
    style="height:100%;"
    class="offcanvas offcanvas-bottom"
  >
    <div class="d-flex mx-3 mt-3 py-1">
      <div class="align-self-center">
        <h1 class="font-20 mb-0">{{ t('asset.withdrawal.name') }}</h1>
      </div>
      <div class="align-self-center ms-auto">
        <a href="#" class="ps-4 shadow-0 me-n2" data-bs-dismiss="offcanvas">
          <i class="bi bi-x color-red-dark font-26 line-height-xl"></i>
        </a>
      </div>
    </div>
    <div
      v-if="loadingWithdrawDescription"
      class="text-center mb-3 mt-2"
    >
      <span class="spinner-border spinner-border-sm"></span>
    </div>
    <div
      v-else-if="withdrawHelp"
      class="card card-style bg-fade2-blue border border-fade-blue alert show fade p-0 mb-3 mt-2"
    >
      <div class="content my-3">
        <p class="color-blue-dark mb-0 ps-3 pe-4 line-height-s">
          <span v-html="withdrawHelp"></span>
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
      v-if="loadingAccounts || loadingAddresses || loadingBanks"
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
            v-model="accountId"
            class="form-select rounded-xs"
            id="withdraw_account"
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
            for="withdraw_account"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.choose_account_label') }}
          </label>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-wallet2 font-14"></i>
          <select
            v-model="selectedType"
            class="form-select rounded-xs"
            id="withdraw_type"
            aria-label="Floating label select example"
          >
            <option value="0" selected>{{ t('asset.withdrawal.type_cash') }}</option>
            <option value="1">{{ t('asset.withdrawal.type_wired') }}</option>
          </select>
          <label
            for="withdraw_type"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.withdrawal.choose_withdrawal_type') }}
          </label>
        </div>
        <div class="pb-3"></div>
        <div v-if="selectedType === '0'">
          <div class="form-custom form-label form-icon">
            <i class="bi bi-wallet2 font-14"></i>
            <select
              v-model="addressId"
              class="form-select rounded-xs"
              id="withdraw_address"
              aria-label="Floating label select example"
            >
              <option value="-1" selected>{{ t('asset.withdrawal.choose_address') }}</option>
              <option
                v-for="address in addresses"
                v-bind:key="address.id"
                :value="address.id"
              >
                {{ address.name }}
              </option>
            </select>
            <label
              for="withdraw_address"
              class="form-label-always-active color-highlight font-11"
            >
              {{ t('asset.withdrawal.choose_address') }}
            </label>
          </div>
          <p
            class="font-13 mt-0 mb-0 ms-1"
          >
            <i class="bi bi-house-door"></i>
            <router-link
              :to="{name:'account-address'}"
              class="ms-2"
            >
              {{ t('asset.withdrawal.manage_address') }}
            </router-link>
          </p>
        </div>
        <div v-else>
          <div class="form-custom form-label form-icon">
            <i class="bi bi-wallet2 font-14"></i>
            <select
              v-model="bankId"
              class="form-select rounded-xs"
              id="withdraw_bank"
              aria-label="Floating label select example"
            >
              <option value="-1" selected>{{ t('asset.withdrawal.choose_bank') }}</option>
              <option
                v-for="bank in banks"
                v-bind:key="bank.id"
                :value="bank.id"
              >
                {{ bank.name }}
              </option>
            </select>
            <label
              for="withdraw_bank"
              class="form-label-always-active color-highlight font-11"
            >
              {{ t('asset.withdrawal.choose_bank') }}
            </label>
          </div>
          <p
            class="font-13 mt-0 mb-0 ms-1"
          >
            <i class="bi bi-credit-card"></i>
            <router-link
              :to="{name:'account-bank'}"
              class="ms-2"
            >
              {{ t('asset.withdrawal.manage_bank') }}
            </router-link>
          </p>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="amount"
            type="number"
            class="form-control rounded-xs"
            id="withdraw_amount"
          />
          <label
            for="withdraw_amount"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.amount_label') }}
          </label>
          <span v-if="accountInfo" class="font-10">( {{ t('asset.currency') }}: {{
              accountInfo.currency.name
            }} )</span>
        </div>
        <p
          v-if="accountInfo"
          class="font-12 mt-0 mb-0"
          style="margin-left:.25rem"
        >
          <i class="bi bi-cash-stack"></i>
          {{ t('asset.avail_fund') }}: {{ accountInfo.balance }}
          ({{ t('asset.account_num') }}:{{ accountInfo.account_number }})
        </p>
        <div class="pb-2"></div>
        <div class="form-custom form-label form-icon mb-3">
          <i class="bi bi-pencil-fill font-12"></i>
          <textarea
            v-model="comment"
            id="withdraw_comment"
            class="form-control rounded-xs"
            style="height:100px!important;">
          </textarea>
          <label
            for="withdraw_comment"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.comment') }}
          </label>
        </div>
      </div>
      <a
        v-if="loadingWithdraw"
        disabled
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        <span class="spinner-border spinner-border-sm"></span>
      </a>
      <a
        v-else
        @click="handleWithdraw"
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        {{ t('asset.withdrawal.confirm_button') }}
      </a>
      <a
        id="close-withdrawal-menu"
        data-bs-dismiss="offcanvas"
        style="display: none"
      ></a>
    </template>
  </div>
</template>
