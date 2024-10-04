<script setup>
import { computed, ref } from 'vue';
import { useStore } from 'vuex';
import { useI18n } from 'vue-i18n';
import PageTitle from '../../PageTitle/PageTitle.vue';

const { t } = useI18n({ useScope: 'global' });
const store = useStore();

const user = computed(() => store.state.auth.user);

const updateLoading = ref(false);
const message = ref('');
const successMessage = ref('');

const oldPassword = ref('');
const newPassword = ref('');
const newPasswordConfirm = ref('');
const username = ref(user.value.username);

const handleUpdate = () => {
  updateLoading.value = true;
  message.value = '';

  if (oldPassword.value.length === 0) {
    message.value = t('account.errors.missing_old_password');
    updateLoading.value = false;
  } else if (newPassword.value.length > 0 && newPasswordConfirm.value.length === 0) {
    message.value = t('account.errors.missing_password_confirm');
    updateLoading.value = false;
  } else if (newPassword.value.length === 0 && newPasswordConfirm.value.length > 0) {
    message.value = t('account.errors.missing_new_password');
    updateLoading.value = false;
  } else if (newPassword.value !== newPasswordConfirm.value) {
    message.value = t('account.errors.password_not_same');
    updateLoading.value = false;
  } else {
    store.dispatch('userProfile/updateAuth', {
      oldPassword: oldPassword.value,
      password: newPassword.value,
      confirmPassword: newPasswordConfirm.value,
      username: username.value,
    })
      .then(() => {
        updateLoading.value = false;
        message.value = '';

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
        <h3 class="font-16 mb-2">{{ t('account.account_security') }}</h3>
      </div>
    </div>
  </div>
  <div class="card card-style">
    <div class="content pb-0 pt-4">
      <div class="content mt-0">
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
            type="password"
            class="form-control rounded-xs"
            id="old_password"
            v-model="oldPassword"
          />
          <label
            for="old_password"
            class="color-highlight form-label-always-active"
          >
            {{ t('account.current_password') }}
          </label>
          <span>{{ t('account.required') }}</span>
        </div>
        <div class="form-custom form-label form-border mb-3 bg-transparent">
          <input
            type="password"
            class="form-control rounded-xs"
            id="new_password"
            v-model="newPassword"
          />
          <label
            for="new_password"
            class="color-highlight form-label-always-active"
          >
            {{ t('account.new_password') }}
          </label>
        </div>
        <div class="form-custom form-label form-border mb-4 bg-transparent">
          <input
            type="password"
            class="form-control rounded-xs"
            id="new_password_confirm"
            v-model="newPasswordConfirm"
          />
          <label
            for="new_password_confirm"
            class="color-highlight form-label-always-active"
          >
            {{ t('account.new_password_confirm') }}
          </label>
        </div>
        <div class="form-custom form-label form-border mb-4 bg-transparent">
          <input
            type="text"
            class="form-control rounded-xs"
            id="username"
            v-model="username"
          />
          <label
            for="username"
            class="color-highlight form-label-always-active"
          >
            {{ t('account.username') }}
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
      </div>
    </div>
  </div>
</template>
