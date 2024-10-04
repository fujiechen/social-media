<script setup>
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useStore } from 'vuex';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const deleteId = computed(() => store.state.userWithdrawAccount.deleteId);
const deleteOpenTrigger = computed(() => store.state.userWithdrawAccount.deleteOpenTrigger);
const deleteWithdrawAccount = computed(() => store.getters['userWithdrawAccount/deleteWithdrawAccount']);

const loading = ref(false);
const message = ref('');

watch(deleteOpenTrigger, () => {
  loading.value = false;
  message.value = '';
});

const handleDelete = () => {
  loading.value = true;
  message.value = '';

  store.dispatch('userWithdrawAccount/delete', { id: deleteId.value })
    .then(() => {
      loading.value = false;
      document.getElementById('close-delete-bank-account-menu').click();
    })
    .catch(() => {
      loading.value = false;
      message.value = t('account.bank_manage.errors.general_delete');
    });
};
</script>
<template>
  <div id="menu-delete-bank-account"
       class="offcanvas offcanvas-bottom offcanvas-detached rounded-m">
    <div class="menu-size" style="min-height:200px;">
      <div class="d-flex mx-3 mt-3 py-1">
        <div class="align-self-center">
          <h1 class="mb-0">{{ t('account.bank_manage.delete_bank') }}</h1>
        </div>
        <div class="align-self-center ms-auto">
          <a href="#" class="ps-4 shadow-0 me-n2" data-bs-dismiss="offcanvas">
            <i class="bi bi-x color-red-dark font-26 line-height-xl"></i>
          </a>
        </div>
      </div>
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
        <p class="mb-0">{{ deleteWithdrawAccount?.name }}</p>
        <span>{{ deleteWithdrawAccount?.account_number }}</span>
        <p class="mb-0">{{ deleteWithdrawAccount?.bank_name }}</p>
        <p>{{ deleteWithdrawAccount?.comment }}</p>
      </div>
      <a
        v-if="loading"
        disabled
        class="mx-3 mb-4 btn btn-full gradient-red shadow-bg shadow-bg-s"
      >
        <span class="spinner-border spinner-border-sm"></span>
      </a>
      <a
        v-else
        @click="handleDelete"
        class="mx-3 mb-4 btn btn-full gradient-red shadow-bg shadow-bg-s"
      >
        {{ t('account.bank_manage.delete') }}
      </a>
      <a
        id="close-delete-bank-account-menu"
        data-bs-dismiss="offcanvas"
        style="display: none"
      ></a>
    </div>
  </div>
</template>
