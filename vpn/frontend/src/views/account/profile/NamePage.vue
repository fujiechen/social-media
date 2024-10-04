<script setup>

import {useUserStore} from "@/stores/user.store";
import {onMounted} from "vue";
import {storeToRefs} from "pinia";

const userStore = useUserStore();
const {user} = storeToRefs(userStore);

onMounted(() => {
  userStore.fetchUser();
});

const updateUser = async () => {
  await userStore.updateUserNickname();
}

</script>

<template>
  <van-form @submit="updateUser">
    <van-cell-group inset>
      <van-field
          v-model="user.nickname"
          label="昵称"
          placeholder="昵称"
          required
          :rules="[{ required: true, message: '请输入您的昵称' }]"
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
