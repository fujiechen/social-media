<script setup>

import { onMounted, ref } from "vue";
import {useAuthStore} from "@/stores/auth.store";
import { storeToRefs } from "pinia";
import { useMetaStore } from "@/stores/meta.store";
import { useRouter } from "vue-router";

const router = useRouter();

const metaStore = useMetaStore();
const {metas} = storeToRefs(metaStore);

const authStore = useAuthStore();
const username = ref('')
const password = ref('')

const captcha = ref({
  captcha_key: '',
  captcha_image: '',
  captcha_input: '',
  loading: true,
});

onMounted(async () => {
  await getCaptcha();
});

const getCaptcha = async () => {
  captcha.value.loading = true;
  const captchaResponse =  await authStore.getCaptcha();
  captcha.value.captcha_key = captchaResponse.captcha_key;
  captcha.value.captcha_image = captchaResponse.captcha_image;
  captcha.value.captcha_input = '';
  captcha.value.loading = false;
};


const login = async () => {
  const success = await authStore.login(
    username.value,
    password.value,
    captcha.value.captcha_key,
    captcha.value.captcha_input,
  );

  if (!success) {
    await getCaptcha();
  }
}

const onClickRegistrationHtml = () => {
  router.push({
    name: 'help',
  });
};

</script>

<template>
  <van-form @submit="login">
    <van-space direction="vertical" fill>
      <van-row
        @click="onClickRegistrationHtml"
        justify="center"
        class="my-3"
        style="color: var(--van-primary-color) !important;"
      >
        <span
          v-if="metas.find(item => item.meta_key === 'REGISTRATION_HTML')"
          v-html="metas.find(item => item.meta_key === 'REGISTRATION_HTML').meta_value"
        ></span>
        <span v-else>
          <b>注册送积分，分享赚积分，积分解锁更多视频</b>
        </span>
      </van-row>
      <van-cell-group inset>
        <van-field
          v-model="username"
          name="username"
          label="登录名"
          placeholder="登录名"
          size="large"
          :rules="[{ required: true, message: '请输入您的登录名' }]"
        />
        <van-field
          v-model="password"
          type="password"
          name="password"
          label="密码"
          placeholder="密码"
          size="large"
          :rules="[{ required: true, message: '请输入您的密码' }]"
        />
        <div class="captcha-container">
          <van-field
            v-model="captcha.captcha_input"
            name="captcha"
            label="验证码"
            placeholder="验证码"
            size="large"
            :rules="[{ required: true, message: '请输入下方验证码' }]"
          />
          <van-loading v-if="captcha.loading || !captcha.captcha_image" class="text-center">
          </van-loading>
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
      </van-cell-group>

      <div class="login-links">
        <router-link :to="{name:'registration'}"><b>免费注册</b></router-link>
        |
        <router-link :to="{name:'reset'}">忘记密码</router-link>
      </div>
      <van-divider/>
      <van-cell-group inset>
        <van-button block round type="success" native-type="submit">
          登录
        </van-button>
      </van-cell-group>
    </van-space>
  </van-form>
</template>
<style>
.captcha-container {
  display: flex;
  flex-grow: 1;
  background: var(--van-cell-background);
  padding-right: 1rem;
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
.login-links {
  text-align: center;
  margin-top: 20px;
  color: var(--van-primary-color) !important;
}
.login-links a {
  color: var(--van-primary-color) !important;
}
</style>
