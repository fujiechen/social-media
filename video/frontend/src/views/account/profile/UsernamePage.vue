<script setup>

import {useUserStore} from "@/stores/user.store";
import {onMounted, ref} from "vue";
import {storeToRefs} from "pinia";

const userStore = useUserStore();
const {user} = storeToRefs(userStore);

const oldPassword = ref('');

onMounted(async () => {
  await userStore.fetchUnionUser();
});

const updateUser = async () => {
  await userStore.updateUserUsername(oldPassword.value, user.value.username);
};

</script>

<template>
  <van-form @submit="updateUser">
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
          size="mini"
          type="text"
          v-model="user.username"
          label="登录名"
          placeholder="登录名"
          required
          :rules="[{ required: true, message: '请输入您的登录名' }]"
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
