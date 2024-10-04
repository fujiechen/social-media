<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import PageTitle from '../PageTitle/PageTitle.vue';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();
const router = useRouter();

const user = computed(() => store.state.auth.user);

const loadingLogout = ref(false);

const handleLogout = async () => {
  loadingLogout.value = true;
  await store.dispatch('auth/logout');
  await router.push({ name: 'signIn' });
  window.location.reload();
};

const handleOpenSupportPopup = () => {
  store.commit('userSupport/openPopup');
};
</script>
<template>

  <PageTitle
    :hide-left="true"
  />
  <div class="text-center">
    <span class="icon icon-xxl rounded-m bg-green-dark shadow-m">{{ user.nickname.charAt(0) }}</span>
    <h2 class="mt-2">{{ user.nickname }}</h2>
  </div>
  <div class="pt-2 mt-4"></div>
  <div class="card card-style overflow-visible">
    <span class="mx-auto rounded-circle shadow-l"></span>
    <div class="content mt-0 mb-2">
      <div class="list-group list-custom list-group-flush list-group-m rounded-xs">
        <router-link
          :to="{name:'account-info-edit'}"
          class="list-group-item"
        >
          <i class="bi bi-person-circle"></i>
          <div>{{ t('account.personal_info') }}</div>
          <i class="bi bi-chevron-right"></i>
        </router-link>
        <router-link
          :to="{name:'account-social-edit'}"
          class="list-group-item"
        >
          <i class="bi bi-telegram"></i>
          <div>{{ t('account.social_network') }}</div>
          <i class="bi bi-chevron-right"></i>
        </router-link>
        <router-link
          :to="{name:'account-security-edit'}"
          class="list-group-item"
        >
          <i class="bi bi-key"></i>
          <div>{{ t('account.account_security') }}</div>
          <i class="bi bi-chevron-right"></i>
        </router-link>
<!--        <router-link-->
<!--          :to="{name:'account-address'}"-->
<!--          class="list-group-item"-->
<!--        >-->
<!--          <i class="bi bi-mailbox"></i>-->
<!--          <div>{{ t('account.your_address') }}</div>-->
<!--          <i class="bi bi-chevron-right"></i>-->
<!--        </router-link>-->
<!--        <router-link-->
<!--          :to="{name:'account-bank'}"-->
<!--          class="list-group-item"-->
<!--        >-->
<!--          <i class="bi bi-credit-card"></i>-->
<!--          <div>{{ t('account.your_bank_account') }}</div>-->
<!--          <i class="bi bi-chevron-right"></i>-->
<!--        </router-link>-->
      </div>
    </div>
  </div>
  <div
    @click="handleOpenSupportPopup"
    data-bs-toggle="offcanvas"
    data-bs-target="#menu-contact-support"
    class="btn btn-full mx-3 gradient-highlight shadow-bg shadow-bg-xs"
  >
    {{ t('account.contact_support') }}
  </div>
  <a
    v-if="loadingLogout"
    class="btn btn-full mx-3 gradient-red shadow-bg shadow-bg-xs mt-4"
  >
    <span
      class="spinner-border spinner-border-sm"
    ></span>
  </a>
  <a
    v-else
    @click="handleLogout"
    class="btn btn-full mx-3 gradient-red shadow-bg shadow-bg-xs mt-4"
  >
    {{ t('account.sign_out') }}
  </a>
</template>
