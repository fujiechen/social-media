<script setup>
import { useRouter } from "vue-router";
import MediaInfo from "@/components/mediaList1/MediaInfo";
import { secondsToDurationString } from "@/helpers/date";
import { ref } from "vue";

const router = useRouter();
const props = defineProps({
  data: {
    type: Object,
  },
});

const openItem = () => {
  router.push({
    name: "media",
    params: {
      id: props.data.id,
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
  <van-row class="mx-2 my-1">
    <div @click="openItem" class="media-list-1-thumbnail-container">
      <video
        v-if="isPlaying"
        width="100%"
        autoplay
        loop
        muted
        playsinline
        :src="data?.preview_file?.url"
        v-on:canplaythrough="canPlay"
        @click="openItem"
      >
      </video>
      <van-image
        v-else
        class="media-list-1-thumbnail"
        fit="cover"
        position="center"
        :src="data?.thumbnail_file?.url || 'error'"
      />
      <div class="video-duration">
        {{ secondsToDurationString(data.duration_in_seconds) }}
      </div>
    </div>
    <MediaInfo
      :data="data"
      :play-preview="playPreview"
      :is-preview-able="!!data?.preview_file?.url"
      :loading-preview="isPlaying && !thumbnailHide"
      :is-playing="isPlaying"
    />
  </van-row>
</template>
<style>
.media-list-1-thumbnail-container {
  position: relative;
  width: 45%;
  border-radius: 6px;
  aspect-ratio: 16/9;
  height: auto;
}

.media-list-1-thumbnail img {
  border-radius: 6px;
  aspect-ratio: 16/9;
  height: auto;
}

.media-list-1-thumbnail {
  position: relative;
  width: 100%;
}

.media-list-1-thumbnail-container .van-image__error-icon {
  transform: translate(0, 100%);
}

.media-list-1-thumbnail-container .video-duration {
  position: absolute;
  bottom: 10px;
  right: 5px;
  background-color: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 5px 10px;
  border-radius: 5px;
  font-size: 0.9em;
}
</style>
