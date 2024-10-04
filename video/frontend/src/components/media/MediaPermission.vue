<script setup>
import { computed } from "vue";

const props = defineProps({
  data: {
    type: Object,
  },
  clickPurchase: {
    type: Function,
    default: () => {},
  },
});

const mediaName = computed(() => {
  if (props.data.type === 'Video') {
    return '视频';
  } else if (props.data.type === 'Album') {
    return '图集';
  }
  return '合集';
});

</script>
<template>
  <div
    class="mx-3"
  >
    <div
      v-if="props.data.meta.user.registration_redirect"
      @click="props.clickPurchase"
    >
      <van-row align="center">
        <span class="permission-title">
          注册专享
        </span>
        <van-divider vertical :hairline="false" :style="{ borderColor: '#9f1447' }" />
        <span class="flex-grow-1 permission-description">
          免费注册会员
        </span>
        <van-button class="permission-button" type="danger" size="small" round plain>
          去注册
        </van-button>
      </van-row>
    </div>
    <div
      v-else-if="props.data.meta.user.membership_redirect"
      @click="props.clickPurchase"
    >
      <van-row align="center">
        <span class="permission-title">
          VIP专享
        </span>
        <van-divider vertical :hairline="false" :style="{ borderColor: '#9f1447' }" />
        <span class="flex-grow-1 permission-description">
          解锁上万个精彩视频图片
        </span>
        <van-button class="permission-button" type="danger" size="small" round plain>
          立即解锁
        </van-button>
      </van-row>
    </div>
    <div
      v-else-if="props.data.meta.user.product_redirect"
      @click="props.clickPurchase"
    >
      <van-row align="center">
        <span class="permission-title">
          产品专享
        </span>
        <van-divider vertical :hairline="false" :style="{ borderColor: '#9f1447' }" />
        <span class="flex-grow-1 permission-description">
          解锁当前精彩{{mediaName}}
        </span>
        <van-button class="permission-button" type="danger" size="small" round plain>
          立即解锁
        </van-button>
      </van-row>
    </div>
    <div
      v-else-if="props.data.meta.user.subscription_redirect"
      @click="props.clickPurchase"
    >
      <van-row align="center">
        <span class="permission-title">
          订阅专享
        </span>
        <van-divider vertical :hairline="false" :style="{ borderColor: '#9f1447' }" />
        <span class="flex-grow-1 permission-description">
          关注{{props.data.user.nickname}}精彩{{mediaName}}
        </span>
        <van-button class="permission-button" type="danger" size="small" round plain>
          立即关注
        </van-button>
      </van-row>
    </div>
  </div>
</template>
<style>
.permission-title {
  color: #9f1447;
  font-size: 1rem;
  font-weight: 800;
}

.permission-description {
  color: #9f1447;
  font-size: .9rem;
}

.permission-button {
  border-color: #9f1447 !important;
  color: #9f1447 !important;
}
</style>
