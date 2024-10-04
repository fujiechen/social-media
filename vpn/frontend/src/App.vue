<script setup>
import {computed, watch} from "vue";
import {useRoute, useRouter} from "vue-router";
import BottomNavBar from "@/components/BottomNavBar"
import {useUserStore} from "@/stores/user.store";
import SharePopup from "@/components/SharePopup.vue";
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

const userFromLocal = JSON.parse(localStorage.getItem('cloud-user'));
const userStore = useUserStore();
if (userFromLocal != null && userFromLocal.access_token != null) {
  userStore.fetchUser();
}

const metaStore = useMetaStore();
metaStore.fetchMetas();

//check if param has user_share_id, then save to localStorage
watch(() => route.query.user_share_id, (userShareId) => {
  if (userShareId) {
    localStorage.setItem('cloud-user_share_id', String(userShareId));
  }
});

const logoPath = `${process.env.VUE_APP_SUB_PATH}logo.png`;
</script>

<template>
  <van-config-provider theme="light">
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
          @click="router.push({name:'home'})"/>
      </template>
    </van-nav-bar>
    <van-nav-bar
      v-else
      :title="title"
      fixed
    >
      <template #left>
        <van-image
          round
          width="7rem"
          height="1.5rem"
          :src="logoPath"
          @click="router.push({name:'home'})"
        />
      </template>
      <template #right>
        <van-icon
          name="question-o" size="18"
          @click="router.push({name:'tutorial',params:{os:'share'}})"/>
      </template>
    </van-nav-bar>
    <div style="margin-top: 50px;"/>
    <Suspense>
      <router-view/>
      <template #fallback>
        <div>Loading...</div>
      </template>
    </Suspense>
    <van-divider/>
    <van-row>&nbsp;</van-row>
    <van-back-top right="10vw" bottom="15vh"/>
    <BottomNavBar v-if="showBottomBar"/>
    <SharePopup/>
    <CustomerService/>
    <LoadingSpinner/>
  </van-config-provider>
</template>

<style>
:root:root {
  --van-cell-background: #eff2f5;
}

.van-grid-item__icon {
  color: #1989fa
}

</style>
