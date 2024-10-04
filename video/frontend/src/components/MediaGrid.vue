<script setup>
import { toRefs } from "vue";
import VideoItem from "@/components/mediaGrid/VideoItem";
import AlbumItem from "@/components/mediaGrid/AlbumItem";
import SeriesItem from "@/components/mediaGrid/SeriesItem";

const props = defineProps({
  list: Array,
  finished: Boolean,
  loading: Boolean,
});

const { list, finished } = toRefs(props);

const emit = defineEmits(["load"]);

const onLoad = () => {
  emit("load");
};

</script>

<template>
  <van-list
    class="pb-2 pt-2"
    :loading="loading"
    :finished="finished"
    finished-text="没有更多了"
    @load="onLoad"
    offset="15"
    direction="down"
  >
    <template
      v-for="item in list"
      :key="item"
    >
      <div class="w-100">
        <VideoItem v-if="item.type === 'Video'" :data="item" />
        <AlbumItem v-else-if="item.type === 'Album'" :data="item" />
        <SeriesItem v-else-if="item.type === 'Series'" :data="item" />
      </div>
      <van-divider />
    </template>
  </van-list>
</template>

