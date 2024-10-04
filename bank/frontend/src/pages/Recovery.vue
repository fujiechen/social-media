<script setup>
import { computed, ref, onBeforeMount } from 'vue';
import { useI18n } from 'vue-i18n';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import NonAuthPageContainer from '../components/PageContainers/NonAuthPageContainer.vue';
import LanguageSwitch from '../components/TopButtons/LanguageSwitch.vue';

const { t, locale } = useI18n({ useScope: 'global' });
const store = useStore();
const router = useRouter();

const loggedIn = computed(() => store.state.auth.status.loggedIn);

const loading = ref(false);
const message = ref('');
const messageStatus = ref('bg-fade-red color-red-dark');

const email = ref('');

onBeforeMount(() => {
  if (loggedIn.value) {
    router.push({ name: 'home' });
  }
});

const handleReset = () => {
  loading.value = true;
  message.value = '';
  messageStatus.value = 'bg-fade-red color-red-dark';

  if (email.value.length === 0) {
    message.value = t('recovery.errors.missing_email');
    loading.value = false;
  } else {
    store.dispatch('auth/reset', {
      email: email.value,
      language: locale.value,
    }).then(() => {
      loading.value = false;
      message.value = t('recovery.message.success');
      messageStatus.value = 'bg-green-light shadow-bg shadow-bg-m';
    }).catch((error) => {
      if (error?.response?.data?.errors) {
        try {
          const { errors } = error.response.data;
          [message.value] = errors[Object.keys(errors)[0]];
        } catch {
          message.value = t('recovery.errors.general');
        }
      } else {
        message.value = t('recovery.errors.general');
      }
      messageStatus.value = 'bg-fade-red color-red-dark';
      loading.value = false;
    });
  }
};
const logoPath = `${process.env.VUE_APP_SUB_PATH}logo.png`;
</script>

<template>
  <NonAuthPageContainer>
    <div class="card bg-5 card-fixed" v-if="!loggedIn">
      <div class="card-center mx-3 px-4 py-4 bg-white rounded-m">
        <div class="d-flex">
          <div class="align-self-center me-auto">
            <img
              :src="logoPath"
              alt="logo"
              @click="router.push({name:'home'})"
              style="width: 150px"
            />
          </div>
          <div class="align-self-center ms-auto">
            <LanguageSwitch/>
          </div>
        </div>
        <p>{{ t('recovery.title') }}</p>
        <div
          v-if="message"
          :class="`alert ${messageStatus} alert-dismissible rounded-s fade show`"
          role="alert"
        >
          <i class="bi bi-exclamation-triangle pe-2"></i>
          {{ message }}
          <button type="button" class="btn-close opacity-20 font-11 pt-3 mt-1"
                  data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="form-custom form-label form-border form-icon mb-3 bg-transparent">
          <i class="bi bi-at font-16"></i>
          <input
            v-model="email"
            type="email"
            class="form-control rounded-xs"
            id="recovery_email"
            :placeholder="t('recovery.email_label')"
          />
          <label
            for="recovery_email"
            class="color-theme"
          >
            {{ t('recovery.email_label') }}
          </label>
          <span>{{ t('recovery.required') }}</span>
        </div>
        <a
          v-if="loading"
          disabled
          class="btn btn-full gradient-highlight shadow-bg shadow-bg-s mt-4"
        >
          <span
            class="spinner-border spinner-border-sm"
          ></span>
        </a>
        <a
          v-else
          @click="handleReset"
          class="btn btn-full gradient-highlight shadow-bg shadow-bg-s mt-4"
        >
          {{ t('recovery.send_recovery_instructions_button') }}
        </a>
        <div class="row">
          <div class="col-6 text-start">
            <router-link
              :to="{name:'signUp'}"
              class="font-11 color-theme opacity-40 pt-4 d-block"
            >
              {{ t('recovery.create_account') }}
            </router-link>
          </div>
          <div class="col-6 text-end">
            <router-link
              :to="{name:'signIn'}"
              class="font-11 color-theme opacity-40 pt-4 d-block"
            >
              {{ t('recovery.sign_in_account') }}
            </router-link>
          </div>
        </div>
      </div>
      <div
        class="card-overlay rounded-0 m-0 bg-black opacity-70"
        @click="router.push({name:'home'})"
      ></div>
    </div>
  </NonAuthPageContainer>
</template>
