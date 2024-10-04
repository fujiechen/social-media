<script setup>
import { computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import NonAuthPageContainer from '@/components/PageContainers/NonAuthPageContainer.vue';
import SimplePageTitle from '@/components/PageTitle/SimplePageTitle.vue';
import CloseTabButton from '@/components/Buttons/CloseTabButton.vue';

const { t, locale } = useI18n({ useScope: 'global' });
const store = useStore();

const loadingTacData = computed(() => store.state.settings.loadingTacData);
const tacData = computed(() => store.state.settings.tacData);

onMounted(() => {
  store.dispatch('settings/getTacData', {
    locale: locale.value,
  });
});
</script>

<template>
  <NonAuthPageContainer>
    <SimplePageTitle :title="t('termAndConditions.name')" :show-back="false"/>
    <div class="card card-style">
      <div v-if="loadingTacData" class="content text-center">
        <span class="spinner-border spinner-border-sm"></span>
      </div>
      <div v-else-if="tacData" class="content">
        <span v-html="tacData"></span>
      </div>
    </div>
    <CloseTabButton/>
  </NonAuthPageContainer>
</template>
