<script setup>
import { computed, onMounted, ref } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import PageTitle from '@/components/PageTitle/PageTitle.vue';
import LanguageConstants from '@/constants/Language';

const { t, locale } = useI18n({ useScope: 'global' });
const store = useStore();

const personalInfo = computed(() => store.getters['userProfile/personalInfo']);

const getLoading = ref(true);
const updateLoading = ref(false);
const message = ref('');
const successMessage = ref('');

const email = ref('');
const nickname = ref('');
const phone = ref('');
const language = ref('');
const wechat = ref('');
const alipay = ref('');

onMounted(() => {
  store.dispatch('userProfile/get')
    .then(() => {
      getLoading.value = false;
      message.value = '';
      successMessage.value = '';

      email.value = personalInfo.value.email;
      nickname.value = personalInfo.value.nickname;
      phone.value = personalInfo.value.phone ?? '';
      language.value = personalInfo.value.language;
      wechat.value = personalInfo.value.wechat;
      alipay.value = personalInfo.value.alipay;
    })
    .catch(() => {
      getLoading.value = false;
      message.value = t('account.errors.general_get');
    });
});

const handleUpdate = async () => {
  updateLoading.value = true;
  message.value = '';
  successMessage.value = '';

  if (email.value.length === 0) {
    message.value = t('account.errors.missing_email');
    updateLoading.value = false;
  } else if (nickname.value.length === 0) {
    message.value = t('account.errors.missing_name');
    updateLoading.value = false;
  } else if (phone.value.length === 0) {
    message.value = t('account.errors.missing_phone');
    updateLoading.value = false;
  } else {
    store.dispatch('userProfile/update', {
      email: email.value,
      nickname: nickname.value,
      phone: phone.value,
      language: language.value,
      wechat: wechat.value,
      alipay: alipay.value,
    })
      .then(() => {
        updateLoading.value = false;
        message.value = '';

        email.value = personalInfo.value.email;
        nickname.value = personalInfo.value.nickname;
        phone.value = personalInfo.value.phone;
        language.value = personalInfo.value.language;
        wechat.value = personalInfo.value.wechat;
        alipay.value = personalInfo.value.alipay;

        if (language.value !== locale.value) {
          locale.value = language.value;
        }

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
  }
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
        <h3 class="font-16 mb-2">{{ t('account.personal_info') }}</h3>
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
              type="email"
              class="form-control rounded-xs"
              id="profile_email"
              v-model="email"
            />
            <label
              for="profile_email"
              class="color-highlight form-label-always-active"
            >
              {{ t('account.email') }}
            </label>
            <span>{{ t('account.required') }}</span>
          </div>
          <div class="form-custom form-label form-border mb-3 bg-transparent">
            <input
              type="text"
              class="form-control rounded-xs"
              id="profile_nickname"
              v-model="nickname"
            />
            <label
              for="profile_nickname"
              class="form-label-always-active color-highlight"
            >
              {{ t('account.nickname') }}
            </label>
            <span>{{ t('account.required') }}</span>
          </div>
          <div class="form-custom form-label form-border mb-3 bg-transparent">
            <input
              type="tel"
              class="form-control rounded-xs"
              id="profile_phone"
              v-model="phone"
            />
            <label
              for="profile_phone"
              class="color-highlight form-label-always-active"
            >
              {{ t('account.phone_number') }}
            </label>
            <span>{{ t('account.required') }}</span>
          </div>
          <div class="form-custom form-label form-border mb-3 bg-transparent">
            <select
              class="form-select rounded-xs"
              id="profile_language"
              aria-label="Floating label select example"
              v-model="language"
            >
              <option
                v-for="language in LanguageConstants.fe"
                :key="language"
                :value="language"
              >
                {{ LanguageConstants.feName[language] }}
              </option>
            </select>
            <label
              for="profile_phone"
              class="color-highlight form-label-always-active"
            >
              {{ t('account.language') }}
            </label>
          </div>
          <div class="form-custom form-label form-border mb-3 bg-transparent">
            <input
              type="tel"
              class="form-control rounded-xs"
              id="profile_wechat"
              v-model="wechat"
            />
            <label
              for="profile_wechat"
              class="color-highlight form-label-always-active"
            >
              {{ t('account.wechat') }}
            </label>
          </div>
          <div class="form-custom form-label form-border mb-3 bg-transparent">
            <input
              type="tel"
              class="form-control rounded-xs"
              id="profile_alipay"
              v-model="alipay"
            />
            <label
              for="profile_alipay"
              class="color-highlight form-label-always-active"
            >
              {{ t('account.alipay') }}
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
            class="btn btn-full gradient-green shadow-bg shadow-bg-s mt-4"
            @click="handleUpdate"
          >
            {{ t('account.apply_button') }}
          </div>
        </template>
      </div>
    </div>
  </div>

</template>
