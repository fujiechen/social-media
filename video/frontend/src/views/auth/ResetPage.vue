<script setup>

import {useUserStore} from "@/stores/user.store";
import {ref} from "vue";

const userStore = useUserStore();
const email = ref('');

const sendResetEmail = async () => {
  await userStore.sendResetEmail(email.value);
}

</script>

<template>
  <van-form @submit="sendResetEmail">
    <van-cell-group inset>
      <van-field
        v-model="email"
        label="邮箱"
        placeholder="邮箱"
        required
        :rules="[{ required: true, message: '请输入您的邮箱' }]"
        class="mt-3"
      />
    </van-cell-group>
    <van-divider/>
    <van-cell-group inset>
      <van-button block round type="success" native-type="submit">
        重置密码
      </van-button>
    </van-cell-group>
  </van-form>
</template>
