<script setup>

import {useUserStore} from "@/stores/user.store";
import {ref} from "vue";
import {useRoute} from "vue-router";
import {showToast} from "vant";

const route = useRoute();
const userStore = useUserStore();
const newPassword = ref('');
const passwordConfirmation = ref('');

const updateUserPassword = async () => {
  if (route.params && 'token' in route.params) {
    await userStore.confirmResetUserPassword(route.params.token, newPassword.value, passwordConfirmation.value);
  } else {
    showToast({
      message: '更新失败, 请重新获取密码重置邮件',
      wordBreak: 'break-word',
    });
  }
}

</script>

<template>
  <van-form @submit="updateUserPassword">
    <van-cell-group inset>
      <van-field
        type="password"
        v-model="newPassword"
        label="新密码"
        placeholder="新密码"
        required
        :rules="[{ required: true, message: '请输入您的新密码' }]"
      />
      <van-field
        size="mini"
        type="password"
        v-model="passwordConfirmation"
        label="验证新密码"
        placeholder="验证新密码"
        required
        :rules="[{ required: true, message: '请验证您的新密码' }]"
      />
    </van-cell-group>
    <van-divider/>
    <van-cell-group inset>
      <van-button block round type="success" native-type="submit">
        更新
      </van-button>
    </van-cell-group>
  </van-form>
</template>
