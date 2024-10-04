<script setup>
import {
  computed, onBeforeMount, ref, watch, onMounted,
} from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import NonAuthPageContainer from '../components/PageContainers/NonAuthPageContainer.vue';
import LanguageSwitch from '../components/TopButtons/LanguageSwitch.vue';

const { t, locale } = useI18n({ useScope: 'global' });
const store = useStore();
const router = useRouter();

const loggedIn = computed(() => store.state.auth.status.loggedIn);
onBeforeMount(() => {
  if (loggedIn.value) {
    router.push({ name: 'home' });
  }
});

const loading = ref(false);
const message = ref('');

const nickname = ref('');
const email = ref('');
const username = ref('');
const password = ref('');
const passwordConfirm = ref('');

const captcha = ref({
  captcha_key: '',
  captcha_image: '',
  captcha_input: '',
  loading: true,
});

const getCaptcha = async () => {
  captcha.value.loading = true;

  try {
    const captchaResponse = await store.dispatch('auth/getCaptcha');

    captcha.value.captcha_key = captchaResponse.captcha_key;
    captcha.value.captcha_image = captchaResponse.captcha_image;
    captcha.value.captcha_input = '';
  } catch (e) {
    message.value = '验证码获取失败，请刷新重试';
    captcha.value.captcha_key = '';
    captcha.value.captcha_image = '';
    captcha.value.captcha_input = '';
  }

  captcha.value.loading = false;
};

onMounted(async () => {
  await getCaptcha();
});

const handleRegister = () => {
  loading.value = true;
  message.value = '';

  if (nickname.value.length === 0) {
    message.value = t('sign_up.errors.missing_name');
    loading.value = false;
  } else if (email.value.length === 0) {
    message.value = t('sign_up.errors.missing_email');
    loading.value = false;
  } else if (username.value.length === 0) {
    message.value = '请输入登录名';
    loading.value = false;
  } else if (password.value.length === 0) {
    message.value = t('sign_up.errors.missing_password');
    loading.value = false;
  } else if (passwordConfirm.value.length === 0) {
    message.value = t('sign_up.errors.missing_password_confirm');
    loading.value = false;
  } else if (passwordConfirm.value !== password.value) {
    message.value = t('sign_up.errors.password_not_same');
    loading.value = false;
  } else if (captcha.value.captcha_input.length === 0) {
    message.value = '请输入验证码';
    loading.value = false;
  } else {
    store.dispatch('auth/register', {
      nickname: nickname.value,
      email: email.value,
      username: username.value,
      password: password.value,
      passwordConfirm: passwordConfirm.value,
      language: locale.value,
      captcha_key: captcha.value.captcha_key,
      captcha: captcha.value.captcha_input,
    }).then((loginUser) => {
      locale.value = loginUser.language;
      router.push({ name: 'home' });
    }).catch((error) => {
      if (error?.response?.data?.errors) {
        try {
          const { errors } = error.response.data;
          [message.value] = errors[Object.keys(errors)[0]];
          if (errors[Object.keys(errors)[0]][0] === 'validation.captcha_api') {
            message.value = '验证码错误';
          }
        } catch {
          message.value = t('sign_up.errors.general');
        }
      } else {
        message.value = t('sign_up.errors.general');
      }
      loading.value = false;
      getCaptcha();
    });
  }
};

watch(email, (newEmail) => {
  username.value = newEmail;
});

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
        <p>{{ t('sign_up.title') }}</p>
        <div
          v-if="message"
          class="alert bg-fade-red color-red-dark alert-dismissible rounded-s fade show"
          role="alert"
        >
          <i class="bi bi-exclamation-triangle pe-2"></i>
          {{ message }}
          <button type="button" class="btn-close opacity-20 font-11 pt-3 mt-1"
                  data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="form-custom form-label form-border form-icon mb-3 bg-transparent">
          <i class="bi bi-person-circle font-13"></i>
          <input type="text" class="form-control rounded-xs" id="nickname" v-model="nickname"
                 :placeholder="t('sign_up.nickname_label')"/>
          <label for="nickname" class="color-theme">{{ t('sign_up.nickname_label') }}</label>
          <span>{{ t('sign_up.required') }}</span>
        </div>
        <div class="form-custom form-label form-border form-icon mb-3 bg-transparent">
          <i class="bi bi-at font-16"></i>
          <input type="email" class="form-control rounded-xs" id="email" v-model="email"
                 :placeholder="t('sign_up.email_label')"/>
          <label for="email" class="color-theme">{{ t('sign_up.email_label') }}</label>
          <span>{{ t('sign_up.required') }}</span>
        </div>
        <div class="form-custom form-label form-border form-icon mb-3 bg-transparent">
          <i class="bi bi-door-open-fill font-13"></i>
          <input type="text" class="form-control rounded-xs" id="nickname" v-model="username"
                 placeholder="登录名"/>
          <label for="nickname" class="color-theme">登录名</label>
          <span>{{ t('sign_up.required') }}</span>
        </div>
        <div class="form-custom form-label form-border form-icon mb-3 bg-transparent">
          <i class="bi bi-asterisk font-13"></i>
          <input type="password" class="form-control rounded-xs" id="password" v-model="password"
                 :placeholder="t('sign_up.first_password_label')"/>
          <label for="password" class="color-theme">{{ t('sign_up.first_password_label') }}</label>
          <span>{{ t('sign_up.required') }}</span>
        </div>
        <div class="form-custom form-label form-border form-icon mb-4 bg-transparent">
          <i class="bi bi-asterisk font-13"></i>
          <input type="password" class="form-control rounded-xs" id="password_confirm"
                 v-model="passwordConfirm" :placeholder="t('sign_up.second_password_label')"/>
          <label for="password_confirm"
                 class="color-theme">{{ t('sign_up.second_password_label') }}</label>
          <span>{{ t('sign_up.required') }}</span>
        </div>
        <div class="d-flex justify-content-center">
          <div class="form-custom form-label form-border form-icon mb-4 bg-transparent flex-grow-1">
            <i class="bi bi-asterisk font-13"></i>
            <input
              id="captcha"
              class="form-control rounded-xs"
              v-model="captcha.captcha_input"
              placeholder="验证码"
            />
            <label for="captcha" class="color-theme">
              验证码
            </label>
            <span>{{ t('sign_up.required') }}</span>
          </div>
          <div v-if="captcha.loading || !captcha.captcha_image" class="captcha-loading">
            加载中
          </div>
          <div
            v-else
            class="captcha-image-container"
          >
            <img
              :src="captcha.captcha_image"
              alt="captcha image"
              class="captcha-image"
              @click="getCaptcha"
            />
          </div>
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
          @click="handleRegister"
          class="btn btn-full gradient-highlight shadow-bg shadow-bg-s mt-4"
        >
          {{ t('sign_up.create_account') }}
        </a>
        <div class="row">
          <div class="col-6 text-start">
            <router-link
              :to="{name:'recovery'}"
              class="font-11 color-theme opacity-40 pt-4 d-block"
            >
              {{ t('sign_up.forgot_password') }}
            </router-link>
          </div>
          <div class="col-6 text-end">
            <router-link
              :to="{name: 'signIn'}"
              class="font-11 color-theme opacity-40 pt-4 d-block"
            >
              {{ t('sign_up.sign_in_account') }}
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
<style>
.captcha-loading {
  text-align: center;
}
.captcha-image-container {
  display: flex;
  justify-content: center;
  width: 60px;
  height: 32px;
  margin-top: .5rem;
}
.captcha-image {
  width: 60px;
  height: 32px;
}
</style>
