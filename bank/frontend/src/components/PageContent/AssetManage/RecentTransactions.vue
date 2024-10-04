<script setup>
import {
  ref, computed, onMounted, watch,
} from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import TransactionConstants from '@/constants/Transaction';
import SimpleUserStatusPill from '@/components/StatusPill/SimpleUserStatusPill.vue';
import PageTitle from '../../PageTitle/PageTitle.vue';

const { t } = useI18n({ useScope: 'global' });

const store = useStore();

const selectAccountId = computed(() => store.state.asset.selectAccountId);
const transactions = computed(() => store.state.asset.recentTransactions);
const addInfo = computed(() => store.state.asset.recentTransactionAdditionInfo);

const accountInfo = computed(() => store.getters['asset/selectedAccount']);

const chartSeries = ref([0, 0]);

const lastDays = ref(TransactionConstants.LAST_DAYS[0]);
const loading = ref(true);
const showError = ref(true);

const handleGetInfo = () => {
  if (selectAccountId.value === -1) {
    showError.value = true;
    return;
  }

  showError.value = false;
  loading.value = true;
  store.dispatch('asset/getAccountTransactions', {
    userAccountId: selectAccountId.value,
    lastDays: lastDays.value,
  })
    .then(() => {
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
};

onMounted(() => {
  handleGetInfo();
});

watch((lastDays), () => {
  handleGetInfo();
});

watch(addInfo, (newValue) => {
  chartSeries.value = [
    newValue.total_income_number,
    newValue.total_expense_number,
  ];
});
</script>

<template>

  <PageTitle
    :name="t('asset.recent_trans.name')"
    :back-link="{name:'asset'}"
  />
  <div class="content mt-0 mb-1">
    <div class="d-flex">
      <div class="align-self-center">
        <h3 class="font-16 mb-2 mt-0 ms-1">{{ accountInfo.account_number ?? '' }}</h3>
      </div>
    </div>
  </div>
  <div class="card card-style">
    <div class="form-custom form-label form-border form-icon px-3 pt-1">
      <i class="bi bi-calendar font-13"></i>
      <select
        class="form-select rounded-xs"
        id="recent_trans_days"
        v-model="lastDays"
      >
        <option
          v-for="days in TransactionConstants.LAST_DAYS"
          :key="days"
          :value="days"
        >
          {{ t('asset.recent_trans.last') }} {{ days }} {{ t('asset.recent_trans.days') }}
        </option>
      </select>
      <label
        for="recent_trans_days"
      >
      </label>
    </div>
    <div v-if="showError" class="content">
      <div class="content text-center">
        <p>{{ t('asset.recent_trans.error_get_account') }}</p>
      </div>
    </div>
    <div v-else-if="loading" class="content">
      <div class="content text-center">
      <span
        class="spinner-border spinner-border-sm"
      ></span>
      </div>
    </div>
    <div v-else-if="transactions.length === 0" class="content text-center">
      <p>{{ t('asset.recent_trans.empty') }}</p>
    </div>
    <template v-else>
      <div class="position-relative">
        <div class="position-absolute w-100" style="height:320px">
          <div class="card-center">
            <h1 class="pb-5 mb-5 text-center">
              <span class="font-16 d-block pt-4 mt-1 color-green-dark">
                {{ addInfo.total_income }}
              </span>
              <span class="font-12 font-400 opacity-50 d-block mt-n2 color-green-dark">
                {{ t('asset.recent_trans.total_income') }}
              </span>
              <span class="font-16 d-block pt-0 mt-0 color-red-dark">
                {{ addInfo.total_expense }}
              </span>
              <span class="font-12 font-400 opacity-50 d-block mt-n2 color-red-dark">
                {{ t('asset.recent_trans.total_expense') }}
              </span>
            </h1>
          </div>
        </div>
        <div class="mx-auto" style="width:320px">
          <apexchart
            class="chart mx-auto no-click"
            width="320px"
            type="donut"
            :options="TransactionConstants.CHART.OPTION"
            :series="chartSeries"
          >
          </apexchart>
        </div>
      </div>
      <div class="content">
        <div class="content mt-2 mb-0">

          <div class="list-group list-custom list-group-m list-group-flush rounded-xs">
            <a
              v-for="transaction in transactions"
              :key="transaction.id"
              class="list-group-item"
            >
              <i v-if="transaction.type === 'income'"
                 class="font-28 color-green-dark bi bi-arrow-up-circle"></i>
              <i v-else class="font-28 color-red-dark bi bi-arrow-down-circle"></i>
              <div class="ms-2">
                <h5 class="font-15 mb-0">{{ transaction.amount }}</h5>
                <span>{{ transaction.created_at }}</span>
              </div>
              <div class="ms-1 align-self-end">
                <span v-if="transaction.comment">{{ transaction.comment }}</span>
              </div>
              <SimpleUserStatusPill :status="transaction.status"/>
            </a>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
