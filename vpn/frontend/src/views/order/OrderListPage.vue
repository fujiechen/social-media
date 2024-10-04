<script setup>

import {storeToRefs} from "pinia";
import {useOrdersStore} from "@/stores/order.store";
import { watch} from "vue";
import {useRoute} from "vue-router/dist/vue-router";
import { computed } from 'vue';
import {useRouter} from "vue-router";

const route = useRoute();
const router = useRouter()

const orderStore = useOrdersStore();
const {orders} = storeToRefs(orderStore);

watch(() => route.params.status, async (newStatus) => {
  await orderStore.fetchAll(newStatus);
}, { immediate: true });

const all = computed(() => {
  return route.params.status === 'all' ? 'shopping-cart' : 'shopping-cart-o';
});
const pending = computed(() => {
  return route.params.status === 'pending' ? 'cart' : 'cart-o';
});
const completed = computed(() => {
  return route.params.status === 'completed' ? 'cart-circle' : 'cart-circle-o';
});

</script>

<template>
  <van-space direction="vertical" fill>

    <van-grid direction="horizontal" :column-num="3">
      <van-grid-item :icon="all" text="全部" clickable :to="{name:'order',params:{status:'all'}}"/>
      <van-grid-item :icon="pending" text="待支付" clickable :to="{name:'order',params:{status:'pending'}}"/>
      <van-grid-item :icon="completed" text="已支付" clickable :to="{name:'order',params:{status:'completed'}}"/>
    </van-grid>

    <template v-if="orders.length > 0">
    <van-cell-group
      v-for="order in orders"
      :key="order.id"
      @click="router.push({name:'orderDetail',params:{orderId:order.id}})"
      inset
    >
      <van-card
          v-for="orderProduct in order.order_products"
          :key="orderProduct.id"
          :num="orderProduct.qty"
          :price="orderProduct.unit_price"
          :desc="orderProduct.product.category_name"
          :title="orderProduct.product.name"
          :thumb="orderProduct.product.thumbnail_file.url"
      >
      </van-card>
      <van-cell title="订单号" :value="order.id"/>
      <van-cell title="合计" :value="order.amount_formatted"/>
      <van-cell title="状态" :value="order.status_name"/>
      <van-cell title="时间" :value="order.created_at_formatted"/>
    </van-cell-group>
    </template>
    <div v-else>
      <van-empty description="您还未有订单"/>
    </div>

  </van-space>
</template>
<style scoped>
.van-card:after {
  position: absolute;
  box-sizing: border-box;
  content: " ";
  pointer-events: none;
  right: var(--van-padding-md);
  bottom: 0;
  left: var(--van-padding-md);
  border-bottom: 1px solid var(--van-cell-border-color);
  transform: scaleY(.5);
}
.van-cell {
  background: var(--van-card-background);
}
</style>
