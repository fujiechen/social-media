<script setup>
import {
  computed, onMounted, ref, watch, nextTick,
} from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import LanguageConstants from '@/constants/Language';
import DepositConstants from '@/constants/Deposit';

const { t, locale } = useI18n({ useScope: 'global' });
const store = useStore();

const popupOpenTrigger = computed(() => store.state.userDeposit.popupOpenTrigger);
const depositAccounts = computed(() => store.state.userDeposit.accounts);
const loadingDescription = computed(() => store.state.settings.loadingDepositDescription);
const loadingDepositAmounts = computed(() => store.state.settings.loadingDepositAmountOptions);
const depositHelp = computed(() => store.state.settings.depositHelp);
const depositAmountOptions = computed(() => store.state.settings.depositAmountOptions);
const stripeLocale = computed(() => {
  const userLanguage = store.state.auth?.user?.language;
  return LanguageConstants.feToStripe[userLanguage ?? 'en'];
});

const loadingAccounts = ref(false);
const loadingDeposit = ref(false);
const message = ref('');

const selectedUserAccountId = ref(0);
const selectedDepositAccount = computed(() => depositAccounts.value.find((account) => account.id === selectedUserAccountId.value));
const selectedPaymentMethod = ref(DepositConstants.PAYMENT_GATEWAY_METHODS[0]);
const amount = ref('');

// nihao
const nihaoForm = ref('');

// stripe
const isStripeLoaded = ref(false);
const stripe = ref(null);
const elements = ref(null);
const isFirstStep = ref(true);

const adjustOffCanvasTop = () => {
  const notification = document.getElementById('top-notification');
  if (notification) {
    const notificationHeight = notification.offsetHeight;
    const offCanvasMenu = document.getElementById('menu-deposit');
    if (offCanvasMenu) {
      offCanvasMenu.style.top = `${notificationHeight}px`;
      offCanvasMenu.style['padding-bottom'] = `${notificationHeight}px`;
    }
  }
};

const loadStripeScript = () => new Promise((resolve, reject) => {
  if (document.querySelector('#stripe-script')) {
    resolve();
    return;
  }

  const script = document.createElement('script');
  script.id = 'stripe-script';
  script.src = 'https://js.stripe.com/v3/';
  script.onload = () => {
    isStripeLoaded.value = true;
    resolve();
  };
  script.onerror = () => reject(new Error('Stripe script failed to load'));
  document.head.appendChild(script);
});

onMounted(async () => {
  try {
    await loadStripeScript();
  } catch (error) {
    // console.error(error);
  }
});

watch(popupOpenTrigger, () => {
  isFirstStep.value = true;
  loadingAccounts.value = true;
  message.value = '';

  selectedUserAccountId.value = 0;
  amount.value = '';

  store.dispatch('settings/getDepositDescription', {
    locale: locale.value,
  });
  store.dispatch('settings/getDepositAmountOptions', {
    locale: locale.value,
  });
  store.dispatch('userDeposit/getAccounts')
    .then(() => {
      loadingAccounts.value = false;
      selectedUserAccountId.value = depositAccounts.value[0].id;
    })
    .catch(() => {
      loadingAccounts.value = false;
      message.value = t('asset.errors.get_account');
    });

  adjustOffCanvasTop();
});

