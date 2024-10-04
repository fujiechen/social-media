<script setup>
import { onBeforeMount, computed } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import { loadBrowserLanguageForGuest } from '@/helpers/language';

const { locale } = useI18n({ useScope: 'global' });
const store = useStore();

const loggedIn = computed(() => store.state.auth.status.loggedIn);
const authLocale = computed(() => store.getters['auth/locale']);

onBeforeMount(() => {
  if (loggedIn.value) {
    if (locale.value !== authLocale.value) {
      locale.value = authLocale.value;
    }
  } else {
    // setup guest language
    const guestLocale = loadBrowserLanguageForGuest();
    if (locale.value !== guestLocale) {
      locale.value = guestLocale;
    }
  }
});
</script>
<template>
  <router-view/>
</template>
