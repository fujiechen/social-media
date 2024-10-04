<script setup>

import { onMounted, ref, watch } from "vue";
import {useAuthStore} from "@/stores/auth.store";

const authStore = useAuthStore();

const email = ref('');
const nickname = ref('');
const username = ref('');
const password = ref('');
const confirmPassword = ref('');
const language = ref('zh');
const userShareId = ref(null);
const userShareIdFromLocal = localStorage.getItem('cloud-user_share_id');

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

if (userShareIdFromLocal != null) {
  userShareId.value = userShareIdFromLocal;
}

const register = async () => {
  const success = await authStore.register(
    username.value,
    password.value,
    confirmPassword.value,
    nickname.value,
    email.value,
    language.value,
    userShareId.value,
    captcha.value.captcha_key,
    captcha.value.captcha_input,
  );

  if (!success) {
    await getCaptcha();
  }
}

watch(email, (newEmail) => {
  username.value = newEmail;
});

</script>

<template>
  <van-form @submit="register">
    <van-space direction="vertical" fill>

      <van-row justify="center">
        <van-image
            src="./logo2.png"
        />
      </van-row>

      <van-row justify="center">
        <b>个人信息</b>
      </van-row>

      <van-cell-group inset>
        <van-field
            v-model="nickname"
            label="昵称"
            placeholder="昵称"
            required
            :rules="[{ required: true, message: '请输入您的昵称' }]"
        />
        <van-field
            v-model="email"
            name="email"
            label="邮箱"
            placeholder="邮箱"
            required
            :rules="[{ required: true, message: '请输入您的邮箱' }]"
        />
        <van-field
            v-model="userShareId"
            name="userShareId"
            label="邀请码"
            placeholder="邀请码"
            :rules="[{ required: false, message: '请输入您的邀请码' }]"
        />
      </van-cell-group>


      <van-row justify="center">
        <b>登录信息</b>
      </van-row>

      <van-cell-group inset>
        <van-field
            v-model="username"
            name="username"
            label="登录名"
            placeholder="登录名"
            required
            :rules="[{ required: true, message: '请输入您的登录名' }]"
        />
        <van-field
            v-model="password"
            type="password"
            name="Password"
            label="密码"
            placeholder="密码"
            required
            :rules="[{ required: true, message: '请输入您的密码' }]"
        />
        <van-field
            v-model="confirmPassword"
            type="password"
            name="confirmPassword"
            label="确认密码"
            placeholder="确认密码"
            required
            :rules="[{ required: true, message: '请输入您的确认密码' }]"
        />
        <div class="captcha-container">
          <van-field
            v-model="captcha.captcha_input"
            name="captcha"
            label="验证码"
            placeholder="验证码"
            :rules="[{ required: true, message: '请输入下方验证码' }]"
            required
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

      <van-row justify="center">
        <p>
          已经拥有账户?
          <router-link :to="{name:'login'}">登录</router-link>
        </p>
      </van-row>

      <van-divider/>

      <van-cell-group inset>
        <van-button block round type="success" native-type="submit">
          免费注册
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
}
.captcha-image-container {
  display: flex;
  justify-content: center;
  margin-top: .5rem;
  width: 60px;
  height: 32px;
}
.captcha-image {
  width: 60px;
  height: 32px;
}
</style>
