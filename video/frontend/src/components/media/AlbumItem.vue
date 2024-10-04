<script setup>
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';
import AuthorInfo from "@/components/media/AuthorInfo";
import MediaInfo from "@/components/media/MediaInfo";
import MediaActions from "@/components/media/MediaActions";

import 'vue3-carousel/dist/carousel.css';
import MediaPermission from "@/components/media/MediaPermission.vue";
import { ref } from "vue";
import { showImagePreview } from "vant";

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

const handleThumbnailClick = () => {
  if (props.isMediaLocked) {
    props.clickPurchase();
  }
};

const carousel = ref(null);
const handleSlideClick = () => {
  showImagePreview({
    images: props.data.images.map(image => image.url),
    startPosition: carousel.value.data.currentSlide,
  });
};
</script>

<template>
  <van-space direction="vertical" fill>
    <div class="w-100">
      <Carousel v-if="!props.isMediaLocked" ref="carousel">
        <Slide v-for="image in props.data.images" :key="image.id">
          <div class="carousel__item">
            <van-image
              fit="contain"
              position="center"
              :src="image.url"
              @click="handleSlideClick"
            />
          </div>
        </Slide>
        <template #addons>
          <Navigation />
          <Pagination />
        </template>
      </Carousel>
      <div
        v-else
        class="media-page-album-thumbnail-container"
        @click="handleThumbnailClick"
      >
        <van-image
          class="media-page-album-thumbnail"
          fit="cover"
          position="center"
          :src="props.data?.thumbnail_file?.url || 'error'"
        />
        <div class="lock-icon">
          <i class="bi bi-lock" />
          {{ props.mediaLockDescription + ' ' + props.data?.total_images + '张图片'}}
        </div>
      </div>
    </div>
    <template v-if="!mediaOnly">
      <AuthorInfo :data="props.data" :click-follow-author="props.clickFollowAuthor" />
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
.carousel__item {
  min-height: 30vh;
  max-height: 75vh;
  width: 100%;
  background-color: var(--vc-clr-white);
  color: var(--vc-clr-white);
  font-size: 20px;
  border-radius: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.carousel__item img {
  max-height: 75vh;
}

.media-page-album-thumbnail-container {
  position: relative;
  width: 100%;
  border-radius: 6px;
  aspect-ratio: 16/9;
  height: auto;
}

.media-page-album-thumbnail-container .media-page-album-thumbnail {
  position: relative;
  width: 100%;
}

.media-page-album-thumbnail-container .media-page-album-thumbnail img {
  border-radius: 6px;
  aspect-ratio: 16/9;
  height: auto;
  width: 100%;
}

.media-page-album-thumbnail-container .van-image__error-icon {
  transform: translate(0, 250%);
}

.media-page-album-thumbnail-container .lock-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  position: absolute;
  bottom: 6px;
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
