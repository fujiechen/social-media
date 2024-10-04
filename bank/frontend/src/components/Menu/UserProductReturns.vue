<script setup>
import { computed, ref, watch } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const popupOpenTrigger = computed(() => store.state.invest.popupOpenTrigger);
const userProductId = computed(() => store.state.invest.userProductId);
const userProductInfo = computed(() => store.state.invest.userProductInfo);
const userProductReturns = computed(() => store.state.invest.userProductReturns);
const userProductReturnsNextPage = computed(() => store.state.invest.userProductReturnsNextPage);

const loadingInitialReturns = ref(true);
const loadingNextPage = ref(false);

const adjustOffCanvasTop = () => {
  const notification = document.getElementById('top-notification');
  if (notification) {
    const notificationHeight = notification.offsetHeight;
    const offCanvasMenu = document.getElementById('invest-menu');
    if (offCanvasMenu) {
      offCanvasMenu.style.top = `${notificationHeight}px`;
      offCanvasMenu.style['padding-bottom'] = `${notificationHeight}px`;
    }
  }
};

watch(popupOpenTrigger, () => {
  loadingInitialReturns.value = true;
  loadingNextPage.value = false;

  store.dispatch('invest/listUserProductReturns', {
    id: userProductId.value,
    init: true,
  })
    .then(
      () => {
        loadingInitialReturns.value = false;
      },
      () => {
        loadingInitialReturns.value = false;
      },
    );

  adjustOffCanvasTop();
});

const handleNextPage = () => {
  loadingNextPage.value = true;

  store.dispatch('invest/listUserProductReturns', {
    id: userProductId.value,
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
  <!-- Account page popup -->
  <div id="invest-menu" class="offcanvas offcanvas-bottom offcanvas-detached rounded-m">
    <!-- menu-size will be the dimension of your menu. If you set it to smaller than your content it will scroll-->
    <div class="menu-size" style="height:500px;">
      <div class="content">
        <a href="#" class="d-flex py-1 pb-4">
          <div class="align-self-center">
            <span
              :class="['icon','rounded-s','me-2','shadow-bg','shadow-bg-xs', userProductInfo?.currencySymbolColor]"
            >
              <span class="color-white">{{ userProductInfo?.currencySymbol }}</span>
            </span>
          </div>
          <div class="align-self-center ps-1">
            <h6 class="pt-1 mb-n1">{{ userProductInfo?.title }}</h6>
            <h5 class="pt-1 mb-n1">{{ userProductInfo?.name }}</h5>
            <p class="mb-0 font-10 opacity-70">
              {{
                userProductInfo?.isActive
                  ? t('invest.product_list.unlock_date')
                  : t('invest.product_list.release_date')
              }}: {{ userProductInfo?.releaseDate }}
            </p>
          </div>
          <div class="align-self-center ms-auto text-end">
            <p class="mb-0 font-11">{{ t('invest.action_sheet.book_cost') }}:
              {{ userProductInfo?.totalBookCost }}</p>
            <p class="mb-0 font-11">{{ t('invest.action_sheet.market_value') }}:
              {{ userProductInfo?.totalMarketValue }}</p>
          </div>
        </a>
        <div
          v-if="loadingInitialReturns"
          class="content mt-4 text-center"
        >
          <span
            class="spinner-border spinner-border-sm"
          ></span>
        </div>
        <div v-else-if="userProductReturns.length === 0" class="content text-center">
          {{ t('invest.action_sheet.empty') }}
        </div>
        <div v-else class="row">
          <div
            class="d-flex py-1"
            v-for="userProductReturn in userProductReturns"
            v-bind:key="userProductReturn.id"
          >
            <div class="align-self-center ps-1">
              <p class="mb-0 font-11">{{ userProductReturn.created_at }}</p>
            </div>
            <div class="align-self-center ps-1">
              <p class="mb-0 font-11">{{ userProductReturn.comment }}</p>
            </div>
            <div class="align-self-center ms-auto text-end">
              <h5 :class="'mb-0 font-15 '+userProductInfo.numberColorClass">
                {{ userProductReturn.market_value }}</h5>
            </div>
          </div>
          <div
            v-if="loadingNextPage"
            class="content mt-4 text-center"
          >
          <span
            class="spinner-border spinner-border-sm"
          ></span>
          </div>
          <a
            v-else-if="userProductReturnsNextPage !== -1"
            @click="handleNextPage"
            class="text-center"
          >
            {{ t('asset.history.view_more') }}
          </a>
        </div>
      </div>
      <a href="#"
         data-bs-dismiss="offcanvas"
         class="mx-3 btn btn-full gradient-highlight shadow-bg shadow-bg-s mb-4"
      >
        {{
          t('invest.action_sheet.back_button')
        }}
      </a>
    </div>
  </div>
</template>
