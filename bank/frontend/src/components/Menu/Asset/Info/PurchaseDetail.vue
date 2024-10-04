<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import UserOrderStatusPill from '@/components/StatusPill/UserOrderStatusPill.vue';
import { useStore } from 'vuex';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const userOrder = computed(() => store.state.userOrderHistory.purchaseUserOrder);
</script>
<template>
  <div id="menu-purchase-detail" class="offcanvas offcanvas-bottom offcanvas-detached rounded-m">
    <div class="menu-size" style="height:370px;">
      <div
        v-if="userOrder && Object.keys(userOrder).length > 0 && Object.getPrototypeOf(userOrder) === Object.prototype"
        class="content"
      >
        <a href="#" class="d-flex py-1 pb-4">
          <div class="align-self-center">
            <i class="font-28 color-green-dark bi bi-graph-up"></i>
          </div>
          <div class="align-self-center ps-1">
            <h5 class="pt-1 mb-n1">{{ t('asset.purchase.name') }}</h5>
            <p class="mb-0 font-11 opacity-70">{{ userOrder.created_at }}</p>
          </div>
          <div class="align-self-center ms-auto text-end">
            <UserOrderStatusPill :status="userOrder.status"/>
            <p class="mb-0 font-11">{{ userOrder.id }}</p>
          </div>
        </a>
        <div class="row">
          <strong class="col-5 color-theme">{{ t('asset.account_label') }}</strong>
          <strong class="col-7 text-end">{{ userOrder.user_account.currency.name }}</strong>
          <div class="col-12 mt-2 mb-2">
            <div class="divider my-0"></div>
          </div>
          <strong class="col-5 color-theme">{{ t('asset.account_number_label') }}</strong>
          <strong class="col-7 text-end">{{ userOrder.user_account.account_number }}</strong>
          <div class="col-12 mt-2 mb-2">
            <div class="divider my-0"></div>
          </div>
          <strong class="col-5 color-theme">{{ t('asset.amount_label') }}</strong>
          <strong class="col-7 text-end">{{ userOrder.amount }}</strong>
          <div class="col-12 mt-2 mb-2">
            <div class="divider my-0"></div>
          </div>
          <strong class="col-5 color-theme">{{ t('asset.purchase.unlock_date') }}</strong>
          <strong class="col-7 text-end">{{ userOrder.release_at }}</strong>
          <div class="col-12 mt-2 mb-2">
            <div class="divider my-0"></div>
          </div>
          <strong class="col-5 color-theme">{{ t('asset.comment') }}</strong>
          <strong class="col-7 text-end">{{ userOrder.comment }}</strong>
        </div>
      </div>
      <a href="#" data-bs-dismiss="offcanvas"
         class="mx-3 mb-4 btn btn-full gradient-highlight shadow-bg shadow-bg-s">{{
          t('asset.history.back_button')
        }}</a>
    </div>
  </div>
</template>