const handleDeposit = async () => {
  loadingDeposit.value = true;

  if (amount.value <= 0) {
    message.value = t('asset.deposit.errors.amount');
  } else {
    try {
      const response = await store.dispatch('userDeposit/deposit', {
        amount: amount.value,
        userAccountId: selectedUserAccountId.value,
        paymentMethod: selectedPaymentMethod.value,
        callbackUrl: `${window.location.protocol}//${window.location.host}${process.env.VUE_APP_SUB_PATH}paymentCallback`,
      });

      const { data } = response;
      if (
        !Array.isArray(data.user_order_payments)
        || !data.user_order_payments.length
        || data.user_order_payments[0].status !== 'successful'
        || !data.user_order_payments[0].payment_gateway
      ) {
        throw new Error('something goes wrong');
      }

      const userOrderPayment = data.user_order_payments[0];

      if (userOrderPayment.payment_gateway.payment_gateway_type === 'nihao') {
        if (!userOrderPayment.response || !userOrderPayment.response.form) {
          throw new Error('something goes wrong');
        }

        nihaoForm.value = userOrderPayment.response.form;

        await nextTick(() => {
          const formElement = document
            .getElementById('nihao-form')
            .querySelector('form');
          if (formElement) {
            formElement.submit();
          } else {
            throw new Error('something goes wrong');
          }
        });
        await new Promise((resolve) => {
          setTimeout(resolve, 3000);
        });
      } else if (userOrderPayment.payment_gateway.payment_gateway_type === 'stripe') {
        if (!userOrderPayment.stripe_intent_client_secret
          || !userOrderPayment.payment_gateway.public) {
          throw new Error('something goes wrong');
        }

        isFirstStep.value = false;

        // Stripe data
        const stripePublicKey = data.user_order_payments[0].payment_gateway.public;
        const stripeIntentClientSecret = data.user_order_payments[0].stripe_intent_client_secret;

        // Initialize Stripe
        /* eslint-disable-next-line no-undef */
        stripe.value = Stripe(stripePublicKey);
        const options = {
          clientSecret: stripeIntentClientSecret,
          locale: stripeLocale.value,
        };

        // Load Stripe Elements
        const stripeInstance = await stripe.value;
        elements.value = stripeInstance.elements(options);

        const paymentElement = elements.value.create('payment');
        paymentElement.mount('#payment-element');
        message.value = '';
        loadingDeposit.value = false;
      } else {
        throw new Error('something goes wrong');
      }
    } catch (error) {
      /* eslint no-console: ["error", { allow: ["error"] }] */
      console.error(error);
      if (error?.response?.data?.errors) {
        try {
          const { errors } = error.response.data;
          [message.value] = errors[Object.keys(errors)[0]];
        } catch {
          message.value = t('asset.deposit.errors.general');
        }
      } else {
        message.value = t('asset.deposit.errors.general');
      }
      loadingDeposit.value = false;
    }
  }
  loadingDeposit.value = false;
};

