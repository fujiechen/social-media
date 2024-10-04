<script setup>

import { storeToRefs } from "pinia";
import { useOrdersStore } from "@/stores/order.store";
import { watch, computed, ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";

const tabs = ['all', 'pending', 'completed'];
const route = useRoute();
const router = useRouter()

const orderStore = useOrdersStore();
const {orders, ordersMeta} = storeToRefs(orderStore);
const active = ref('');

watch(active, async (newStatus, oldStatus) => {
  if (newStatus !== oldStatus && tabs.includes(newStatus)) {
    orders.value = [];
    ordersMeta.value = {
      loading: true,
      currentPage: 1,
      hasMorePages: false,
    };
    await orderStore.fetchAll(newStatus);
  }
});

const all = computed(() => {
  return active.value === 'all' ? 'shopping-cart' : 'shopping-cart-o';
});
const pending = computed(() => {
  return active.value === 'pending' ? 'cart' : 'cart-o';
});
const completed = computed(() => {
  return active.value === 'completed' ? 'cart-circle' : 'cart-circle-o';
});

onMounted(async () => {
  active.value = route.query?.status || 'all';
});

const handleTabClick = async (title) => {
  active.value = title;
  const query = { ...route.query };
  delete query.status;
  await router.replace({ query })
};

</script>
<template>
  <van-grid class="order-list-page-grid" direction="horizontal" :column-num="3">
    <van-grid-item :icon="all" text="全部" clickable @click="handleTabClick('all')"/>
    <van-grid-item :icon="pending" text="待支付" clickable @click="handleTabClick('pending')"/>
    <van-grid-item :icon="completed" text="已支付" clickable @click="handleTabClick('completed')"/>
  </van-grid>
  <van-space v-if="orders.length > 0" direction="vertical" fill>
    <van-cell-group inset v-for="order in orders" :key="order.id" @click="router.push({name:'orderDetail',params:{orderId:order.id}})">
      <van-card
        v-for="orderProduct in order.order_products"
        :key="orderProduct.id"
        :num="orderProduct.qty"
        :price="orderProduct.unit_price"
        :desc="orderProduct.product.category_name"
        :title="orderProduct.product.name"
        :thumb="orderProduct.product?.thumbnail_file?.url || 'error'"
        :currency="orderProduct.product.unit_price_formatted.substring(0, 1)"
      >
      </van-card>
      <van-cell title="订单号" :value="order.id"/>
      <van-cell title="合计" :value="order.amount_formatted"/>
      <van-cell title="状态" :value="order.status_name"/>
      <van-cell title="时间" :value="order.created_at_formatted"/>
    </van-cell-group>
  </van-space>
  <div v-if="ordersMeta.loading" class="mt-3 text-center">
    <van-loading class="mb-5" vertical>加载中...</van-loading>
  </div>
  <div v-else-if="ordersMeta.hasMorePages" @click="orderStore.fetchAll(active)">
    <van-divider class="mb-5" dashed>加载更多</van-divider>
  </div>
  <van-empty
    v-else-if="orders.length === 0"
    description="您还未有订单"
  />
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

.order-list-page-grid {
  position: sticky;
  top: 46px;
  z-index: 100;
}
</style>
