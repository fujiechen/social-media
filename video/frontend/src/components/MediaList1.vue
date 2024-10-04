<script setup>
import SeriesItem from "@/components/mediaList1/SeriesItem";
import AlbumItem from "@/components/mediaList1/AlbumItem";
import VideoItem from "@/components/mediaList1/VideoItem";

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
  <template
    v-for="media in medias"
    :key="media.id"
  >
    <div class="w-100">
      <VideoItem v-if="media.type === 'Video'" :data="media" />
      <AlbumItem v-else-if="media.type === 'Album'" :data="media" />
      <SeriesItem v-else-if="media.type === 'Series'" :data="media" />
    </div>
  </template>
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
