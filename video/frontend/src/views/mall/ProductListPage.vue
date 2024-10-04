<script setup>

import { computed, onMounted, ref, watch } from "vue";
import {storeToRefs} from "pinia";
import {useMetaStore} from "@/stores/meta.store";
import {useRoute, useRouter} from "vue-router";
import ProductList from "@/components/ProductList";
import { fetchProductList } from "@/services/product";
import { showFailToast } from "vant";
import { useAuthStore } from "@/stores/auth.store";
import { useUserStore } from "@/stores/user.store";
import { redirectToBank } from "@/helpers/redirect";

const router = useRouter();
const route = useRoute();

const metaStore = useMetaStore();
const {metas} = storeToRefs(metaStore);
const bannerUrl = ref('');

const productUserType = ref('all');
const productType = ref('all');
const currency = ref('ALL');

const product = ref({
  list: [],
  currentPage: 1,
  loading: false,
  finished: false,
});

onMounted(async () => {
  await loadProductList(true);

  if (loggedIn.value) {
    loadingUserAccount.value = true;
    await userStore.fetchOneUserAccount('CNY');
    await userStore.fetchOneUserAccount('COIN');
    loadingUserAccount.value = false;
  }

  if (route.query.user_type) {
    productUserType.value = route.query.user_type;
  }
  if (route.query.product_type) {
    productType.value = route.query.product_type;
  }
  if (route.query.currency_name) {
    currency.value = route.query.currency_name.toUpperCase();
  }
});

const loadProductList = async (reload) => {
  product.value.loading = true;

  try {
    if (reload) {
      product.value.currentPage = 1;
    }

    const params = {
      product_user_type: productUserType.value,
      type: productType.value === 'all' ? '' : productType.value,
      currency_name: currency.value === 'ALL' ? '' : currency.value,
      page: product.value.currentPage++,
      per_page: 10,
    };

    const response = await fetchProductList(params);
    product.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
    product.value.list = [
      ...product.value.list,
      ...response.data,
    ];

    bannerUrl.value = metas.value.find(m => {
      return m.meta_key === 'PRODUCTS_BANNER_URL'
    }).meta_value;
  } catch (e) {
    showFailToast('无法获取产品列表，请稍后重试！');
    product.value.finished = true;
  }

  product.value.loading = false;
}

const updateProductList = () => {
  router.push({
    name: 'products',
    query: {
      user_type: productUserType.value,
      product_type: productType.value,
      currency_name: currency.value,
    }
  });
};

watch(() => route.query, async () => {
  product.value = {
    list: [],
    currentPage: 1,
    loading: false,
    finished: false,
  };
  await loadProductList(true);
});

const productUserTypeOptions = [
  {text: '全部商品', value: 'all'},
  {text: 'Video自营', value: 'self'},
  {text: '用户店铺', value: 'user'},
];

const productTypeOptions = [
  {text: '全部类型', value: 'all'},
  {text: '媒体产品', value: 'media'},
  {text: '会员产品', value: 'membership'},
  {text: '订阅服务', value: 'subscription'},
];
const currencyOptions = [
  {text: '全部币种', value: 'ALL'},
  {text: '人民币', value: 'CNY'},
  {text: '积分', value: 'COIN'},
];

const handleBannerClick = () => {
  router.push({
    name: 'help',
    query: {
      tab: 'coop'
    },
  });
};


const authStore = useAuthStore();
const loggedIn = computed(() => {
  return !!authStore.user;
});

const userStore = useUserStore();
const {userAccounts} = storeToRefs(userStore);
const {user} = storeToRefs(authStore);
const loadingUserAccount = ref(false);

const cnyAccount = computed(() => {
  return userAccounts.value.find(userAccount => userAccount.currencyName === 'CNY');
});

const coinAccount = computed(() => {
  return userAccounts.value.find(userAccount => userAccount.currencyName === 'COIN');
});

const redirectToWallet = () => {
  redirectToBank(user.value.access_token, `返回${process.env.VUE_APP_NAME}: 商城`, {currency: 'CNY'});
};
</script>

<template>
    <van-grid :border="false" :column-num="1">
      <van-grid-item>
        <van-image
          :src="bannerUrl"
          @click="handleBannerClick"
        />
      </van-grid-item>
    </van-grid>
    <van-dropdown-menu class="product-list-dropdown">
      <van-dropdown-item v-model="productUserType" :options="productUserTypeOptions" @change="updateProductList"/>
      <van-dropdown-item v-model="productType" :options="productTypeOptions" @change="updateProductList"/>
      <van-dropdown-item v-model="currency" :options="currencyOptions" @change="updateProductList"/>
    </van-dropdown-menu>
    <van-sticky position="top" offset-top="93px">
      <div v-if="loggedIn" class="py-2 px-3" style="background-color: var(--bs-body-bg);">
        <div class="d-flex">
          <div class="d-flex flex-grow-1">
            积分余额：
            <van-loading v-if="loadingUserAccount">加载中...</van-loading>
            <span v-else class="fw-bold">{{ coinAccount?.balance || 'C0.00' }}</span>
          </div>
          <van-button
            @click="handleBannerClick"
            round
            type="primary"
            size="small"
          >
            赚取积分
          </van-button>
        </div>
        <div class="d-flex mt-3">
          <div class="d-flex flex-grow-1">
            账户余额：
            <van-loading v-if="loadingUserAccount">加载中...</van-loading>
            <span v-else class="fw-bold">{{ cnyAccount?.balance || '¥0.00' }}</span>
          </div>
          <van-button
            @click="redirectToWallet"
            round
            type="primary"
            size="small"
          >
            立即充值
          </van-button>
        </div>
      </div>
      <van-row
        v-else
        @click="handleBannerClick"
        justify="center"
        class="py-2 px-3"
        style="background-color: var(--bs-body-bg); color: var(--van-primary-color) !important;"
      >
              <span
                v-if="metas.find(item => item.meta_key === 'REGISTRATION_HTML')"
                v-html="metas.find(item => item.meta_key === 'REGISTRATION_HTML').meta_value"
              ></span>
        <span v-else>
            <b>注册送积分，分享赚积分，积分解锁更多视频</b>
            </span>
      </van-row>
    </van-sticky>

    <ProductList
      :products="product.list"
      :loading="product.loading"
      :has-more-pages="!product.finished"
      :load-next-page="() => loadProductList(false)"
    />
</template>
<style>
.product-list-dropdown {
  position: sticky;
  top: 46px;
  z-index: 100;
}
</style>
