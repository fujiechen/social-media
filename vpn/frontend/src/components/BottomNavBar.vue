<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const active = computed(() => route.name)
</script>


<template>
  <van-tabbar :modelValue="active">
    <van-tabbar-item name="home" icon="home-o" :to="{name:'home'}">首页</van-tabbar-item>
    <van-tabbar-item name="tutorial" icon="notes-o" :to="{name:'tutorial',params:{os:'share'}}">教程</van-tabbar-item>
    <van-tabbar-item name="app" icon="apps-o" :to="{name:'app'}">导航</van-tabbar-item>
    <van-tabbar-item name="vpn" icon="desktop-o" :to="{name:'vpn'}" >VPN</van-tabbar-item>
    <van-tabbar-item name="mine" icon="contact" :to="{name:'mine'}">我的</van-tabbar-item>
  </van-tabbar>
</template>
