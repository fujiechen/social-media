<script setup>

import {storeToRefs} from "pinia";
import {useOrdersStore} from "@/stores/order.store";
import {useRoute} from "vue-router/dist/vue-router";
import {onMounted} from "vue";
import {useUserStore} from "@/stores/user.store";
import {redirectToBank} from "@/helpers/redirect";
import {useGlobalStore} from "@/stores/global.store";
import useDebounce from "@/helpers/debouncer";

const route = useRoute();
const orderStore = useOrdersStore();
const {order} = storeToRefs(orderStore)
const userStore = useUserStore();
const {user, userAccount} = storeToRefs(userStore)
const globalStore = useGlobalStore();

onMounted(async () => {
  await orderStore.fetchOne(route.params.orderId);
  await userStore.fetchOneUserAccount('CNY');
})

const createPayment = async () => {
  useDebounce(await orderStore.pay());
}

const redirectToUrl = () => {
  redirectToBank(user.value.access_token, `${process.env.VUE_APP_NAME}` + ': 请充值后返回继续完成订单');
};

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
          :thumb="orderProduct.product.thumbnail_file.url"
      />
      <van-cell title="订单号" :value="order.id"/>
      <van-cell title="合计" :value="order.amount_formatted"/>
      <van-cell title="状态" :value="order.status_name"/>
      <van-cell title="时间" :value="order.created_at_formatted"/>
    </van-cell-group>

    <van-row justify="center">
      <p>
        <van-button plain @click="globalStore.showCustomerServicePopup = true" type="warning">遇到问题? 联系客服</van-button>
      </p>
    </van-row>

    <div v-if="order.status !== 'completed'">
      <van-submit-bar :price="order.total_amount * 100" button-text="支付" @submit="createPayment">
        <van-action-bar-icon icon="home-o" text="首页" :to="{name:'home'}"/>
        <template #tip>
          <van-notice-bar left-icon="info-o">
            钱包余额 （{{ userAccount.balance }})
            <template #right-icon>
              <a @click="redirectToUrl">充值</a>
            </template>
          </van-notice-bar>
        </template>
      </van-submit-bar>
    </div>
  </van-space>
</template>
