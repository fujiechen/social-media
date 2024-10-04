<script setup>
import { formatNumberToChineseDecimal } from "@/utils";
import { useRouter } from "vue-router";
const router = useRouter();

const props = defineProps({
  media: {
    type: Object,
    required: true,
  }
});

const openItem = () => {
  router.push({
    name: "media",
    params: {
      id: props.media.id,
    },
  });
};
</script>
<template>
  <div @click="openItem" class="media-list-2-thumbnail-container">
    <van-image
      class="media-list-2-thumbnail"
      fit="cover"
      position="center"
      :src="media?.thumbnail_file?.url || 'error'"
    />
    <div class="album-sign">
      <van-icon name="photo" />
    </div>
  </div>
  <van-text-ellipsis
    class="fs-m"
    :content="media.name"
  />
  <div class="d-flex justify-content-between text-secondary">
    <span class="fs-m">{{ media.user.nickname }}</span>
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

.media-list-2-thumbnail-container .album-sign {
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
