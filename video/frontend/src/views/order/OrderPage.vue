<script setup>

import {storeToRefs} from "pinia";
import {useOrdersStore} from "@/stores/order.store";
import {useRoute, useRouter} from "vue-router/dist/vue-router";
import { computed, onMounted } from "vue";
import {useUserStore} from "@/stores/user.store";
import {redirectToBank} from "@/helpers/redirect";
import {useGlobalStore} from "@/stores/global.store";
import useDebounce from "@/helpers/debouncer";

const route = useRoute();
const router = useRouter();
const orderStore = useOrdersStore();
const {order} = storeToRefs(orderStore)
const userStore = useUserStore();
const {user, userAccounts} = storeToRefs(userStore)
const globalStore = useGlobalStore();

onMounted(async () => {
  await orderStore.fetchOne(route.params.orderId);
  await userStore.fetchOneUserAccount(order.value.currency_name);
})

const createPayment = async () => {
  useDebounce(await orderStore.pay());
}

const userAccount = computed(() => {
  return userAccounts.value.find(userAccount => userAccount.currencyName === order.value.currency_name);
});

const redirectToUrl = (currency) => {
  redirectToBank(user.value.access_token, `${process.env.VUE_APP_NAME}` + ': 请充值后返回继续完成订单', {currency: currency});
};

const goToProductPage = (productId) => {
  router.push({
    name: 'product',
    params: {
      id: productId,
    },
  });
};

const showBackToMineButton = computed(() => {
  return order.value.order_products.some(orderProduct => {
    return orderProduct.product && orderProduct.product.type === 'membership';
  })
});

</script>

<template>
  <van-space direction="vertical" fill>
    <van-cell-group inset>
      <van-card
        v-for="orderProduct in order.order_products"
        :key="orderProduct.id"
        :num="orderProduct.qty"
        :price="orderProduct.unit_price"
        :desc="orderProduct.product.category_name"
        :title="orderProduct.product.name"
        :thumb="orderProduct.product?.thumbnail_file?.url || 'error'"
        :currency="orderProduct.product.unit_price_formatted.substring(0, 1)"
        @click="() => goToProductPage(orderProduct.product_id)"
      />
      <van-cell title="订单号" :value="order.id"/>
      <van-cell title="合计" :value="order.amount_formatted"/>
      <van-cell title="状态" :value="order.status_name"/>
      <van-cell title="时间" :value="order.created_at_formatted"/>
    </van-cell-group>

    <div class="mx-3 my-3">
      <van-button class="w-100" plain @click="globalStore.showCustomerServicePopup = true" type="warning">
        遇到问题? 联系客服
      </van-button>
    </div>

    <div v-if="order.status === 'completed'" class="mx-3">
      <div v-if="showBackToMineButton" class="my-3" >
        <router-link :to="{name:'mine'}" class="mb-3">
          <van-button type="success" size="large" class="back-button">
            返回我的页面
          </van-button>
        </router-link>
      </div>
      <div v-else class="my-3" >
        <van-button
          type="success"
          size="large"
          class="back-button"
          @click="() => goToProductPage(order.order_products[0].product_id)"
        >
          返回商品页面
        </van-button>
      </div>

      <router-link :to="{name:'home'}">
        <van-button type="primary" size="large" class="back-button">
          返回首页
        </van-button>
      </router-link>
    </div>

    <div v-else>
      <van-submit-bar
        :price="order.total_amount * 100"
        button-text="支付"
        @submit="createPayment"
        :currency="order.currency_name === 'CNY' ? '¥' : 'C'"
      >
        <van-action-bar-icon icon="home-o" text="首页" :to="{name:'home'}"/>
        <template #tip>
          <van-notice-bar left-icon="info-o">
            {{ order.currency_name }}余额: {{ userAccount?.balance || '0.00' }}
            <template #right-icon>
              <span v-if="order.currency_name === 'CNY'">
                <a @click="redirectToUrl('CNY')">充值</a>
              </span>
              <span v-else>
                <a @click="redirectToUrl('COIN')">获得更多积分</a>
              </span>
            </template>
          </van-notice-bar>
        </template>
      </van-submit-bar>
    </div>
  </van-space>
</template>
