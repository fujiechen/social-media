<script setup>
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useStore } from 'vuex';

const { t, locale } = useI18n({ useScope: 'global' });
const store = useStore();

const popupOpenTrigger = computed(() => store.state.userSupport.popupOpenTrigger);

const loading = computed(() => store.state.settings.loadingCustomerServiceImg);
const customerServiceImg = computed(() => store.state.settings.customerServiceImg);

const adjustOffCanvasTop = () => {
  const notification = document.getElementById('top-notification');
  if (notification) {
    const notificationHeight = notification.offsetHeight;
    const offCanvasMenu = document.getElementById('menu-contact-support');
    if (offCanvasMenu) {
      offCanvasMenu.style.top = `${notificationHeight}px`;
      offCanvasMenu.style['padding-bottom'] = `${notificationHeight}px`;
    }
  }
};

watch(popupOpenTrigger, () => {
  store.dispatch('settings/getCustomerServiceImg', {
    locale: locale.value,
  });

  adjustOffCanvasTop();
});

</script>
<template>
  <div id="menu-contact-support" class="offcanvas offcanvas-bottom offcanvas-detached rounded-m">
    <div class="menu-size" style="min-height:400px;">
      <div class="d-flex mx-3 mt-3 py-1">
        <div class="align-self-center">
          <h1 class="mb-0">{{ t('contact_support.title') }}</h1>
        </div>
        <div class="align-self-center ms-auto">
          <a href="#" class="ps-4 shadow-0 me-n2" data-bs-dismiss="offcanvas">
            <i class="bi bi-x color-red-dark font-26 line-height-xl"></i>
          </a>
        </div>
      </div>
      <div class="divider divider-margins mt-2 mb-2"></div>
      <div class="content mt-0">
        <div class="pb-3"></div>
        <div class="pb-2"></div>
        <div
          v-if="loading || !customerServiceImg"
          class="d-flex justify-content-center align-items-center pt-5"
        >
          <span class="spinner-border spinner-border-lg"></span>
        </div>
        <div v-else>
          <img :src="customerServiceImg" alt="123" class="w-100 h-auto" />
        </div>
      </div>
    </div>
  </div>
</template>
