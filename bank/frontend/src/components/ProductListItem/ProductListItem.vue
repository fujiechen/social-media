<script setup>
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';

const { t } = useI18n({ useScope: 'global' });

const store = useStore();
defineProps({
  listItems: Array,
});

const handlePurchase = (id) => {
  store.commit('userPurchase/openCreatePopup', { id });
};
</script>
<template>
  <div v-for="(listItem, index) in listItems" :key="listItem.id">
    <a
      @click="handlePurchase(listItem.id)"
      class="d-flex py-1"
      data-bs-toggle="offcanvas"
      data-bs-target="#purchase-menu"
    >
      <div class="align-self-center me-auto ps-1" style="width: 110px">
        <h5 class="pt-1 mb-n1">{{ listItem.title }}</h5>
        <p class="mb-0 font-11 opacity-70">
          {{ t('home.suggest_invest.frozen_days', {n: listItem.freezeDays}) }}
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
        <h4 :class="['pt-0','mb-n1',listItem.numberColorClass]">
          {{ listItem.startAmount }}
        </h4>
        <h6 :class="['text-end','mb-0',listItem.numberColorClass]">
          {{ listItem.rate }}
        </h6>
      </div>
    </a>
    <div
      v-if="index !== listItems.length - 1"
      class="divider my-2 opacity-50"
    ></div>
  </div>
</template>
