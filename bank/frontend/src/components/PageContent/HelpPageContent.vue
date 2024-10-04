<script setup>
import { computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import SimplePageTitle from '../PageTitle/SimplePageTitle.vue';
import BackButton from '../Buttons/BackButton.vue';

const { t, locale } = useI18n({ useScope: 'global' });
const store = useStore();

const loadingHelpData = computed(() => store.state.settings.loadingHelpData);
const helpData = computed(() => store.state.settings.helpData);

onMounted(() => {
  store.dispatch('settings/getHelpData', {
    locale: locale.value,
  });
});
</script>
<template>
  <SimplePageTitle :title="t('help.name')" :show-back="true"/>
  <div class="card card-style">
    <div v-if="loadingHelpData" class="content text-center">
      <span class="spinner-border spinner-border-sm"></span>
    </div>
    <div v-else-if="helpData" class="content" style="line-height: 1.7">
      <span v-html="helpData"></span>
    </div>
  </div>
  <BackButton/>
</template>
