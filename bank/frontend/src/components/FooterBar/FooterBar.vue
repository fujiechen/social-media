<script setup>
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';

const route = useRoute();
const { t } = useI18n({ useScope: 'global' });
</script>
<template>
  <div id="footer-bar" class="footer-bar-1 footer-bar-detached">
    <router-link
      :to="{name:'home'}"
      :class="route.name === 'home' || route.name === 'index' ? 'active-nav' : ''"
    >
      <i class="bi bi-house-fill"></i>
      <span>{{t('footer_bar.home')}}</span>
    </router-link>
    <router-link
      :to="{name:'market'}"
      :class="route.name === 'market' ? 'active-nav' : ''"
    >
      <i class="bi bi-graph-up"></i>
      <span>{{t('footer_bar.market')}}</span>
    </router-link>
    <router-link
      :to="{name:'asset'}"
      :class="route.name === 'asset' ? 'active-nav circle-nav-2' : 'circle-nav-2'"
    >
      <i class="bi bi-wallet2"></i>
      <span>{{t('footer_bar.asset')}}</span>
    </router-link>
    <router-link
      :to="{name:'invest'}"
      :class="route.name === 'invest' ? 'active-nav' : ''"
    >
      <i class="bi bi-currency-exchange"></i>
      <span>{{t('footer_bar.invest')}}</span>
    </router-link>

    <router-link
      :to="{name:'account'}"
      :class="route.name === 'account' ? 'active-nav' : ''"
    >
      <i class="bi bi-person"></i>
      <span>{{t('footer_bar.account')}}</span>
    </router-link>
  </div>
</template>
