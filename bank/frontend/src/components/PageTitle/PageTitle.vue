<script setup>
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import LanguageSwitch from '@/components/TopButtons/LanguageSwitch.vue';
import HelpButton from '@/components/TopButtons/HelpButton.vue';
import CustomerServiceButton from '@/components/TopButtons/CustomerServiceButton.vue';

const router = useRouter();

const { t } = useI18n({ useScope: 'global' });
defineProps({
  highlightSignIn: Boolean,
  highlight: String,
  hideLeft: Boolean,
  name: String,
  backLink: Object,
});

const logoPath = `${process.env.VUE_APP_SUB_PATH}logo.png`;
</script>
<template>
  <div class="pt-3">
    <div class="page-title d-flex">
      <div v-if="backLink !== undefined" class="align-self-center">
        <a
          @click="router.push(backLink)"
          class="me-3 ms-0 icon icon-xxs bg-theme rounded-s shadow-m"
        >
          <i class="bi bi-chevron-left color-theme font-14"></i>
        </a>
      </div>
      <div v-if="!hideLeft" class="align-self-center me-auto">
        <router-link v-if="highlightSignIn" :to="{name:'signIn'}" class="color-gray-dark">
          {{ t('page_title.sign_in') }}
        </router-link>
        <p v-else-if="name" class="color-gray-dark mb-1">{{ name }}</p>
        <img
          :src="logoPath"
          alt="logo"
          style="width: 150px; display: block;"
        />
      </div>
      <div class="align-self-center ms-auto d-flex">
        <CustomerServiceButton />
        <HelpButton />
        <LanguageSwitch />
      </div>
    </div>
  </div>
</template>
