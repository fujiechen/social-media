<script setup>
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useStore } from 'vuex';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const isForCreate = computed(() => store.state.userWithdrawAccount.isCreated);
const popupOpenTrigger = computed(() => store.state.userWithdrawAccount.popupOpenTrigger);
const editAccount = computed(() => store.getters['userWithdrawAccount/editWithdrawAccount']);

const loading = ref(false);
const message = ref('');

const name = ref('');
const phone = ref('');
const accountNumber = ref('');
const bankName = ref('');
const bankAddress = ref('');
const comment = ref('');

const adjustOffCanvasTop = () => {
  const notification = document.getElementById('top-notification');
  if (notification) {
    const notificationHeight = notification.offsetHeight;
    const offCanvasMenu = document.getElementById('menu-bank-account');
    if (offCanvasMenu) {
      offCanvasMenu.style.top = `${notificationHeight}px`;
      offCanvasMenu.style['padding-bottom'] = `${notificationHeight}px`;
    }
  }
};

watch(popupOpenTrigger, () => {
  loading.value = false;
  message.value = '';

  if (!isForCreate.value && editAccount.value) {
    name.value = editAccount.value.name;
    accountNumber.value = editAccount.value.account_number;
    phone.value = editAccount.value.phone;
    bankName.value = editAccount.value.bank_name;
    bankAddress.value = editAccount.value.bank_address;
    comment.value = editAccount.value.comment;
  } else {
    name.value = '';
    phone.value = '';
    bankName.value = '';
    accountNumber.value = '';
    bankAddress.value = '';
    comment.value = '';
  }

  adjustOffCanvasTop();
});

const handleCreate = () => {
  loading.value = true;
  message.value = '';

  if (name.value.length === 0) {
    message.value = t('account.bank_manage.errors.missing_name');
    loading.value = false;
  } else if (phone.value.length === 0) {
    message.value = t('account.bank_manage.errors.missing_phone');
    loading.value = false;
  } else if (accountNumber.value.length === 0) {
    message.value = t('account.bank_manage.errors.missing_accountNumber');
    loading.value = false;
  } else if (bankName.value.length === 0) {
    message.value = t('account.bank_manage.errors.missing_bankName');
    loading.value = false;
  } else if (bankAddress.value.length === 0) {
    message.value = t('account.bank_manage.errors.missing_bankAddress');
    loading.value = false;
  } else {
    store.dispatch('userWithdrawAccount/create', {
      name: name.value,
      phone: phone.value,
      accountNumber: accountNumber.value,
      bankName: bankName.value,
      bankAddress: bankAddress.value,
      comment: comment.value,
    })
      .then(() => {
        name.value = '';
        phone.value = '';
        accountNumber.value = '';
        bankName.value = '';
        bankAddress.value = '';
        comment.value = '';
        loading.value = false;
        document.getElementById('close-bank-account-menu').click();
      })
      .catch((error) => {
        if (error?.response?.data?.errors) {
          try {
            const { errors } = error.response.data;
            [message.value] = errors[Object.keys(errors)[0]];
          } catch {
            message.value = t('account.bank_manage.errors.general_create');
          }
        } else {
          message.value = t('account.bank_manage.errors.general_create');
        }
        loading.value = false;
      });
  }
};

