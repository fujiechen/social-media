<script setup>

import {useUserStore} from "@/stores/user.store";
import {onMounted} from "vue";
import {storeToRefs} from "pinia";

const userStore = useUserStore();
const {user} = storeToRefs(userStore);

onMounted(async () => {
  await userStore.fetchUnionUser();
});

const updateUser = async () => {
  await userStore.updateUserEmail(user.value.email);
};

</script>

<template>
  <van-form @submit="updateUser">
    <van-cell-group inset>
      <van-field
          v-model="user.email"
          label="邮箱"
          placeholder="邮箱"
          required
          :rules="[{ required: true, message: '请输入您的邮箱' }]"
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
