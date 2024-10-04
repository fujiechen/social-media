<script setup>
import { computed, onMounted, ref } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import PageTitle from '../../PageTitle/PageTitle.vue';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const socialNetwork = computed(() => store.getters['userProfile/socialNetwork']);

const getLoading = ref(true);
const updateLoading = ref(false);
const message = ref('');
const successMessage = ref('');

const whatsapp = ref('');
const telegram = ref('');
const facebook = ref('');

onMounted(() => {
  store.dispatch('userProfile/get')
    .then(() => {
      getLoading.value = false;
      message.value = '';
      successMessage.value = '';

      whatsapp.value = socialNetwork.value.whatsapp ?? '';
      telegram.value = socialNetwork.value.telegram ?? '';
      facebook.value = socialNetwork.value.facebook ?? '';
    })
    .catch(() => {
      getLoading.value = false;
      message.value = t('account.errors.general_get');
    });
});

const handleUpdate = () => {
  updateLoading.value = true;
  message.value = '';
  successMessage.value = '';

  store.dispatch('userProfile/update', {
    whatsapp: whatsapp.value,
    telegram: telegram.value,
    facebook: facebook.value,
  })
    .then(() => {
      updateLoading.value = false;
      message.value = '';

      whatsapp.value = socialNetwork.value.whatsapp;
      telegram.value = socialNetwork.value.telegram;
      facebook.value = socialNetwork.value.facebook;

      successMessage.value = t('account.success');
    })
    .catch((error) => {
      if (error?.response?.data?.errors) {
        try {
          const { errors } = error.response.data;
          [message.value] = errors[Object.keys(errors)[0]];
        } catch {
          message.value = t('account.errors.general_update');
        }
      } else {
        message.value = t('account.errors.general_update');
      }
      updateLoading.value = false;
    });
};
</script>
<template>
  <PageTitle
    :name="t('account.info_title')"
    :back-link="{name:'account'}"
  />
  <div class="content my-0 mt-n2 px-1">
    <div class="d-flex">
      <div class="align-self-center">
        <h3 class="font-16 mb-2">{{ t('account.social_network') }}</h3>
      </div>
    </div>
  </div>
  <div class="card card-style">
    <div class="content pb-0 pt-4">
      <div class="content mt-0">
        <div
          v-if="getLoading"
          class="content mt-4 text-center"
        >
        <span
          class="spinner-border spinner-border-sm"
        ></span>
        </div>
        <template v-else>
          <div
            v-if="successMessage"
            class="alert bg-green-light shadow-bg shadow-bg-m alert-dismissible rounded-s fade show"
            role="alert"
          >
            <i class="bi bi-check-circle-fill pe-2"></i>
            {{ successMessage }}
            <button type="button" class="btn-close opacity-10" data-bs-dismiss="alert"
                    aria-label="Close"></button>
          </div>
          <div
            v-if="message"
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
          <div class="form-custom form-label form-border mb-3 bg-transparent">
            <input
              type="text"
              class="form-control rounded-xs"
              id="telegram"
              v-model="telegram"
            />
            <label
              for="telegram"
              class="form-label-always-active color-highlight"
            >
              {{ t('account.telegram') }}
            </label>
          </div>
          <div class="form-custom form-label form-border mb-3 bg-transparent">
            <input
              type="text"
              class="form-control rounded-xs"
              id="whatsapp"
              v-model="whatsapp"
            />
            <label
              for="whatsapp"
              class="form-label-always-active color-highlight"
            >
              {{ t('account.whatsapp') }}
            </label>
          </div>
          <div class="form-custom form-label form-border mb-3 bg-transparent">
            <input
              type="text"
              class="form-control rounded-xs"
              id="facebook"
              v-model="facebook"
            />
            <label
              for="facebook"
              class="form-label-always-active color-highlight"
            >
              {{ t('account.facebook') }}
            </label>
          </div>
          <div
            v-if="updateLoading"
            disabled
            class="btn btn-full gradient-green shadow-bg shadow-bg-s mt-4"
          >
            <span class="spinner-border spinner-border-sm"></span>
          </div>
          <div
            v-else
            @click="handleUpdate"
            class="btn btn-full gradient-green shadow-bg shadow-bg-s mt-4"
          >
            {{ t('account.apply_button') }}
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