const handleEdit = () => {
  loading.value = true;
  message.value = '';

  if (name.value.length === 0) {
    message.value = t('account.bank_manage.errors.missing_name');
    loading.value = false;
  } else if (phone.value.length === 0) {
    message.value = t('account.bank_manage.errors.missing_phone');
    loading.value = false;
  } else if (accountNumber.value.length === 0) {
    message.value = t('account.bank_manage.errors.missing_accountNumber');
    loading.value = false;
  } else if (bankName.value.length === 0) {
    message.value = t('account.bank_manage.errors.missing_bankName');
    loading.value = false;
  } else if (bankAddress.value.length === 0) {
    message.value = t('account.bank_manage.errors.missing_bankAddress');
    loading.value = false;
  } else {
    store.dispatch('userWithdrawAccount/edit', {
      withdrawAccount: {
        name: name.value,
        phone: phone.value,
        accountNumber: accountNumber.value,
        bankName: bankName.value,
        bankAddress: bankAddress.value,
        comment: comment.value,
      },
    })
      .then(() => {
        name.value = '';
        phone.value = '';
        accountNumber.value = '';
        bankName.value = '';
        bankAddress.value = '';
        comment.value = '';
        loading.value = false;
        document.getElementById('close-bank-account-menu').click();
      })
      .catch((error) => {
        if (error?.response?.data?.errors) {
          try {
            const { errors } = error.response.data;
            [message.value] = errors[Object.keys(errors)[0]];
          } catch {
            message.value = t('account.bank_manage.errors.general_edit');
          }
        } else {
          message.value = t('account.bank_manage.errors.general_edit');
        }
        loading.value = false;
      });
  }
};
</script>
<template>
  <div id="menu-bank-account" class="offcanvas offcanvas-bottom offcanvas-detached rounded-m">
    <div class="menu-size" style="min-height:600px;">
      <div class="d-flex mx-3 mt-3 py-1">
        <div class="align-self-center">
          <h1 v-if="isForCreate" class="mb-0">{{ t('account.bank_manage.create_new_bank') }}</h1>
          <h1 v-else class="mb-0">{{ t('account.bank_manage.edit_bank') }}</h1>
        </div>
        <div class="align-self-center ms-auto">
          <a href="#" class="ps-4 shadow-0 me-n2" data-bs-dismiss="offcanvas">
            <i class="bi bi-x color-red-dark font-26 line-height-xl"></i>
          </a>
        </div>
      </div>
      <div class="divider divider-margins mt-3"></div>
      <div class="content mt-0">
        <div
          v-if="message"
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
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="name"
            type="text"
            class="form-control rounded-xs"
            id="withdraw_account_name"
            :placeholder="t('account.bank_manage.name_label')"
          />
          <label
            for="withdraw_account_name"
            class="form-label-always-active color-highlight font-11"
          >{{ t('account.bank_manage.name_label') }}
          </label>
          <span class="font-10">{{ t('account.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="phone"
            id="withdraw_account_phone"
            type="text"
            class="form-control rounded-xs"
            :placeholder="t('account.bank_manage.phone_label')"
          />
          <label
            for="withdraw_account_phone"
            class="form-label-always-active color-highlight font-11">
            {{ t('account.bank_manage.phone_label') }}
          </label>
          <span class="font-10">{{ t('account.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="accountNumber"
            type="text"
            class="form-control rounded-xs"
            id="withdraw_account_number"
            :placeholder="t('account.bank_manage.account_number_label')"
          />
          <label
            for="withdraw_account_number"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('account.bank_manage.account_number_label') }}
          </label>
          <span class="font-10">{{ t('account.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="bankName"
            type="text"
            class="form-control rounded-xs"
            id="withdraw_account_bank_name"
            :placeholder="t('account.bank_manage.bank_name_label')"
          />
          <label
            for="withdraw_account_bank_name"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('account.bank_manage.bank_name_label') }}
          </label>
          <span class="font-10">{{ t('account.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="bankAddress"
            type="text"
            class="form-control rounded-xs"
            id="withdraw_account_bank_address"
            :placeholder="t('account.bank_manage.bank_address_label')"
          />
          <label
            for="withdraw_account_bank_address"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('account.bank_manage.bank_address_label') }}
          </label>
          <span class="font-10">{{ t('account.required') }}</span>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="comment"
            type="text"
            class="form-control rounded-xs"
            id="withdraw_account_comment"
            :placeholder="t('account.bank_manage.comment')"
          />
          <label
            for="withdraw_account_comment"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('account.bank_manage.comment') }}
          </label>
          <span class="font-10">{{ t('account.optional') }}</span>
        </div>
      </div>
      <a
        v-if="loading"
        disabled
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        <span class="spinner-border spinner-border-sm"></span>
      </a>
      <a
        v-else-if="isForCreate"
        @click="handleCreate"
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        {{ t('account.bank_manage.create') }}
      </a>
      <a
        v-else
        @click="handleEdit"
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        {{ t('account.bank_manage.edit') }}
      </a>
      <a
        id="close-bank-account-menu"
        data-bs-dismiss="offcanvas"
        style="display: none"
      ></a>
    </div>
  </div>
</template>
