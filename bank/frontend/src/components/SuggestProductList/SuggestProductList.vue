<script setup>
import { computed, onMounted, ref } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const suggestProductList = computed(() => store.state.product.suggestProducts);

const loading = ref(true);

onMounted(() => {
  store.dispatch('product/listSuggestProducts')
    .then(() => {
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
});

const handlePurchase = (id) => {
  store.commit('userPurchase/openCreatePopup', { id });
};
</script>
<template>
  <div class="content my-0 mt-n2 px-1">
    <div class="d-flex">
      <div class="align-self-center">
        <h3 class="font-16 mb-2">{{ t('home.suggest_product.title') }}</h3>
      </div>
    </div>
  </div>
  <div
    v-if="loading"
    class="content mt-4 text-center"
  >
    <span
      class="spinner-border spinner-border-sm"
    ></span>
  </div>
  <template v-else>
    <div v-if="suggestProductList.length === 0" class="card card-style">
      <div class="content text-center">
        {{ t('home.suggest_product.empty') }}
      </div>
    </div>
    <div
      v-for="(product) in suggestProductList"
      v-bind:key="product.id"
      @click="handlePurchase(product.id)"
      class="card card-style mb-3"
      data-bs-toggle="offcanvas"
      data-bs-target="#purchase-menu"
    >
      <div class="content">
        <div class="d-flex">
          <div class="ps-2 w-100">
            <div class="d-flex">
              <div class="pt-1 d-flex">
                <span
                  class="align-self-center icon rounded-s shadow-bg shadow-bg-xs gradient-green"
                >
                  <span class="color-white">{{ product.currency_name }}</span>
                </span>
              </div>
              <div class="pt-1 ms-2">
                <strong class="opacity-30 color-theme font-11">{{ product.title }}</strong>
                <h3 class="font-16 mt-n2 mb-0">{{ product.name }}</h3>
              </div>
            </div>
            <p class="mt-2 mb-2" style="line-height: 1.2;">{{ product.description }}</p>
            <div class="row mt-0 ms-1 font-11" style="line-height: 1.2">
              <p class="mb-0 ps-0"><i class="bi bi-check-circle-fill color-green-dark"></i>
                {{ t('home.suggest_product.min_require', {x: product.start_amount}) }}
              </p>
              <p class="mb-0 ps-0"><i class="bi bi-alarm-fill color-green-dark"></i>
                {{ t('home.suggest_product.freeze_days', {n: product.freeze_days}) }}
              </p>
              <p class="mb-0 ps-0"><i class="bi bi-award-fill color-green-dark"></i>
                {{ t('home.suggest_product.estimate_rate', {x: product.return_rate}) }}
              </p>
              <p v-if="product.fund_assets" class="mb-0 ps-0"><i
                class="bi bi-bank2 color-green-dark"></i>
                {{ t('home.suggest_product.total_asset', {x: product.fund_assets}) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
</template>
