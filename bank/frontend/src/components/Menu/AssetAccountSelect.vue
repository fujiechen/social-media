<script setup>
import { computed } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const accounts = computed(() => store.state.asset.accounts);

const handleSelect = (id) => {
  store.commit('asset/selectAccount', {
    id,
  });
  document.getElementById('close-asset-account-select-menu').click();
};
</script>
<template>
  <div
    id="asset-account-select-menu"
    class="offcanvas offcanvas-bottom offcanvas-detached rounded-m"
  >
    <!-- menu-size will be the dimension of your menu. If you set it to smaller than your content it will scroll-->
    <div class="menu-size" style="height:410px;">
      <div class="d-flex mx-3 mt-3 py-1">
        <div class="align-self-center">
          <h1 class="mb-0">{{ t('asset.select_card.name') }}</h1>
        </div>
        <div class="align-self-center ms-auto">
          <a href="#" class="py-3 ps-4 shadow-0 me-n2" data-bs-dismiss="offcanvas">
            <i class="bi bi-x color-red-dark font-26"></i>
          </a>
        </div>
      </div>
      <div class="p-3 pt-2 list-group list-custom list-group-m list-group-flush mb-4">
        <a
          v-for="account in accounts"
          :key="account.id"
          @click="handleSelect(account.id)"
          class="list-group-item d-flex justify-content-between"
        >
          <div class="d-flex">
            <div class="pt-1 d-flex">
            <span
              class="align-self-center icon rounded-s shadow-bg shadow-bg-xs gradient-green opacity-100"
            >
              <strong class="color-white opacity-100 font-14" style="display: inline-block">
                {{ account.currency.name }}
              </strong>
            </span>
            </div>
            <strong class="font-14 ms-2">
              {{ account.account_number }}
            </strong>
          </div>
          <div>
            <strong class="font-14 pe-3">{{ account.balance }}</strong>
            <i class="bi bi-chevron-right ms-1"></i>
          </div>
        </a>
      </div>
    </div>
    <a
      id="close-asset-account-select-menu"
      data-bs-dismiss="offcanvas"
      style="display: none"
    ></a>
  </div>
</template>
