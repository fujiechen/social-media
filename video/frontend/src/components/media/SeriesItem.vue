<script setup>
import { computed } from "vue";

import AuthorInfo from "@/components/media/AuthorInfo";
import MediaInfo from "@/components/media/MediaInfo";
import MediaActions from "@/components/media/MediaActions";
import MediaList1 from "@/components/MediaList1";
import MediaPermission from "@/components/media/MediaPermission.vue";

const props = defineProps({
  data: {
    type: Object,
    required: true,
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

const mediaCount = computed(() => {
  let videoCount = 0;
  let albumCount = 0;
  let seriesCount = 0;

  props.data.children_medias.forEach((media) => {
    if (media.type === 'Video') {
      videoCount++;
    } else if (media.type === 'Album') {
      albumCount++;
    } else if (media.type === 'Series') {
      seriesCount++;
    }
  });

  return {
    video: videoCount,
    album: albumCount,
    series: seriesCount,
  }
});

const handleThumbnailClick = () => {
  if (props.isMediaLocked) {
    props.clickPurchase();
  }
};
</script>

<template>
  <van-space direction="vertical" fill>
    <div
      class="media-page-series-thumbnail-container"
      @click="handleThumbnailClick"
    >
      <van-image
        class="media-page-series-thumbnail"
        fit="cover"
        position="center"
        :src="props.data?.thumbnail_file?.url || 'error'"
      />
      <div class="children-media-count">
        <i v-if="props.isMediaLocked" class="bi bi-lock" />
        <van-icon v-else name="bars" />
        {{ props.isMediaLocked
          ? props.mediaLockDescription
          :props.data.meta.count.children_medias }}
      </div>
    </div>
    <template v-if="!mediaOnly">
      <AuthorInfo :data="props.data" :click-follow-author="props.clickFollowAuthor" />
      <MediaInfo :data="props.data" :series-count="mediaCount" />
      <MediaPermission
        v-if="props.isMediaLocked"
        :data="props.data"
        :click-author-follow="props.clickFollowAuthor"
        :click-purchase="props.clickPurchase"
      />
      <MediaActions :data="props.data" :click-comment="props.clickComment" :click-like="props.clickLike" :click-favorite="props.clickFavorite" />
      <div class="mt-2"></div>
      <MediaList1 :medias="props.data.children_medias" />
    </template>
  </van-space>
</template>
<style>
.media-page-series-thumbnail-container {
  position: relative;
  width: 100%;
  border-radius: 6px;
  aspect-ratio: 16/9;
  height: auto;
}

.media-page-series-thumbnail-container .media-page-series-thumbnail {
  position: relative;
  width: 100%;
}

.media-page-series-thumbnail-container .media-page-series-thumbnail img {
  border-radius: 6px;
  aspect-ratio: 16/9;
  height: auto;
  width: 100%;
}

.media-page-series-thumbnail-container .van-image__error-icon {
  transform: translate(0, 250%);
}

.media-page-series-thumbnail-container .children-media-count {
  display: flex;
  justify-content: center;
  align-items: center;
  position: absolute;
  bottom: 5px;
  right: 0;
  width: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 5px 0;
  border-bottom-left-radius: 6px;
  border-bottom-right-radius: 6px;
  font-size: 0.9em;
}
</style>
