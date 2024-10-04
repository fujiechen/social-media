<script setup>
import { useStore } from 'vuex';
import UserOrderConstants from '@/constants/UserOrder';
import UserOrderStatusPill from '../StatusPill/UserOrderStatusPill.vue';

const props = defineProps({
  userOrder: Object,
});

const store = useStore();

const handleShowPurchasePopup = () => {
  store.commit('userOrderHistory/openPurchasePopup', {
    userOrder: props.userOrder,
  });
};

const handleShowDepositPopup = () => {
  store.commit('userOrderHistory/openDepositPopup', {
    userOrder: props.userOrder,
  });
};
const handleShowWithdrawPopup = () => {
  store.commit('userOrderHistory/openWithdrawPopup', {
    userOrder: props.userOrder,
  });
};
const handleShowExchangePopup = () => {
  store.commit('userOrderHistory/openExchangePopup', {
    userOrder: props.userOrder,
  });
};
const handleShowTransferPopup = () => {
  store.commit('userOrderHistory/openTransferPopup', {
    userOrder: props.userOrder,
  });
};
</script>
<template>
  <a
    v-if="userOrder.type === UserOrderConstants.TYPES.PURCHASE"
    @click="handleShowPurchasePopup"
    href="#"
    data-bs-toggle="offcanvas"
    data-bs-target="#menu-purchase-detail"
    class="list-group-item"
  >
    <i class="font-28 color-green-dark bi bi-graph-up"></i>
    <div style="margin-left: .5rem">
      <h5 class="font-15 mb-1">{{ userOrder.amount }}</h5>
      <span>{{ userOrder.created_at }}</span>
    </div>
    <UserOrderStatusPill :status="userOrder.status"/>
  </a>
  <a
    v-else-if="userOrder.type === UserOrderConstants.TYPES.DEPOSIT"
    @click="handleShowDepositPopup"
    href="#"
    data-bs-toggle="offcanvas"
    data-bs-target="#menu-deposit-detail"
    class="list-group-item"
  >
    <i class="font-28 color-green-dark bi bi-arrow-up-circle"></i>
    <div style="margin-left: .5rem">
      <h5 class="font-15 mb-1">{{ userOrder.amount }}</h5>
      <span>{{ userOrder.created_at }}</span>
    </div>
    <UserOrderStatusPill :status="userOrder.status"/>
  </a>
  <a
    v-else-if="userOrder.type === UserOrderConstants.TYPES.WITHDRAW"
    @click="handleShowWithdrawPopup"
    href="#"
    data-bs-toggle="offcanvas"
    data-bs-target="#menu-withdrawal-detail"
    class="list-group-item"
  >
    <i class="font-28 color-red-dark bi bi-arrow-down-circle"></i>
    <div style="margin-left: .5rem">
      <h5 class="font-15 mb-1">{{ userOrder.amount }}</h5>
      <span>{{ userOrder.created_at }}</span>
    </div>
    <UserOrderStatusPill :status="userOrder.status"/>
  </a>
  <a
    v-else-if="userOrder.type === UserOrderConstants.TYPES.EXCHANGE"
    @click="handleShowExchangePopup"
    href="#"
    data-bs-toggle="offcanvas"
    data-bs-target="#menu-exchange-detail"
    class="list-group-item"
  >
    <i class="font-28 color-blue-dark bi bi-arrow-repeat"></i>
    <div style="margin-left: .5rem">
      <h5 class="font-15 mb-1">{{ userOrder.amount }}</h5>
      <span>{{ userOrder.created_at }}</span>
    </div>
    <UserOrderStatusPill :status="userOrder.status"/>
  </a>
  <a
    v-else-if="userOrder.type === UserOrderConstants.TYPES.TRANSFER"
    @click="handleShowTransferPopup"
    href="#"
    data-bs-toggle="offcanvas"
    data-bs-target="#menu-transfer-detail"
    class="list-group-item"
  >
    <i class="font-28 color-brown-dark bi bi-arrow-left-right"></i>
    <div style="margin-left: .5rem">
      <h5 class="font-15 mb-1">{{ userOrder.amount }}</h5>
      <span>{{ userOrder.created_at }}</span>
    </div>
    <UserOrderStatusPill :status="userOrder.status"/>
  </a>
</template>
