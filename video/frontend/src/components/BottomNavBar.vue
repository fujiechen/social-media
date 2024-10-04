<script setup>
import { computed } from "vue";
import { useRoute } from 'vue-router'

const route = useRoute()
const active = computed(() => route.name)
</script>

<template>
  <van-tabbar :modelValue="active">
    <van-tabbar-item name="home" icon="home-o" :to="{name:'home'}">首页</van-tabbar-item>
    <van-tabbar-item name="subscription" icon="like-o" :to="{name:'subscription'}" >关注</van-tabbar-item>
    <van-tabbar-item name="accountMedias" icon="plus" :to="{name:'accountMedias'}" ></van-tabbar-item>
    <van-tabbar-item name="products" icon="shopping-cart-o" :to="{name:'products'}">商城</van-tabbar-item>
    <van-tabbar-item name="mine" icon="contact-o" :to="{name:'mine'}">我的</van-tabbar-item>
  </van-tabbar>
</template>
