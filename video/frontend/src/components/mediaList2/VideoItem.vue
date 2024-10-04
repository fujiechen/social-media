<script setup>
import { secondsToDurationString } from "@/helpers/date";
import { formatNumberToChineseDecimal } from "@/utils";
import { ref } from "vue";
import { useRouter } from "vue-router";
const router = useRouter();

const props = defineProps({
  media: {
    type: Object,
    required: true,
  },
});

const openItem = () => {
  router.push({
    name: "media",
    params: {
      id: props.media.id,
    },
  });
};

const isPlaying = ref(false);

const playPreview = () => {
  const newValue = !isPlaying.value;
  isPlaying.value = newValue;
  if (!newValue) {
    thumbnailHide.value = false;
  }
};

const thumbnailHide = ref(false);

const canPlay = () => {
  thumbnailHide.value = !!isPlaying.value;
};

</script>
<template>
  <div @click="openItem" class="w-100">
    <video
      v-if="isPlaying"
      width="100%"
      autoplay
      loop
      muted
      playsinline
      :src="media.preview_file.url"
      v-on:canplaythrough="canPlay"
    >
    </video>
    <div v-else class="media-list-2-thumbnail-container">
      <van-image
        class="media-list-2-thumbnail"
        fit="cover"
        position="center"
        :src="media?.thumbnail_file?.url || 'error'"
      />
      <div class="video-duration">
        {{ secondsToDurationString(media.duration_in_seconds) }}
      </div>
    </div>
  </div>
  <van-text-ellipsis
    class="fs-m"
    :content="media.name"
  />
  <div class="d-flex justify-content-between align-items-center text-secondary media-list-2-info-container">
    <span class="fs-m nickname">{{ media.user.nickname }}</span>
    <div class="d-flex align-items-center">
      <van-icon
        v-if="media.meta.user.like"
        color="#f53636"
        name="like"
        size="1em"
      />
      <van-icon
        v-else
        name="like-o"
        size="1em"
      />
      <span class="fs-m ms-1 me-2">{{ formatNumberToChineseDecimal(media.meta.count.likes) }}</span>
      <div
        class="preview-container d-flex justify-content-between align-items-center"
        v-if="media.type === 'Video' && !!media?.preview_file?.url"
        @click="playPreview()"
      >
        <van-button
          :icon="isPlaying ? 'pause' : 'play'"
          color="#fc7488"
          size="mini"
          type="default"
          hairline
          round
          :loading="isPlaying && !thumbnailHide"
          loading-text="加载中"
          loading-type="spinner"
        >
          预览
        </van-button>
      </div>
    </div>
  </div>
</template>
<style>
.media-list-2-thumbnail-container {
  position: relative;
  width: 100%;
  border-radius: 6px;
  aspect-ratio: 16/9;
  height: auto;
}

.media-list-2-thumbnail img {
  border-radius: 6px;
  aspect-ratio: 16/9;
}

.media-list-2-thumbnail {
  position: relative;
  width: 100%;
}

.media-list-2-thumbnail-container .van-image__error-icon {
  transform: translate(0, 100%);
}

.media-list-2-thumbnail-container .video-duration {
  position: absolute;
  bottom: 10px;
  right: 5px;
  background-color: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 5px 10px;
  border-radius: 5px;
  font-size: 0.9em;
}

.preview-container .van-button__text {
  margin-left: 0 !important;
  white-space: nowrap;
}

.media-list-2-info-container .nickname {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  word-wrap: break-word;
  word-break: break-all;
  hyphens: auto;
}
</style>
