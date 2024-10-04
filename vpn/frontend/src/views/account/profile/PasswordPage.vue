<script setup>

import {useUserStore} from "@/stores/user.store";
import {ref} from "vue";

const userStore = useUserStore();

const oldPassword = ref('');
const newPassword = ref('');
const passwordConfirmation = ref('');

const updateUserPassword = async () => {
  await userStore.updateUserPassword(oldPassword.value, newPassword.value, passwordConfirmation.value);
}

</script>

<template>
  <van-form @submit="updateUserPassword">
    <van-cell-group inset>
      <van-field
          type="password"
          v-model="oldPassword"
          label="当前密码"
          placeholder="当前密码"
          required
          :rules="[{ required: true, message: '请输入您当前密码' }]"
      />
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
