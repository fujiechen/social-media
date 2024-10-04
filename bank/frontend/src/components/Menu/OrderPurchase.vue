<script setup>
import { computed, ref, watch } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';

const { t, locale } = useI18n({ useScope: 'global' });
const store = useStore();
const router = useRouter();

const loggedIn = computed(() => store.state.auth.status.loggedIn);
const productInfo = computed(() => store.state.userPurchase.productInfo);
const productPurchaseEnabled = computed(() => store.getters['userPurchase/productPurchaseEnabled']);
const accountDetail = computed(() => store.state.userPurchase.accountDetail);
const productId = computed(() => store.state.userPurchase.investProductId);
const popupOpenTrigger = computed(() => store.state.userPurchase.popupOpenTrigger);

const loadingPurchaseDescription = computed(() => store.state.settings.loadingPurchaseDescription);
const purchaseHelp = computed(() => store.state.settings.purchaseHelp);

const loadingProduct = ref(true);
const loadingAccount = ref(true);
const loadingPurchase = ref(true);
const message = ref('');

const loadingInit = computed(() => loadingProduct.value || loadingAccount.value);

const amount = ref('');

const adjustOffCanvasTop = () => {
  const notification = document.getElementById('top-notification');
  if (notification) {
    const notificationHeight = notification.offsetHeight;
    const offCanvasMenu = document.getElementById('purchase-menu');
    if (offCanvasMenu) {
      offCanvasMenu.style.top = `${notificationHeight}px`;
      offCanvasMenu.style['padding-bottom'] = `${notificationHeight}px`;
    }
  }
};

watch(popupOpenTrigger, () => {
  loadingProduct.value = true;
  loadingAccount.value = true;
  loadingPurchase.value = false;
  message.value = '';

  amount.value = '';

  store.dispatch('settings/getPurchaseDescription', {
    locale: locale.value,
  });

  store.dispatch('userPurchase/getProduct', { id: productId.value })
    .then(
      (product) => {
        loadingProduct.value = false;

        return product?.currency?.id;
      },
      () => {
        loadingProduct.value = false;
        message.value = t('market.action_sheet.errors.get_product');
      },
    )
    .then((currencyId) => {
      if (currencyId && loggedIn.value) {
        store.dispatch('userPurchase/getAccount', { currencyId })
          .then(() => {
            loadingAccount.value = false;
          })
          .catch(() => {
            loadingAccount.value = false;
            message.value = t('market.action_sheet.errors.get_account');
          });
      }
    });

  adjustOffCanvasTop();
});

