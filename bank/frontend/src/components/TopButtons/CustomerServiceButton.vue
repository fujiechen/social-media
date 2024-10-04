<script setup>
import { useStore } from 'vuex';

const store = useStore();
const handleOpenSupportPopup = () => {
  store.commit('userSupport/openPopup');
};
</script>
<template>
  <a
    @click="handleOpenSupportPopup"
    data-bs-toggle="offcanvas"
    data-bs-target="#menu-contact-support"
    class="icon bg-white shadow-bg shadow-bg-s rounded-m"
  >
    <i class="bi bi-headset"></i>
  </a>
</template>
