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
    <div class="children-media-count">
      <van-icon name="bars"/>
      {{ media.meta.count.children_medias }}
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

.media-list-2-thumbnail-container .media-list-2-thumbnail img {
  border-radius: 6px;
  aspect-ratio: 16/9;
  height: auto;
  width: 100%;
}

.media-list-2-thumbnail-container .media-list-2-thumbnail {
  position: relative;
  width: 100%;
}

.media-list-2-thumbnail-container .van-image__error-icon {
  transform: translate(0, 100%);
}

.media-list-2-thumbnail-container .children-media-count {
  display: flex;
  justify-content: center;
  align-items: center;
  position: absolute;
  bottom: 5px;
  right: 0;
  width: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 2px 0;
  border-bottom-left-radius: 6px;
  border-bottom-right-radius: 6px;
  font-size: 0.9em;
}
</style>