const handlePurchase = () => {
  loadingPurchase.value = true;

  if (amount.value <= 0) {
    message.value = t('market.action_sheet.errors.amount');
    loadingPurchase.value = false;
  } else {
    store.dispatch('userPurchase/purchase', {
      productId: productId.value,
      amount: amount.value,
    })
      .then(
        (response) => {
          const { data } = response;
          const amountPurchased = data.amount;
          const namePurchased = data.product?.name;
          store.commit('invest/showSuccessMessage', {
            title: t('invest.successMessage.purchase_title'),
            content: `${t('invest.successMessage.purchase_message')}${amountPurchased} ${namePurchased}`,
          });
          loadingPurchase.value = false;
          router.push({ name: 'invest' });
        },
        (error) => {
          if (error?.response?.data?.errors) {
            try {
              const { errors } = error.response.data;
              [message.value] = errors[Object.keys(errors)[0]];
            } catch {
              message.value = t('market.action_sheet.errors.general_purchase');
            }
          } else {
            message.value = t('market.action_sheet.errors.general_purchase');
          }
          loadingPurchase.value = false;
        },
      );
  }
};
</script>
<template>
  <!-- Invest page popup -->
  <div id="purchase-menu" style="height:100%;" class="offcanvas offcanvas-bottom">
    <!-- Title -->
    <div class="d-flex mx-3 mt-3 py-1">
      <div class="align-self-center">
        <h1 class="font-20 mb-0">{{ t('market.action_sheet.purchase_product') }}</h1>
      </div>
      <div class="align-self-center ms-auto">
        <a href="#" class="ps-4 shadow-0 me-n2" data-bs-dismiss="offcanvas">
          <i class="bi bi-x color-red-dark font-26 line-height-xl"></i>
        </a>
      </div>
    </div>
    <div
      v-if="!loadingPurchaseDescription && purchaseHelp"
      class="card card-style bg-fade2-blue border border-fade-blue alert show fade p-0 mb-3 mt-2"
    >
      <div class="content my-3">
        <p class="color-blue-dark mb-0 ps-1 pe-1 line-height-s">
          <span v-html="purchaseHelp"></span>
        </p>
      </div>
    </div>
    <!-- Description -->
    <div
      v-if="loadingProduct"
      class="content mt-4 text-center"
    >
      <span
        class="spinner-border spinner-border-sm"
      ></span>
    </div>
    <div
      v-else
      class="content mt-0"
    >
      <span>{{ productInfo.title }}</span>
      <h3>{{ productInfo.name }}</h3>
      <p class="mt-2 mb-2" style="line-height: 1.4;">{{ productInfo.description }}</p>
      <div class="row mt-1 ms-1 font-11" style="line-height: 1.4">
        <p class="mb-0 ps-0">
          <i class="bi bi-check-circle-fill color-green-dark"></i>
          {{ t('market.action_sheet.min_require', {x: productInfo.start_amount}) }}
        </p>
        <p class="mb-0 ps-0">
          <i class="bi bi-alarm-fill color-green-dark"></i>
          {{ t('market.action_sheet.freeze_days', {n: productInfo.freeze_days}) }}
        </p>
        <p class="mb-0 ps-0">
          <i class="bi bi-award-fill color-green-dark"></i>
          {{ t('market.action_sheet.estimate_rate', {x: productInfo.estimate_rate}) }}
        </p>
        <p v-if="productInfo.fund_assets" class="mb-0 ps-0">
          <i class="bi bi-bank2 color-green-dark"></i>
          {{ t('market.action_sheet.total_asset', {x: productInfo.fund_assets}) }}
        </p>
        <p v-if="productInfo.fund_fact_url" class="mb-0 ps-0">
          <i class="bi bi-file-pdf-fill color-green-dark"></i>
          <a :href="productInfo.fund_fact_url" target="_blank"
             class="ms-1">{{ t('market.action_sheet.fund_fact_sheet') }}</a>
        </p>
        <p v-if="productInfo.prospectus_url" class="mb-0 ps-0">
          <i class="bi bi-file-pdf-fill color-green-dark"></i>
          <a :href="productInfo.prospectus_url" target="_blank"
             class="ms-1">{{ t('market.action_sheet.prospectus') }}</a>
        </p>
      </div>
    </div>
    <div class="divider divider-margins mt-3"></div>
    <div v-if="message" class="content mt-0">
      <div
        class="alert bg-fade-red color-red-dark alert-dismissible rounded-s fade show"
        role="alert"
      >
        <i class="bi bi-exclamation-triangle pe-2"></i>
        {{ message }}
        <button
          type="button"
          class="btn-close opacity-20 font-11 pt-3 mt-1"
          data-bs-dismiss="alert"
          aria-label="Close"></button>
      </div>
    </div>
    <div v-if="loggedIn && productPurchaseEnabled && !loadingInit" class="mb-4">
      <!-- Purchase -->
      <div class="content mt-0">
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            v-model="amount"
            type="number"
            class="form-control rounded-xs"
            id="market_action_sheet_amount"
          />
          <label
            for="market_action_sheet_amount"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('market.action_sheet.amount') }}
          </label>
          <span v-if="!loadingProduct" class="font-10">
            ( {{ t('market.action_sheet.currency') }}: {{ productInfo.currency?.name }} )
          </span>
        </div>
        <p v-if="loadingAccount" class="font-12 mt-0 mb-0 text-center" style="margin-left:.25rem">
          <span
            class="spinner-border spinner-border-sm"
          ></span>
        </p>
        <p v-else class="font-12 mt-0 mb-0" style="margin-left:.25rem">
          <i class="bi bi-cash-stack"></i>
          {{ t('market.action_sheet.avail_fund') }}: {{ accountDetail.balance }}
          ({{ t('market.action_sheet.account_num') }}: {{ accountDetail.account_number }})
        </p>
        <div class="pb-2"></div>
      </div>
      <a
        v-if="loadingPurchase"
        disabled
        class="mx-3 mb-3 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        <span class="spinner-border spinner-border-sm"></span>
      </a>
      <a
        v-else
        @click="handlePurchase"
        class="mx-3 mb-3 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        {{ t('market.action_sheet.confirm_button') }}
      </a>
    </div>
    <div v-else-if="loggedIn"></div>
    <div v-else class="mb-4">
      <router-link
        :to="{name:'signIn'}"
        class="mx-3 mb-3 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        {{ t('market.action_sheet.login_button') }}
      </router-link>
    </div>
  </div>
</template>
