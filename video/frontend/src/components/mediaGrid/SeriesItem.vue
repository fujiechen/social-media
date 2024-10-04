<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";
import MediaBottom from "./MediaBottom";
import MediaTop from "./MediaTop";

const router = useRouter();
const props = defineProps({
  data: {
    type: Object,
  },
});

const thumbnails = computed(() => {
  let childrenThumbnails = [];
  if (props.data.children_medias && props.data.children_medias.length > 0) {
    childrenThumbnails = props.data.children_medias.map((media) => {
      return media?.thumbnail_file?.url || 'error';
    });
  }

  return [
    props.data?.thumbnail_file?.url || 'error',
    ...childrenThumbnails.slice(0, 2),
  ];
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
    <div class="w-100 d-flex justify-content-between" @click="openItem">
      <div
        v-for="(thumbnail, index) in thumbnails"
        :key="index"
        class="square-image-container"
      >
        <van-image
          class="square-image"
          fit="cover"
          position="center"
          width="100%"
          :src="thumbnail"
        />
      </div>
    </div>
    <MediaBottom
      :data="props.data"
    />
  </van-space>
</template>

<style lang="scss" scoped>
.square-image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;

  &-container {
    width: 32.5%;
    float: left;
    position: relative;

    &::before {
      content: "";
      display: block;
      padding-top: 100%;
    }
  }
}
</style>
