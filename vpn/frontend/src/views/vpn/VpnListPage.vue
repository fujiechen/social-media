<script setup>
import { ref, onMounted, watch, computed } from "vue";
import {storeToRefs} from "pinia";
import {useUserStore} from "@/stores/user.store";
import {useCategoriesStore} from "@/stores/category.store";
import {useRoute, useRouter} from "vue-router";
import {useGlobalStore} from "@/stores/global.store";
import {useOrdersStore} from "@/stores/order.store";
import { showFailToast, showLoadingToast, showSuccessToast } from "vant";
import CopyToClipboardSvg from "@/components/CopyToClipboardSvg.vue";

const router = useRouter();
const route = useRoute();
const userStore = useUserStore();
const categoryStore = useCategoriesStore();
const {categories} = storeToRefs(categoryStore);
const {servers} = storeToRefs(userStore);
const globalStore = useGlobalStore();
const orderStore = useOrdersStore();

const active = ref(0);

const redirect = (redirectObj) => {
  router.push(redirectObj);
}

onMounted(async () => {
  if (orderStore.waitingForVpnSetup) {
    orderStore.waitingForVpnSetup = false;
    showLoadingToast({
      duration: 8000,
      forbidClick: true,
      message: '正在创建服务器',
      wordBreak: "break-word"
    });

    setInterval(() => {
      location.reload();
    }, 3000)
  }
  globalStore.loading = true;

  if (route.query.categoryId) {
    active.value = parseInt(route.query.categoryId);
  }

  await categoryStore.fetchAll();

  globalStore.loading = false;
});

watch(active, async (newActive) => {
  if (newActive) {
    globalStore.loading = true;
    await userStore.fetchServers(newActive);
    globalStore.loading = false;
  }
});

const refreshPage = () => {
  location.reload();
}

const serverTypes = computed(() => [...new Set(servers.value.map(server => server.server_type))].map((typeName) => {
  let title = '普通渠道';
  switch (typeName) {
    case 'Openvpn':
      title = 'Openvpn(需安装App)';
      break;
    case 'IPsec':
      title = 'IPsec(无需安装App)';
      break;
  }

  return {
    title: title,
    name: typeName,
  };
}));
const activeServerType = ref('Openvpn');
const serverList = computed(() => servers.value.filter((server) => server.server_type === activeServerType.value));

const copyContentToClipBoard = async (text) => {
  try {
    await navigator.clipboard.writeText(text);
    showSuccessToast('复制成功，请粘贴到需要填写的位置')
  } catch (err) {
    showFailToast('无法复制到剪贴板，请手动输入');
  }
}
</script>

<template>
  <van-space direction="vertical" fill>
    <van-steps active="2">
      <van-step @click="redirect({name:'home'})">购买服务</van-step>
      <van-step @click="redirect({name:'tutorial',params:{os:'auto'}})">
        查阅教程
      </van-step>
      <van-step>配置VPN</van-step>
      <van-step @click="redirect({name:'app'})">
        连接成功
      </van-step>
    </van-steps>
    <van-tabs v-model:active="active">
      <van-row>&nbsp;</van-row>
      <van-tabs
        v-if="serverTypes.length"
        v-model:active="activeServerType"
        type="card"
      >
        <van-tab
          v-for="serverType in serverTypes"
          :key="serverType.name"
          :title="serverType.title"
          :name="serverType.name"
        >
          <van-row>&nbsp;</van-row>
          <van-notice-bar
            v-if="serverType.name === 'IPsec'"
            mode="link"
            @click="router.push({name:'tutorial',params:{os:'auto'}})"
          >
            注意：使用此方式每日需要更换服务器地址
          </van-notice-bar>
          <van-notice-bar
            v-if="serverType.name === 'Openvpn'"
            mode="link"
            @click="router.push({name:'tutorial',params:{os:'auto'}})"
          >
            注意：使用此方式请下载App客户端
          </van-notice-bar>
        </van-tab>
      </van-tabs>
      <van-tab
        v-for="category in categories"
        :key="category.id"
        :title="category.name"
        :name="category.id"
        :to="{name:'vpn',query:{categoryId:category.id}}"
      >
        <template v-if="serverList.length > 0">
          <van-cell-group inset v-for="server in serverList" :key="server.id">
            <van-row>&nbsp;</van-row>
            <van-cell title="名称" :value="server.server_name"/>
            <van-cell title="国家" :value="server.server_country_code"/>
            <van-cell title="更新日期" :value="server.server_updated_at_formatted"/>
            <van-cell v-if="activeServerType === 'IPsec'" center title="服务器地址" @click="copyContentToClipBoard(server.server_ip)">
              <template #value>
                <span class="important-info">{{server.server_ip}}</span>
                <copy-to-clipboard-svg />
              </template>
            </van-cell>
            <van-cell center title="用户名" @click="copyContentToClipBoard(server.radius_username)">
              <template #value>
                <span class="important-info">{{server.radius_username}}</span>
                <copy-to-clipboard-svg />
              </template>
            </van-cell>
            <van-cell center title="密码" @click="copyContentToClipBoard(server.radius_password)">
              <template #value>
                <span class="important-info">{{server.radius_password}}</span>
                <copy-to-clipboard-svg />
              </template>
            </van-cell>
            <van-cell v-if="activeServerType === 'IPsec'" center title="预共享密钥" @click="copyContentToClipBoard(server.server_ipsec_shared_key)">
              <template #value>
                <span class="important-info">{{server.server_ipsec_shared_key}}</span>
                <copy-to-clipboard-svg />
              </template>
            </van-cell>
            <van-cell v-if="activeServerType === 'Openvpn'" center title="配置文件" :is-link="!!server.vpn_file.url">
              <template #value>
                <a v-if="server.vpn_file.url" class="important-info" :href="server.vpn_file.url" target="_blank">下载</a>
                <span v-else class="important-info">文件损坏</span>
              </template>
            </van-cell>
            <van-cell v-if="server.category_valid_until_at_days > 0" center title="有效期至">
              <template #value>
                <span class="important-info">{{server.category_valid_until_at_formatted}}</span>
              </template>
            </van-cell>
            <van-cell v-else center title="已过期">
              <template #value>
                <router-link
                  :to="{name:'category', params: {categoryId: server.category_id}}"
                  class="important-info"
                >
                  点击续费
                </router-link>
              </template>
            </van-cell>
          </van-cell-group>
          <van-row>&nbsp;</van-row>
        </template>
        <div v-else>
          <van-empty image="network" description="您还未开通此服务或VPN正在创建，请刷新页面">
            <van-button plain round type="default" @click="refreshPage">
              刷新列表
            </van-button>
          </van-empty>
        </div>
      </van-tab>
    </van-tabs>
    <van-row>&nbsp;</van-row>
    <van-cell-group inset>
      <van-button block round type="success" :to="{name:'tutorial',params:{os:'auto'}}">
        查阅教程
      </van-button>
      <br/>
      <van-button block plain round type="warning" @click="globalStore.showCustomerServicePopup = true">
        技术支持
      </van-button>
    </van-cell-group>
  </van-space>
</template>
<style scoped>
.refresh-page-btn {
  margin-left: 20px;
  margin-right: 20px;
}

.important-info {
  font-weight: bold;
  color: black;
}
</style>
