<script setup>
import VideoItem from "@/components/mediaList2/VideoItem";
import AlbumItem from "@/components/mediaList2/AlbumItem";
import SeriesItem from "@/components/mediaList2/SeriesItem";

defineProps({
  medias: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
    required: false,
  },
  hasMorePages: {
    type: Boolean,
    default: false,
    required: false,
  },
  loadNextPage: {
    type: Function,
    default: () => {},
    required: false,
  },
});

</script>

<template>
  <van-grid
    class="similar-list-container"
    :border="false"
    :column-num="2"
    :center="false"
  >
    <van-grid-item
      v-for="media in medias"
      :key="media.id"
    >
      <div class="w-100">
        <VideoItem v-if="media.type === 'Video'" :media="media" />
        <AlbumItem v-else-if="media.type === 'Album'" :media="media" />
        <SeriesItem v-else-if="media.type === 'Series'" :media="media" />
      </div>
    </van-grid-item>
  </van-grid>
  <div v-if="loading" class="mt-3 text-center">
    <van-loading class="mb-5" vertical>加载中...</van-loading>
  </div>
  <div v-else-if="hasMorePages" @click="loadNextPage">
    <van-divider class="mb-5" dashed>加载更多</van-divider>
  </div>
  <van-empty
    v-else-if="medias.length === 0"
    image="search"
    description="没有了"
  />
</template>
<style>
.similar-list-container img {
  border-radius: 6px;
  aspect-ratio: 16/9;
  height: auto;
  width: 100%;
}
</style>
