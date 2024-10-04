<script setup>
import { computed } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import PageTitle from '../PageTitle/PageTitle.vue';
import HomeBanner from '../HomeBanner/HomeBanner.vue';
import SuggestProductList from '../SuggestProductList/SuggestProductList.vue';
import PersonalProductList from '../PersonalProductList/PersonalProductList.vue';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const loggedIn = computed(() => store.state.auth.status.loggedIn);
const user = computed(() => store.state.auth.user);
</script>
<template>
  <PageTitle
    :highlight-sign-in="!loggedIn"
    :highlight="loggedIn ? t('hello') + user.nickname : ''"
    :name="t('app_name')"
  />
  <HomeBanner/>
  <SuggestProductList/>
  <PersonalProductList/>
</template>
