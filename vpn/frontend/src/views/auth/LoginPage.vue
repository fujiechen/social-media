<script setup>

import {onMounted, ref} from 'vue'
import {useAuthStore} from "@/stores/auth.store";

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
  const captchaResponse = await authStore.getCaptcha();
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

</script>

<template>
  <van-form @submit="login">
    <van-space direction="vertical" fill>
      <van-row justify="center">
        <van-image
          src="./logo2.png"
        />
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
          <van-loading v-if="captcha.loading || !captcha.captcha_image" class="captcha-loading">
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

      <div style="text-align: center; margin-top: 20px;">
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
.captcha-loading {
  margin-top: .5rem;
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
