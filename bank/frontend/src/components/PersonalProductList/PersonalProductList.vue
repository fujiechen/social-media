<script setup>
import {
  computed, onMounted, ref, watch,
} from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import { convertProductToListItem } from '@/helpers/productList';
import ProductListItem from '@/components/ProductListItem/ProductListItem.vue';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const suggestInvestList = computed(() => store.state.product.suggestInvestments);

const loading = ref(true);
const listItems = ref([]);

watch(suggestInvestList, (newValue) => {
  listItems.value = newValue.map((product) => convertProductToListItem(product));
});

onMounted(() => {
  store.dispatch('product/listSuggestInvestments')
    .then(() => {
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
});
</script>
<template>
  <!-- Title -->
  <div class="content my-0 mt-n2 px-1">
    <div class="d-flex">
      <div class="align-self-center">
        <h3 class="font-16 mb-2">{{ t('home.suggest_invest.title') }}</h3>
      </div>
      <div class="align-self-center ms-auto">
        <router-link
          :to="{name:'market'}"
          class="font-12 pt-1"
        >
          {{ t('home.suggest_invest.view_all') }}
        </router-link>
      </div>
    </div>
  </div>
  <div class="card card-style">
    <div class="content mb-0">
      <div
        v-if="loading"
        class="content mt-4 text-center"
      >
        <span
          class="spinner-border spinner-border-sm"
        ></span>
      </div>
      <template v-else>
        <div v-if="listItems.length === 0" class="text-center">
          {{ t('home.suggest_invest.empty') }}
        </div>
        <ProductListItem
          :list-items="listItems"
        />
      </template>
      <div class="pb-3"></div>
    </div>
  </div>
</template>
