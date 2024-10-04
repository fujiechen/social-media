<script setup>
import { computed, ref } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import { convertInvestProductListItemToInvestHistoryPopup } from '@/helpers/productList';

const store = useStore();

defineProps({
  listItems: Array,
  handleNextPage: Function,
});

const { t } = useI18n({ useScope: 'global' });

const userProductsNextPage = computed(() => store.state.invest.userProductsNextPage);

const loadingNextPage = ref(false);

const handleShowPopup = (lineItem) => {
  store.commit('invest/openPopup', convertInvestProductListItemToInvestHistoryPopup(lineItem));
};

const handleNextPage = () => {
  loadingNextPage.value = true;

  store.dispatch('invest/listUserProducts', {
    init: false,
  })
    .then(() => {
      loadingNextPage.value = false;
    })
    .catch(() => {
      loadingNextPage.value = false;
    });
};
</script>

<template>
  <div v-for="(listItem, index) in listItems" :key="listItem.id">
    <a
      @click="handleShowPopup(listItem)"
      class="d-flex py-1"
      data-bs-toggle="offcanvas"
      data-bs-target="#invest-menu"
    >
      <div class="align-self-center me-auto ps-1" style="width: 100px">
        <h5 :class="['pt-1','mb-n1', listItem.isActive ? '' : 'opacity-70']">{{
            listItem.title
          }}</h5>
        <p class="mb-0 font-10 opacity-70">
          {{
            listItem.isActive
              ? t('invest.product_list.unlock_date')
              : t('invest.product_list.release_date')
          }}: {{ listItem.releaseDate }}
        </p>
      </div>
      <div class="align-self-center m-1">
        <apexchart
          class="chart mt-n5 pt-4 ms-n3"
          :width="listItem.chartMetaData.width"
          :type="listItem.chartMetaData.type"
          :height="listItem.chartMetaData.height"
          :options="listItem.chartMetaData.chartOptions"
          :series="listItem.chartMetaData.chartSeries"
        >
        </apexchart>
      </div>
      <div class="align-self-center ms-auto text-end">
        <h5
          :class="['pt-0','mb-n1',listItem.numberColorClass,listItem.isActive ? '' : 'opacity-70']">
          {{
            listItem.isActive
              ? listItem.totalMarketValue
              : listItem.finalMarketValue
          }}
        </h5>
        <h6
          :class="['text-end','mb-0',listItem.numberColorClass, listItem.isActive ? '' : 'opacity-70']">
          {{
            listItem.isActive
              ? listItem.totalIncreaseRate
              : listItem.finalIncreaseRate
          }}
        </h6>
      </div>
    </a>
    <div
      v-if="index !== listItems.length - 1"
      class="divider my-2 opacity-50"
    ></div>
  </div>
  <div class="row" v-if="loadingNextPage || userProductsNextPage !== -1">
    <div
      v-if="loadingNextPage"
      class="mt-4 text-center"
    >
    <span
      class="spinner-border spinner-border-sm"
    ></span>
    </div>
    <a
      v-else-if="userProductsNextPage !== -1"
      @click="handleNextPage"
      class="text-center"
    >
      {{ t('asset.history.view_more') }}
    </a>
  </div>
</template>
