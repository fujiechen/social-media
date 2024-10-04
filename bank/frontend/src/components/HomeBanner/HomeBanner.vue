<script setup>
import { computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n({ useScope: 'global' });
const store = useStore();

const loadingBannerInfo = computed(() => store.state.settings.loadingBannerInfo);
const bannerInfo = computed(() => store.state.settings.bannerInfo);

onMounted(() => {
  store.dispatch('settings/getBannerInfo', {
    locale: locale.value,
  });
});

const handleBannerClick = () => {
  if (bannerInfo.value?.aboutUsUrl) {
    window.open(bannerInfo.value.aboutUsUrl, '_blank');
  }
};
</script>
<template>
  <div
    v-if="loadingBannerInfo"
    class="mb-3 mt-3 text-center"
  >
    <span class="spinner-border spinner-border-sm"></span>
  </div>
  <div
    v-else-if="bannerInfo"
    @click="handleBannerClick"
    class="card card-style"
    :style="`height:150px; background-image:url(${bannerInfo.imageUrl})`"
  >
    <div class="card-bottom p-3">
      <h1 class="color-white mb-0">{{ bannerInfo.title }}</h1>
      <p class="color-white mb-0 opacity-60">
        {{ bannerInfo.slogan }}
      </p>
    </div>
    <div class="card-overlay bg-gradient-fade"></div>
  </div>
</template>
