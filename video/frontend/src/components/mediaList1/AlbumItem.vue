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
      <div class="album-sign">
        <van-icon name="photo" />
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
}

.media-list-1-thumbnail {
  position: relative;
  width: 100%;
}

.media-list-1-thumbnail-container .van-image__error-icon {
  transform: translate(0, 100%);
}

.media-list-1-thumbnail-container .album-sign {
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
