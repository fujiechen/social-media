<script setup>
import { computed, onBeforeMount } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import AccountPageContent from '../components/PageContent/AccountPageContent.vue';
import GeneralPageContainer from '../components/PageContainers/GeneralPageContainer.vue';

const { locale } = useI18n({ useScope: 'global' });
const store = useStore();
const router = useRouter();

const loggedIn = computed(() => store.state.auth.status.loggedIn);
onBeforeMount(() => {
  if (!loggedIn.value) {
    router.push({ name: 'signIn' });
  } else if (store.state.auth.user.language !== locale.value) {
    locale.value = store.state.auth.user.language;
  }
});
</script>

<template>
  <GeneralPageContainer>
    <AccountPageContent v-if="loggedIn"/>
  </GeneralPageContainer>
</template>
