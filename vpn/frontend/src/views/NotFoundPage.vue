<template>
  <van-space
    class="container"
    align="center"
    direction="vertical"
    fill
  >
    <van-row>
      <h1>页面不存在</h1>
    </van-row>
    <van-row>
      <p>此页面不存在</p>
    </van-row>
    <van-row>
      <p>请检查链接，此页面或许已删除</p>
    </van-row>
    <router-link :to="{name:'home'}">
      <van-button type="primary" size="large" class="back-button">
        返回首页
      </van-button>
    </router-link>
  </van-space>
</template>
<style scoped>
.container {
  height: 100%;
  padding: 10px;
  margin-top: 20vh;
}
.back-button {
  width: 250px;
}
</style>
<script setup>
</script>
