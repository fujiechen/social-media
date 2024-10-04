<script setup>
import { ref } from "vue";
import {useRouter} from "vue-router";
import { secondsToDurationString } from "@/helpers/date";
import MediaTop from "./MediaTop";
import MediaBottom from "./MediaBottom";

const router = useRouter();
const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
});
const isPlaying = ref(false);

const openItem = () => {
  router.push({
    name: "media",
    params: {
      id: props.data.id,
    },
  });
};

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
  <van-space direction="vertical" fill>
    <MediaTop :data="props.data" />
    <van-text-ellipsis
      class="text-body d-block fs-l line-height-s d-inline-block mx-3"
      :content="props.data.name"
      expand-text="展开"
      collapse-text="收起"
      rows="2"
    />
    <div class="w-100" @click="openItem">
      <video
        v-if="isPlaying"
        :class="thumbnailHide ? '' : 'd-none'"
        width="100%"
        autoplay
        loop
        muted
        playsinline
        :src="props.data.preview_file.url"
        v-on:canplaythrough="canPlay"
      >
      </video>
      <div class="media-grid-thumbnail-container">
        <van-image
          :class="thumbnailHide ? 'd-none' : '' + 'thumbnail-img'"
          fit="cover"
          position="center"
          width="100%"
          :src="props.data?.thumbnail_file?.url || 'error'"
        />
        <div class="video-duration">{{secondsToDurationString(props.data.duration_in_seconds)}}</div>
      </div>
    </div>
    <MediaBottom
      :data="props.data"
      :play-preview="playPreview"
      :is-preview-able="!!props.data?.preview_file?.url"
      :loading-preview="isPlaying && !thumbnailHide"
      :is-playing="isPlaying"
    />
  </van-space>
</template>
<style>
.media-grid-thumbnail-container {
  position: relative;
}

.media-grid-thumbnail-container .video-duration {
  position: absolute;
  bottom: 10px;
  right: 10px;
  background-color: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 5px 10px;
  border-radius: 5px;
  font-size: 0.9em;
}
</style>
