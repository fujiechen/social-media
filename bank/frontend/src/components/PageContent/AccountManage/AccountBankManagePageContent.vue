<script setup>
import { computed, onMounted, ref } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import PageTitle from '../../PageTitle/PageTitle.vue';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const bankAccounts = computed(() => store.state.userWithdrawAccount.withdrawAccounts);

const loading = ref(true);

onMounted(() => {
  store.dispatch('userWithdrawAccount/list')
    .then(() => {
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
});

const handleCreate = () => {
  store.commit('userWithdrawAccount/openCreatePopup');
};
const handleEdit = (id) => {
  store.commit('userWithdrawAccount/openEditPopup', { id });
};
const handleDelete = (id) => {
  store.commit('userWithdrawAccount/openDeletePopup', { id });
};
</script>

<template>
  <PageTitle
    :name="t('account.bank_manage.title')"
    :back-link="{name:'account'}"
  />
  <div class="content my-0 mt-n2 px-1">
    <div class="d-flex">
      <div class="align-self-center">
        <h3 class="font-16 mb-2">{{ t('account.bank_manage.subtitle') }}</h3>
      </div>
    </div>
  </div>
  <div class="card card-style">
    <div class="content">
      <div
        v-if="loading"
        class="content mt-4 text-center"
      >
        <span
          class="spinner-border spinner-border-sm"
        ></span>
      </div>
      <div
        v-else
        class="content mt-2"
      >
        <div
          class="list-group list-custom list-group-m list-group-flush rounded-xs overflow-visible">
          <template v-if="bankAccounts.length === 0">
            <div class="text-center">
              <span>{{ t('account.bank_manage.no_accounts') }}</span>
            </div>
          </template>
          <template v-else>
            <a
              v-for="bankAccount in bankAccounts"
              v-bind:key="bankAccount.id"
              class="list-group-item"
            >
              <div
                @click="handleEdit(bankAccount.id)"
                data-bs-toggle="offcanvas"
                data-bs-target="#menu-bank-account"
              >
                <p class="mb-0">{{ bankAccount.name }}</p>
                <span>{{ bankAccount.account_number }}</span>
                <p class="mb-0">{{ bankAccount.bank_address }}</p>
                <p v-if="bankAccount.comment">{{ bankAccount.comment }}</p>
              </div>
              <i
                @click="handleDelete(bankAccount.id)"
                class="bi bi-trash font-15 color-red-dark"
                data-bs-toggle="offcanvas"
                data-bs-target="#menu-delete-bank-account"
              ></i>
            </a>
          </template>
        </div>

        <div
          @click="handleCreate"
          data-bs-toggle="offcanvas"
          data-bs-target="#menu-bank-account"
          class="btn btn-full gradient-highlight shadow-bg shadow-bg-s mt-4"
        >
          {{ t('account.bank_manage.create_new_bank') }}
        </div>
      </div>
    </div>
  </div>
</template>
