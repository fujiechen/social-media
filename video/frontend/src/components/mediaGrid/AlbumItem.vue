<script setup>
import { useRouter } from "vue-router";
import MediaBottom from "./MediaBottom";
import MediaTop from "./MediaTop";

const router = useRouter();
const props = defineProps({
  data: {
    type: Object,
    required: true,
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
      <div class="media-grid-thumbnail-container">
        <van-image
          class="thumbnail-img"
          fit="cover"
          position="center"
          width="100%"
          :src="props.data?.thumbnail_file?.url || 'error'"
        />
        <div class="album-sign">
          <van-icon name="photo" />
        </div>
      </div>
    </div>
    <MediaBottom
      :data="props.data"
    />
  </van-space>
</template>
<style>
.media-grid-thumbnail-container {
  position: relative;
}

.media-grid-thumbnail-container .album-sign {
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
