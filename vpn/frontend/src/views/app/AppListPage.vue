<script setup>
import {onMounted, ref, watch} from 'vue';
import {storeToRefs} from "pinia";
import {useAppStore} from "@/stores/app.store";
import {useGlobalStore} from "@/stores/global.store";
import {useRouter} from "vue-router";
import {useUserStore} from "@/stores/user.store";


const router = useRouter();
const appStore = useAppStore();
const globalStore = useGlobalStore();
const userStore = useUserStore();
const {apps, appCategories} = storeToRefs(appStore);
const {loading, connected} = storeToRefs(globalStore);
const {userCategory} = storeToRefs(userStore);

const categoryId = ref(0);
const checked = ref(false);

const loadConnectionStatus = async () => {
  const user = JSON.parse(localStorage.getItem('cloud-user'));
  if (user != null && user.access_token != null) {
    await userStore.fetchUserCategory();
  }
};

onMounted(async () => {
  loading.value = true;
  await appStore.fetchAppCategories();
  categoryId.value = appCategories.value[0].id;
  loading.value = false;
});

watch(categoryId, async (newCategoryId, oldCategoryId) => {
  if (oldCategoryId !== newCategoryId) {
    loading.value = true;
    await appStore.fetchApps(categoryId.value);
    await globalStore.updateVpnConnectedStatus();
    if (connected) {
      await loadConnectionStatus();
    }
    loading.value = false;
  }
});

const onGridItemClick = (url) => {
  window.open(url, '_blank');
};

</script>
<template>
  <van-space direction="vertical" fill>
    <van-notice-bar v-if="connected" color="#1989fa" background="#ecf9ff" left-icon="info-o">
      已连接: {{userCategory.category_name}}
      <template #right-icon>
        <router-link :to="{name:'category',params:{categoryId:userCategory.category_id}}">
          续费 ({{userCategory.valid_until_at_days }} 天到期)
        </router-link>
      </template>
    </van-notice-bar>
    <van-notice-bar v-else left-icon="info-o">
      您还未连接任何服务器
      <template #right-icon>
        <van-switch v-model="checked" inactive-color="#ee0a24" @click="router.push({name:'vpn'})"/>
      </template>
    </van-notice-bar>

    <van-tabs v-model:active="categoryId">
      <van-tab v-for="appCategory in appCategories"
               :key="appCategory.id"
               :title="appCategory.name"
               :name="appCategory.id"
      >
        <van-grid
          class="navigation-grid"
          :border="false"
          :column-num="4"
          clickable
        >
          <van-grid-item v-for="app in apps" :key="app.id" @click="onGridItemClick(app.url)">
            <van-image
              class="app-list-page-app-icon"
              :src="app.icon_file.url"
            />
            <span class="app-list-page-app-name">{{ app.name }}</span>
          </van-grid-item>
        </van-grid>
      </van-tab>
    </van-tabs>
  </van-space>
</template>
<style>
.app-list-page-app-icon {
  display: flex;
  width: 60%;
  height: 0;
  padding-bottom: 60%;
  position: relative;

}

.app-list-page-app-icon img {
  aspect-ratio: 1;
  width: 100%;
  height: 100%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  object-fit: contain;
}

.app-list-page-app-name {
  font-size: 12px;
  margin-top: 12px;
}

.navigation-grid {
  margin-top: 10px;
}
</style>
