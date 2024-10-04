<script setup>
import { computed, onBeforeMount } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import AccountSocialManagePageContent
  from '../../components/PageContent/AccountManage/AccountSocialManagePageContent.vue';
import GeneralPageContainer from '../../components/PageContainers/GeneralPageContainer.vue';

const store = useStore();
const router = useRouter();

const loggedIn = computed(() => store.state.auth.status.loggedIn);
onBeforeMount(() => {
  if (!loggedIn.value) {
    router.push({ name: 'signIn' });
  }
});
</script>

<template>
  <GeneralPageContainer>
    <AccountSocialManagePageContent v-if="loggedIn"/>
  </GeneralPageContainer>
</template>
