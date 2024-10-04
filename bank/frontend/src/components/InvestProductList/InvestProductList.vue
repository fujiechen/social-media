<script setup>
import {
  computed, ref, watch, onMounted,
} from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import { convertUserProductToListItem } from '@/helpers/productList';
import InvestProductListItem from '@/components/InvestProductList/InvestProductListItem.vue';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const userProductList = computed(() => store.state.invest.userProducts);

const loading = ref(true);
const listItems = ref([]);

watch(userProductList, (newValue) => {
  listItems.value = newValue.map((product) => convertUserProductToListItem(product));
});

onMounted(() => {
  store.dispatch('invest/listUserProducts', {
    init: true,
  })
    .then(() => {
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
});
</script>

<template>
  <div class="content my-0 mt-n2 px-1">
    <div class="d-flex">
      <div class="align-self-center">
        <h3 class="font-16 mb-2">{{ t('invest.my_investment') }}</h3>
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
          {{ t('invest.no_investment') }}
        </div>
        <InvestProductListItem
          v-else
          :list-items="listItems"
        />
      </template>
      <div class="pb-3"></div>
    </div>
  </div>
</template>
