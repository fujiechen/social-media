<script setup>
import { storeToRefs } from "pinia";
import { onUnmounted } from "vue";
import { useUserShareStore } from "@/stores/userShare.store";

const userShareStore = useUserShareStore();

const { sharePopup } = storeToRefs(userShareStore);

onUnmounted(async () => {
  sharePopup.value.show = false;
});
</script>

<template>
  <van-popup
    v-model:show="sharePopup.show"
    position="bottom"
    closeable
    round
    :style="{ height: '100%' }"
  >
    <div
      v-if="sharePopup.loading"
      class="share-popup-loading-container"
    >
      <van-loading size="30px">
        加载中...
      </van-loading>
    </div>
    <div
      v-else
      class="share-popup-content-container"
    >
      <div class="share-popup-content-inner-container">
        <van-row justify="center">
          <van-image
            class="share-popup-content-bg-img"
            :src="sharePopup.userShare?.background_image?.url"
          />
        </van-row>
        <van-row justify="center">
          <p class="share-popup-content-share-text">
            {{sharePopup.userShare.share_text}}
          </p>
        </van-row>
        <van-row>
          <object
            class="share-popup-content-qr-code"
            :data="sharePopup.userShare.qr_code_image_url"
            type="image/svg+xml"
          />
        </van-row>
        <van-row justify="center">
          <p class="share-popup-content-qr-code-text">长按识别二维码</p>
        </van-row>
        <van-row justify="center">
          <p class="share-popup-content-screenshot-text">请截图保存分享</p>
        </van-row>
      </div>
    </div>
  </van-popup>
</template>

<style scoped>
.share-popup-loading-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
}

.share-popup-content-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  background-color: var(--van-background);
}

.share-popup-content-inner-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin: 20px;
  background-color: var(--van-background);
  min-width: 90%;
  min-height: 90%;
}

.share-popup-content-bg-img {
  display: block;
  margin-bottom: 10px;
  width: 90%;
}

.share-popup-content-share-text {
  font-size: 16px;
}

.share-popup-content-qr-code {
  width: 120px;
  height: 120px;
  margin-top: 5px;
}

.share-popup-content-qr-code-text {
  font-size: 16px;
}

.share-popup-content-screenshot-text {
  font-size: 13px;
}

</style>
