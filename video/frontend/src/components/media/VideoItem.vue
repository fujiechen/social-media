<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import Hls from 'hls.js';

import AuthorInfo from "@/components/media/AuthorInfo";
import MediaInfo from "@/components/media/MediaInfo";
import MediaActions from "@/components/media/MediaActions";
import { secondsToDurationString } from "@/helpers/date";
import MediaPermission from "@/components/media/MediaPermission.vue";

const props = defineProps({
  data: {
    type: Object,
  },
  isMediaLocked: {
    type: Boolean,
    default: false,
  },
  mediaLockDescription: {
    type: String,
    default: '',
  },
  clickComment: {
    type: Function,
    required: false,
    default: () => {},
  },
  clickLike: {
    type: Function,
    required: false,
    default: () => {},
  },
  clickFavorite: {
    type: Function,
    required: false,
    default: () => {},
  },
  clickFollowAuthor: {
    type: Function,
    required: false,
    default: () => {},
  },
  clickPurchase: {
    type: Function,
    required: false,
    default: () => {},
  },
  mediaOnly: {
    type: Boolean,
    default: false,
  },
});

const plyrOption = {
  fullscreen: {
    enabled: true,
    fallback: true,
    iosNative: true,
    container: null,
  },
  controls: [
    'play-large',
    'play',
    'progress',
    'current-time',
    'mute',
    'volume',
    'settings',
    'fullscreen',
    'airplay',
  ],
  settings: [
    'quality',
    'speed',
    'loop'
  ],
};

const videoType = computed(() => {
  if (props.data.video_file) {
    const url = props.data.video_file.url;
    const baseUrl = url.split('?')[0];
    const fileExtension = baseUrl.substring(baseUrl.lastIndexOf('.') + 1);

    if (fileExtension.toLowerCase() === 'mp4') {
      return 'video/mp4';
    }
    if (fileExtension.toLowerCase() === 'm4v') {
      return 'video/mp4';
    }
    if (fileExtension.toLowerCase() === 'm3u8') {
      return 'application/x-mpegURL';
    }
  }

  return '';
});

const videoEl = ref(null);

const loadVideo = () => {
  if (props.data.video_file && videoType.value === 'application/x-mpegURL' && Hls.isSupported() && videoEl.value) {
    const hls = new Hls();
    hls.loadSource(props.data.video_file.url);
    hls.attachMedia(videoEl.value);
  }
};

const destroyVideo = () => {
  // Clean up and destroy Hls instance if component is unmounted
  if (videoEl.value && videoEl.value.hls) {
    videoEl.value.hls.detachMedia();
    videoEl.value.hls.destroy();
  }
}

onMounted(() => {
  loadVideo();
});

onUnmounted(() => {
  destroyVideo();
});

watch(() => props.data?.video_file?.url, () => {
  destroyVideo();
  const posters = document.getElementsByClassName('plyr__poster');
  if (posters.length > 0) {
    if (props.data?.thumbnail_file?.url) {
      posters[0].style = `background-image: url(${props.data.thumbnail_file.url});`;
    } else {
      posters[0].remove();
    }
  }
  loadVideo();
});

const handleThumbnailClick = () => {
  if (props.isMediaLocked) {
    props.clickPurchase();
  }
};
</script>

<template>
  <van-space direction="vertical" fill>
    <van-sticky :offset-top="46">
      <div class="w-100">
        <vue-plyr
          v-if="props.data.video_file"
          :options="plyrOption"
          class="plyr-setting"
        >
          <video
            ref='videoEl'
            controls
            crossorigin
            playsinline
            :data-poster="props.data?.thumbnail_file?.url"
          >
            <source
              v-if="props.data.video_file"
              :src="props.data.video_file.url"
              :type="videoType"
            />
          </video>
        </vue-plyr>
        <div
          v-else-if="props.data.preview_file"
          class="media-page-video-item-thumbnail-container"
          @click="handleThumbnailClick"
        >
          <video
            width="100%"
            autoplay
            loop
            muted
            playsinline
            :src="props.data.preview_file.url"
          >
          </video>
          <div class="video-duration">
            <i v-if="props.isMediaLocked" class="bi bi-lock" />
            {{ props.isMediaLocked ? props.mediaLockDescription : '' + ' ' }}
            预览
          </div>
        </div>
        <div
          v-else
          class="media-page-video-item-thumbnail-container"
          @click="handleThumbnailClick"
        >
          <van-image
            class="thumbnail-img"
            fit="cover"
            position="center"
            width="100%"
            :src="props.data?.thumbnail_file?.url || 'error'"
          />
          <div class="video-duration">
            <i v-if="props.isMediaLocked" class="bi bi-lock" />
            {{ props.isMediaLocked ? props.mediaLockDescription : '' + ' '}}
            {{secondsToDurationString(props.data.duration_in_seconds)}}
          </div>
        </div>
      </div>
    </van-sticky>
    <template v-if="!mediaOnly">
    <AuthorInfo :data="props.data" :click-follow-author="props.clickFollowAuthor"/>
    <MediaInfo :data="props.data" />
    <MediaPermission
      v-if="props.isMediaLocked"
      :data="props.data"
      :click-author-follow="props.clickFollowAuthor"
      :click-purchase="props.clickPurchase"
    />
    <MediaActions :data="props.data" :click-comment="props.clickComment" :click-like="props.clickLike" :click-favorite="props.clickFavorite" />
    </template>
  </van-space>
</template>
<style>
.media-page-video-item-thumbnail-container {
  position: relative;
}

.media-page-video-item-thumbnail-container .video-duration {
  position: absolute;
  bottom: 10px;
  right: 10px;
  background-color: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 5px 10px;
  border-radius: 5px;
  font-size: 0.9em;
}

.plyr:not(:fullscreen) .plyr-setting,
.plyr:not(:-webkit-full-screen) .plyr-setting {
  max-height: 50vh;
}
</style>
