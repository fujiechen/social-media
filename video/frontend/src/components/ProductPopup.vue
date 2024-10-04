<script setup>
import {useRouter} from "vue-router";
import {storeToRefs} from "pinia";
import {useProductStore} from "@/stores/product.store";
import {useOrdersStore} from "@/stores/order.store";
import {
  convertProductCurrencyNameToHumanReadable,
  convertProductTypeToHumanReadable,
  convertProductUserTypeToHumanReadable,
} from "@/utils";
import { computed, onUnmounted, ref, watch } from "vue";
import { useUserStore } from "@/stores/user.store";
import { redirectToBank } from "@/helpers/redirect";
import { useAuthStore } from "@/stores/auth.store";
import { showConfirmDialog } from "vant";
import { useMetaStore } from "@/stores/meta.store";

const props = defineProps({
  purchaseSuccess: {
    type: Function,
    default: () => {},
  }
});
const authStore = useAuthStore();
const userStore = useUserStore();
const {userAccounts} = storeToRefs(userStore);
const {user} = storeToRefs(authStore);
const loadingUserAccount = ref(false);

const router = useRouter();

const metaStore = useMetaStore();
const {metas} = storeToRefs(metaStore);

const productStore = useProductStore();
const {products, showProductPopup, productPopupTopic} = storeToRefs(productStore);

const orderStore = useOrdersStore();

const loggedIn = computed(() => {
  return !!authStore.user;
});

const doInstantPay = async (productId) => {
  if (!loggedIn.value) {
    await router.push({
      name: 'login',
    });
    return;
  }

  showConfirmDialog({
    title: '快速购买',
    message:
      '确认使用一键支付购买该产品吗？',
  })
    .then(async () => {
      const result = await orderStore.instantPayment(productId, 1);
      if (result) {
        props.purchaseSuccess();
      }
    })
    .catch(() => {
      // on cancel
    });
};

onUnmounted(() => {
  showProductPopup.value = false;
});

watch(showProductPopup, async (newValue, oldValue) => {
  // popup open
  if (oldValue === false && newValue === true) {
    if (loggedIn.value) {
      loadingUserAccount.value = true;
      await userStore.fetchOneUserAccount('CNY');
      await userStore.fetchOneUserAccount('COIN');
      loadingUserAccount.value = false;
    }
  } else {
    productPopupTopic.value = '购买产品';
  }
});

const cnyAccount = computed(() => {
  return userAccounts.value.find(userAccount => userAccount.currencyName === 'CNY');
});

const coinAccount = computed(() => {
  return userAccounts.value.find(userAccount => userAccount.currencyName === 'COIN');
});

const redirectToWallet = () => {
  redirectToBank(user.value.access_token, `返回${process.env.VUE_APP_NAME}: ${productPopupTopic.value}`, {currency: 'CNY'});
};

const redirectToGetMorePoints = () => {
  router.push({
    name: 'help',
  });
};

</script>

<template>
  <van-popup
    v-model:show="showProductPopup"
    position="bottom"
    closeable
    close-icon="close"
    round
    :style="{ height: '80%' }"
  >
    <div v-if="loggedIn" class="mt-5 mx-3">
      <div class="d-flex">
        <div class="d-flex flex-grow-1">
          积分余额：
          <van-loading v-if="loadingUserAccount">加载中...</van-loading>
          <span v-else class="fw-bold">{{ coinAccount?.balance || 'C0.00' }}</span>
        </div>
        <van-button
          @click="redirectToGetMorePoints"
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
      @click="redirectToGetMorePoints"
      justify="center"
      class="mt-5 mx-3"
      style="color: var(--van-primary-color) !important;"
    >
        <span
          v-if="metas.find(item => item.meta_key === 'REGISTRATION_HTML')"
          v-html="metas.find(item => item.meta_key === 'REGISTRATION_HTML').meta_value"
        ></span>
      <span v-else>
          <b>注册送积分，分享赚积分，积分解锁更多视频</b>
        </span>
    </van-row>
    <div
      v-if="products.length > 0"
      class="product-popup-container"
    >
      <van-card
        v-for="product in products"
        :key="product.id"
        :thumb="product?.thumbnail_file?.url || 'error'"
        :price="product.unit_price_formatted.substring(1)"
        :currency="product.unit_price_formatted.substring(0, 1)"
        @click="router.push({name:'product',params:{id:product.id}})"
        :centered="true"
        :title="product.name"
        :desc="product.description"
      >
        <template #tags>
          <div class="product-tag-list">
            <van-tag>
              {{ convertProductUserTypeToHumanReadable(product.product_user_type) }}
            </van-tag>
            <van-tag>
              {{ convertProductTypeToHumanReadable(product.type) }}
            </van-tag>
            <van-tag>
              {{ convertProductCurrencyNameToHumanReadable(product.currency_name) }}
            </van-tag>
          </div>
        </template>
        <template #footer>
          <van-button
            v-if="!product.media_product_bought"
            @click.stop="doInstantPay(product.id)"
            round
            type="primary"
            icon="shopping-cart-o"
            size="small"
          >
            一键支付
          </van-button>
          <van-button
            v-else
            round
            disabled
            plain
            type="primary"
            icon="shopping-cart-o"
            size="small"
          >
            已购买
          </van-button>
        </template>
      </van-card>
    </div>
    <span v-else>
      <van-empty image="search" description="未找到商品"/>
    </span>
  </van-popup>
</template>
<style>
.product-tag-list .van-tag {
  margin-right: .5em;
}

.product-popup-container {
  margin-top: 1rem;
}

.product-popup-container .van-card__title {
  font-size: 1rem;
}

.product-popup-container .van-card__desc {
  font-size: .9rem;
}

.product-popup-container .van-card__content {
  justify-content: start;
}

.product-popup-container .van-card__footer {
  margin-bottom: .2rem;
}
</style>
