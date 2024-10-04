<script setup>
import { useRouter } from "vue-router";
import MediaInfo from "@/components/mediaList1/MediaInfo";

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
</script>
<template>
  <van-row @click="openItem" class="mx-2 my-1">
    <div class="media-list-1-thumbnail-container">
      <van-image
        class="media-list-1-thumbnail"
        fit="cover"
        position="center"
        :src="data?.thumbnail_file?.url || 'error'"
      />
      <div class="children-media-count">
        <van-icon name="bars"/>
        {{ data.meta.count.children_medias }}
      </div>
    </div>
    <MediaInfo :data="data" />
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
  width: 100%;
}

.media-list-1-thumbnail {
  position: relative;
  width: 100%;
}

.media-list-1-thumbnail-container .van-image__error-icon {
  transform: translate(0, 100%);
}

.media-list-1-thumbnail-container .children-media-count {
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
