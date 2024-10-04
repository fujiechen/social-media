<script setup>
import {computed, watch} from "vue";
import {useRoute, useRouter} from "vue-router";
import BottomNavBar from "@/components/BottomNavBar"
import {useUserStore} from "@/stores/user.store";
import LoadingSpinner from "@/components/LoadingSpinner.vue";
import CustomerService from "@/components/CustomerService";
import {useMetaStore} from "@/stores/meta.store";

const route = useRoute();
const router = useRouter();

const title = computed(() => route.meta.title);
const showBackBar = computed(() => route.meta.showBackBar);
const showBottomBar = computed(() => route.meta.showBottomBar);

const handleBack = () => {
  router.back();
};

const logoPath = `${process.env.VUE_APP_SUB_PATH}logo.png`;
const userFromLocal = JSON.parse(localStorage.getItem('video-user'));
const userStore = useUserStore();
if (userFromLocal != null && userFromLocal.access_token != null) {
  userStore.fetchUser();
}

const metaStore = useMetaStore();
metaStore.fetchMetas();

//check if param has user_share_id, then save to localStorage
watch(() => route.query.user_share_id, (userShareId) => {
  if (userShareId) {
    localStorage.setItem('video-user_share_id', String(userShareId));
  }
});

const handleBackBarHome = () => {
  if (showBackBar.value?.home) {
    router.push( showBackBar.value.home );
  } else {
    router.push({ name: 'home' });
  }
};

const themeVars = {
  cardPadding: '10px 30px',
  cardFontSize: 'var(--van-font-size-lg)',
  cardDescColor: '#9f9f9f',
};
</script>

<template>
  <van-config-provider theme="dark" :theme-vars="themeVars">
    <van-nav-bar
      v-if="showBackBar"
      :title="title"
      left-text="返回"
      left-arrow
      fixed
      @click-left="handleBack"
    >
      <template #right>
        <van-icon
          name="home-o" size="18"
          @click="handleBackBarHome"/>
      </template>
    </van-nav-bar>
    <van-nav-bar
      v-else
      :title="title"
      fixed
    >
      <template #left>
        <van-image
          width="7rem"
          :src="logoPath"
          @click="router.push({name:'home'})"
        />
      </template>
      <template #right>
        <van-icon
          name="search"
          size="24"
          @click="router.push({name:'mediaSearch'})"
        />
      </template>
    </van-nav-bar>
    <div style="margin-top: 46px;"/>
    <Suspense>
      <router-view/>
      <template #fallback>
        <div>加载中...</div>
      </template>
    </Suspense>
    <van-divider/>
    <van-row>&nbsp;</van-row>
    <van-back-top right="5vw" bottom="15vh"/>
    <BottomNavBar v-if="showBottomBar"/>
    <CustomerService/>
    <LoadingSpinner/>
  </van-config-provider>
</template>

<style>
:root:root {
  /* vant theme */
  --van-primary-color: #fc7488;
  --van-background: #212529;
  --van-cell-background: var(--van-background-2);
  --van-tag-primary-color: #fc7488;
  --van-grid-item-content-background: var(--van-background);
  --van-divider-text-color: var(--van-text-color);
  --van-button-success-background: var(--van-tag-primary-color);
  --van-button-success-border-color: var(--van-tag-primary-color);
  --van-cell-group-background: var(--van-background);
  --van-tabs-nav-background: var(--van-background);
  --van-tab-text-color: var(--van-gray-6);
  --van-text-ellipsis-action-color: var(--van-primary-color);
  --van-floating-panel-background: var(--van-background);
  --van-back-top-background: var(--van-primary-color);
  --van-badge-background: var(--van-primary-color);
  --van-dropdown-menu-background: var(--van-background);
  --van-search-background: var(--van-background);
  --van-search-content-background: var(--van-background-2);
  --van-nav-bar-background: var(--van-background);
  --van-tag-default-color: var(--van-orange-dark);
  --van-card-desc-color: var(--van-text-color-2);

  --bs-body-background: var(--van-background);

  /* video player main color */
  --plyr-color-main: #fc7488;

  /* vue3-carousel paginator setting */
  --vc-clr-primary: var(--van-primary-color);
  --vc-clr-secondary: var(--van-primary-color);
  --vc-pgn-width: 7px;
  --vc-pgn-height: 7px;
  --vc-pgn-margin: 1.5px;
  --vc-pgn-border-radius: 50%;
  --vc-pgn-background-color: var(--van-gray-1);
  --vc-pgn-active-color: #fc7488;
}

.van-grid-item__icon {
  color: var(--van-primary-color);
}

.thumbnail-img {
  display: flex;
  width: 100%;
  height: 0;
  padding-bottom: 56.25%;
  position: relative;
}

.thumbnail-img img {
  aspect-ratio: 16 / 9;
  width: 100%;
  height: auto;
  position: absolute;
  top: 0;
  left: 0;
}

.van-badge {
  word-break: keep-all;
}

</style>
