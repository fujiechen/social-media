<script setup>
import {
  computed, ref, onMounted, watch,
} from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import UserOrderListItem from './UserOrderListItem.vue';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const userOrders = computed(() => store.state.userOrderHistory.userOrders);
const userOrdersNextPage = computed(() => store.state.userOrderHistory.userOrdersNextPage);
const refreshTrigger = computed(() => store.state.asset.refreshTrigger);

const loadingInitial = ref(true);
const loadingNextPage = ref(false);

const initLoad = () => {
  loadingInitial.value = true;
  loadingNextPage.value = false;

  store.dispatch('userOrderHistory/listUserOrders', {
    init: true,
  })
    .then(() => {
      loadingInitial.value = false;
    })
    .catch(() => {
      loadingInitial.value = false;
    });
};

watch(refreshTrigger, () => {
  initLoad();
});

onMounted(() => {
  initLoad();
});

const handleNextPage = () => {
  loadingNextPage.value = true;

  store.dispatch('userOrderHistory/listUserOrders', {
    init: false,
  })
    .then(
      () => {
        loadingNextPage.value = false;
      },
      () => {
        loadingNextPage.value = false;
      },
    );
};
</script>
<template>
  <div class="content mb-0">
    <div class="d-flex">
      <div class="align-self-center">
        <h3 class="font-16 mb-2">{{ t('asset.history.recent_order') }}</h3>
      </div>
    </div>
  </div>

  <div class="card card-style">
    <div
      v-if="loadingInitial"
      class="content mt-4 text-center"
    >
          <span
            class="spinner-border spinner-border-sm"
          ></span>
    </div>
    <div v-else-if="userOrders.length === 0" class="content text-center">
      {{ t('asset.history.empty') }}
    </div>
    <div v-else class="content">
      <div class="list-group list-custom list-group-m list-group-flush rounded-xs">
        <template
          v-for="userOrder in userOrders"
          :key="userOrder.id"
        >
          <UserOrderListItem :user-order="userOrder"/>
        </template>
      </div>
      <div
        v-if="loadingNextPage"
        class="content text-center"
      >
        <span
          class="spinner-border spinner-border-sm"
        ></span>
      </div>
      <div
        class="row"
        v-else-if="userOrdersNextPage !== -1"
        @click="handleNextPage"
      >
        <a class="text-center">{{ t('asset.history.view_more') }}</a>
      </div>
    </div>
  </div>
</template>
