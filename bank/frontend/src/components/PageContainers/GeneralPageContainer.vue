<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { useStore } from 'vuex';
import { initTemplate } from '@/custom';
import FooterBar from '../FooterBar/FooterBar.vue';
import MenuContainer from '../Menu/MenuContainer.vue';

const store = useStore();

/* eslint-disable */
onMounted(() => {
  initTemplate();
});

const redirectTrigger = computed(() => store.getters['auth/redirect']);

const handleRedirectClick = (event) => {
  event.preventDefault();

  const redirectUrl = redirectTrigger.value.url;
  store.commit('auth/removeRedirect');
  window.location.href = redirectUrl;
};

const adjustOffCanvasTop = () => {
  const notification = document.getElementById('top-notification');
  if (notification) {
    const notificationHeight = notification.offsetHeight;
    const mainContent = document.getElementById('page');
    if (mainContent) {
      mainContent.style.top = `${notificationHeight}px`;
      mainContent.style['padding-bottom'] = `${notificationHeight}px`;
    }
  }
};

onMounted(() => {
  adjustOffCanvasTop();
  window.addEventListener('resize', adjustOffCanvasTop);
});

onUnmounted(() => {
  window.removeEventListener('resize', adjustOffCanvasTop);
});
</script>
<template>
  <!-- Page redirect notification -->
  <div
    v-if="redirectTrigger"
    @click="handleRedirectClick"
    id="top-notification"
    class="alert alert-primary fixed-top mb-0 custom-notification"
  >
    <a
      href="#"
      class="alert-link"
    >
      <i class="bi bi-chevron-left"></i>
      {{ redirectTrigger.text }}
    </a>
  </div>
  <!-- Page Wrapper-->
  <div id="page" style="display: block">
    <!-- Footer Bar -->
    <FooterBar />
    <!-- Page Content - Only Page Elements Here-->
    <div></div>
    <div class="page-content footer-clear">
      <slot></slot>
    </div>
    <!-- End of Page Content-->
    <!-- Off Canvas and Menu Elements-->
    <!-- Always outside the Page Content-->
    <MenuContainer />
  </div>
  <!-- End of Page ID-->
</template>
<style scoped>
.custom-notification {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
</style>
