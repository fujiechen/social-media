<script setup>
import { useUserShareStore } from "@/stores/userShare.store";
import { storeToRefs } from "pinia";

const userShareStore = useUserShareStore();
const { newUserShare, loadingNewUserShare, showUserSharePopup } = storeToRefs(userShareStore);

</script>

<template>
  <van-popup
    v-model:show="showUserSharePopup"
    position="bottom"
    closeable
    round
    :style="{ height: '100%' }"
  >
    <div
      v-if="loadingNewUserShare"
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
            :src="newUserShare.background_image.url"
          />
        </van-row>
        <van-row justify="center">
          <p class="share-popup-content-share-text">
            {{newUserShare.share_text}}
          </p>
        </van-row>
        <van-row>
          <object
            class="share-popup-content-qr-code"
            :data="newUserShare.qr_code_image_url"
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
  background-color: #f7f7f7;
}

.share-popup-content-inner-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin: 20px;
  background-color: #fff;
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
