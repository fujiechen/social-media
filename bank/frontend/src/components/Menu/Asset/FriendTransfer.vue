<script setup>
import { computed, ref, watch } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n({ useScope: 'global' });
const store = useStore();

const popupOpenTrigger = computed(() => store.state.userTransfer.popupOpenTrigger);
const accounts = computed(() => store.state.userTransfer.accounts);
const selectedFriendInfo = computed(() => store.state.userTransfer.selectedFriendInfo);

const assetPageAccountId = computed(() => store.state.asset.selectAccountId);

const loadingTransferDescription = computed(() => store.state.settings.loadingTransferDescription);
const transferHelp = computed(() => store.state.settings.transferHelp);

const loadingAccounts = ref(false);
const loadingTransfer = ref(false);
const message = ref('');

const accountId = ref('-1');
const toUserEmail = ref('');
const toUserName = ref('');
const amount = ref('');
const comment = ref('');

const accountInfo = ref(null);

const adjustOffCanvasTop = () => {
  const notification = document.getElementById('top-notification');
  if (notification) {
    const notificationHeight = notification.offsetHeight;
    const offCanvasMenu = document.getElementById('menu-transfer');
    if (offCanvasMenu) {
      offCanvasMenu.style.top = `${notificationHeight}px`;
      offCanvasMenu.style['padding-bottom'] = `${notificationHeight}px`;
    }
  }
};

watch(popupOpenTrigger, () => {
  loadingAccounts.value = true;
  loadingTransfer.value = false;
  message.value = '';

  accountId.value = assetPageAccountId.value;
  amount.value = '';
  comment.value = '';

  if (selectedFriendInfo.value.name || selectedFriendInfo.value.email) {
    toUserEmail.value = selectedFriendInfo.value.email;
    toUserName.value = selectedFriendInfo.value.name;
  } else {
    toUserEmail.value = '';
    toUserName.value = '';
  }

  store.dispatch('settings/getTransferDescription', {
    locale: locale.value,
  });

  store.dispatch('userTransfer/getAccounts')
    .then(() => {
      loadingAccounts.value = false;
    })
    .catch(() => {
      loadingAccounts.value = false;
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

const handleTransfer = () => {
  loadingTransfer.value = true;
  message.value = '';

  if (accountId.value === '-1') {
    message.value = t('asset.transfer.errors.accountId');
    loadingTransfer.value = false;
  } else if (toUserName.value === '') {
    message.value = t('asset.transfer.errors.toUserName');
    loadingTransfer.value = false;
  } else if (toUserEmail.value === '') {
    message.value = t('asset.transfer.errors.toUserEmail');
    loadingTransfer.value = false;
  } else if (amount.value <= 0) {
    message.value = t('asset.transfer.errors.amount');
    loadingTransfer.value = false;
  } else {
    store.dispatch('userTransfer/transfer', {
      amount: amount.value,
      fromUserAccountId: accountId.value,
      toUserEmail: toUserEmail.value,
      toUserName: toUserName.value,
      comment: comment.value,
    })
      .then(
        (response) => {
          const { data } = response;
          store.commit('asset/showSuccessMessage', {
            title: t('asset.transfer.successMessage.purchase_title'),
            content: `${t('asset.transfer.successMessage.purchase_message')} ${data.amount}`,
          });
          store.commit('asset/refreshAfterOrder');
          store.commit('userTransfer/refreshFriendListAfterTransfer');

          loadingTransfer.value = false;
          document.getElementById('close-transfer-menu').click();
        },
        (error) => {
          if (error?.response?.data?.errors) {
            try {
              const { errors } = error.response.data;
              if (
                'to_user_name' in errors
                && errors.to_user_name.length > 0
                && errors.to_user_name[0] === 'validation.exists'
              ) {
                message.value = t('asset.transfer.errors.toUserNameExists');
              } else if (
                'to_user_email' in errors
                && errors.to_user_email.length > 0
                && errors?.to_user_email[0] === 'validation.exists'
              ) {
                message.value = t('asset.transfer.errors.toUserEmailExists');
              } else {
                message.value = t('asset.transfer.errors.general');
              }
            } catch {
              message.value = t('asset.transfer.errors.general');
            }
          } else {
            message.value = t('asset.transfer.errors.general');
          }
          loadingTransfer.value = false;
        },
      );
  }
};

</script>
<template>
  <div
    id="menu-transfer"
    style="height:100%;"
    class="offcanvas offcanvas-bottom"
  >
    <div class="d-flex mx-3 mt-3 py-1">
      <div class="align-self-center">
        <h1 class="font-20 mb-0">{{ t('asset.transfer.name') }}</h1>
      </div>
      <div class="align-self-center ms-auto">
        <a href="#" class="ps-4 shadow-0 me-n2" data-bs-dismiss="offcanvas">
          <i class="bi bi-x color-red-dark font-26 line-height-xl"></i>
        </a>
      </div>
    </div>
    <div
      v-if="loadingTransferDescription"
      class="text-center mb-3 mt-2"
    >
      <span class="spinner-border spinner-border-sm"></span>
    </div>
    <div
      v-else-if="transferHelp"
      class="card card-style bg-fade2-blue border border-fade-blue alert show fade p-0 mb-3 mt-2"
    >
      <div class="content my-3">
        <p class="color-blue-dark mb-0 ps-3 pe-4 line-height-s">
          <span v-html="transferHelp"></span>
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
            v-model="accountId"
            class="form-select rounded-xs"
            id="transfer_account"
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
            for="transfer_account"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.choose_account_label') }}
          </label>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="toUserName"
            type="text"
            class="form-control rounded-xs"
            id="transfer_full_name"
          />
          <label
            for="transfer_full_name"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.transfer.full_name') }}
          </label>
          <span class="font-10">{{ t('asset.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="toUserEmail"
            type="email"
            class="form-control rounded-xs"
            id="transfer_email"
          />
          <label
            for="transfer_email"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.transfer.friend_email_address') }}
          </label>
          <span class="font-10">{{ t('asset.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="amount"
            type="number"
            class="form-control rounded-xs"
            id="transfer_amount"
          />
          <label
            for="transfer_amount"
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
            id="transfer_comment"
            class="form-control rounded-xs"
            style="height:100px!important;">
          </textarea>
          <label
            for="transfer_comment"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.comment') }}
          </label>
        </div>
      </div>
      <a
        v-if="loadingTransfer"
        disabled
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        <span class="spinner-border spinner-border-sm"></span>
      </a>
      <a
        v-else
        @click="handleTransfer"
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        {{ t('asset.transfer.confirm_button') }}
      </a>
      <a
        id="close-transfer-menu"
        data-bs-dismiss="offcanvas"
        style="display: none"
      ></a>
    </template>
  </div>
</template>