const handleStripeSubmit = async (e) => {
  loadingDeposit.value = true;
  e.preventDefault();

  try {
    const result = await stripe.value.confirmPayment({
      elements: elements.value,
      redirect: 'always',
      confirmParams: {
        return_url: window.location.href,
      },
    });

    if (result.error) {
      throw new Error(result.error?.message);
    }

    loadingDeposit.value = false;
    document.getElementById('close-deposit-menu').click();
  } catch (error) {
    if (error?.message) {
      message.value = error.message;
    } else {
      message.value = t('asset.deposit.errors.general');
    }
    loadingDeposit.value = false;
  }
};
</script>
<template>
  <div
    id="menu-deposit"
    style="height:100%;"
    class="offcanvas offcanvas-bottom"
  >
    <div id="nihao-form" v-html="nihaoForm"></div>
    <div class="d-flex mx-3 mt-3 py-1">
      <div class="align-self-center">
        <h1 class="font-20 mb-0">{{ t('asset.deposit.name') }}</h1>
      </div>
      <div class="align-self-center ms-auto">
        <a href="#" class="ps-4 shadow-0 me-n2" data-bs-dismiss="offcanvas">
          <i class="bi bi-x color-red-dark font-26 line-height-xl"></i>
        </a>
      </div>
    </div>
    <div v-if="isFirstStep">
      <div
        v-if="loadingDescription"
        class="text-center mb-3 mt-2"
      >
        <span class="spinner-border spinner-border-sm"></span>
      </div>
      <div
        v-else-if="depositHelp"
        class="card card-style bg-fade2-blue border border-fade-blue alert show fade p-0 mb-3 mt-2"
      >
        <div class="content my-3">
          <p class="color-blue-dark mb-0 ps-3 pe-4 line-height-s">
            <span v-html="depositHelp"></span>
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
      <div class="content mt-0">
        <div class="form-custom form-label form-icon">
          <i class="bi bi-wallet2 font-14"></i>
          <select
            v-model="selectedUserAccountId"
            class="form-select rounded-xs"
            id="account"
            aria-label="select user account id"
          >
            <option v-if="loadingAccounts || depositAccounts.length === 0" :value="0">
              ...
            </option>
            <option v-for="(depositAccount) in depositAccounts" :value="depositAccount.id" :key="depositAccount.id">
              {{ depositAccount.account_number }}
            </option>
          </select>
          <label
            for="account"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.deposit.account') }}
          </label>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <select
            v-model="selectedPaymentMethod"
            class="form-select rounded-xs"
            id="payment_method"
            aria-label="select payment method"
          >
            <option v-for="paymentMethod in DepositConstants.PAYMENT_GATEWAY_METHODS" :value="paymentMethod" :key="paymentMethod">
              {{ t(`asset.deposit.payment_method.${paymentMethod}`) }}
            </option>
          </select>
          <label
            for="payment_method"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.payment_method_label') }}
          </label>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <select
            v-model="amount"
            class="form-select rounded-xs"
            id="account"
            aria-label="select deposit amount"
          >
            <option v-if="loadingDepositAmounts || depositAmountOptions.length === 0" :value="0">
              ...
            </option>
            <option v-for="(depositAmountOption, key) in depositAmountOptions" :value="depositAmountOption" :key="key">
              {{ depositAmountOption }}
            </option>
          </select>
          <label
            for="deposit_amount"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.amount_label') }}
          </label>
          <span v-if="!loadingAccounts && selectedUserAccountId" class="font-10">( {{ t('asset.currency') }}: {{
              selectedDepositAccount?.currency?.name
            }} )</span>
        </div>
        <p v-if="loadingAccounts || !selectedUserAccountId" class="font-12 mt-0 mb-0 text-center" style="margin-left:.25rem">
        <span
          class="spinner-border spinner-border-sm"
        ></span>
        </p>
        <p v-else class="font-12 mt-0 mb-0" style="margin-left:.25rem">
          <i class="bi bi-cash-stack"></i>
          {{ t('asset.avail_fund') }}: {{ selectedDepositAccount.balance }}
          ({{ t('asset.account_num') }}: {{ selectedDepositAccount.account_number }})
        </p>
      </div>
      <a
        v-if="loadingDeposit"
        disabled
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        <span class="spinner-border spinner-border-sm"></span>
      </a>
      <a
        v-else
        @click="handleDeposit"
        class="mx-3 mb-4 btn btn-full gradient-green shadow-bg shadow-bg-s"
      >
        {{ t('asset.deposit.confirm_button') }}
      </a>
      <a
        id="close-deposit-menu"
        data-bs-dismiss="offcanvas"
        style="display: none"
      ></a>
    </div>
    <div v-else>
      <div class="content mt-3">
        <div class="form-custom form-label form-icon">
          <i class="bi bi-wallet2 font-14"></i>
          <input
            disabled
            v-model="selectedDepositAccount.account_number"
            type="text"
            class="form-control rounded-xs"
            id="account"
          />
          <label
            for="account"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.deposit.account') }}
          </label>
        </div>
        <div class="pb-3"></div>
        <div class="form-custom form-label form-icon">
          <i class="bi bi-code-square font-14"></i>
          <input
            disabled
            v-model="amount"
            type="number"
            class="form-control rounded-xs"
            id="deposit_amount"
          />
          <label
            for="deposit_amount"
            class="form-label-always-active color-highlight font-11"
          >
            {{ t('asset.amount_label') }}
          </label>
          <span v-if="!loadingAccounts && selectedUserAccountId" class="font-10">( {{ t('asset.currency') }}: {{
              selectedDepositAccount?.currency?.name
            }} )</span>
        </div>
        <p v-if="loadingAccounts || !selectedUserAccountId" class="font-12 mt-0 mb-0 text-center" style="margin-left:.25rem">
        <span
          class="spinner-border spinner-border-sm"
        ></span>
        </p>
        <p v-else class="font-12 mt-0 mb-0" style="margin-left:.25rem">
          <i class="bi bi-cash-stack"></i>
          {{ t('asset.avail_fund') }}: {{ selectedDepositAccount.balance }}
          ({{ t('asset.account_num') }}: {{ selectedDepositAccount.account_number }})
        </p>
      </div>
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
      <form id="payment-form" class="mx-3">
        <div id="payment-element">
          <!-- Stripe will create form elements here -->
        </div>
        <a
          v-if="loadingDeposit"
          disabled
          class="mt-3 btn btn-full gradient-green shadow-bg shadow-bg-s w-100"
        >
          <span class="spinner-border spinner-border-sm"></span>
        </a>
        <button
          v-else
          type="submit"
          class="mt-3 btn btn-full gradient-green shadow-bg shadow-bg-s w-100"
          @click="handleStripeSubmit"
        >
          {{ t('asset.deposit.confirm_stripe_pay') }}
        </button>
        <a
          id="close-deposit-menu"
          data-bs-dismiss="offcanvas"
          style="display: none"
        ></a>
      </form>
    </div>
  </div>
</template>
