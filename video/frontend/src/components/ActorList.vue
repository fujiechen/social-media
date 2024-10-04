<script setup>
import {useRouter} from "vue-router";

const router = useRouter();
defineProps({
  actors: {
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

const handleActorClick = (actorId) => {
  router.push({
    name: "mediaActor",
    params: {
      id: actorId,
    },
  });
};

</script>
<template>
  <div
    class="actor-cards-container"
  >
    <van-card
      v-for="actor in actors"
      :key="actor.id"
      :desc='actor.country'
      :title="actor.name"
      :thumb="actor.avatar_file.url"
      @click="handleActorClick(actor.id)"
    >
      <template #tags>
        <van-tag plain type="primary" class="me-1">合计: {{actor.medias.medias_count}}</van-tag>
        <van-tag plain type="primary" class="me-1">视频: {{actor.medias.videos_count}}</van-tag>
        <van-tag plain type="primary" class="me-1">合集: {{actor.medias.series_count}}</van-tag>
        <van-tag plain type="primary" class="me-1">图册: {{actor.medias.albums_count}}</van-tag>
      </template>
    </van-card>
  </div>
  <div v-if="loading" class="mt-3 text-center">
    <van-loading class="mb-5" vertical>加载中...</van-loading>
  </div>
  <div v-else-if="hasMorePages" @click="loadNextPage">
    <van-divider class="mb-5" dashed>加载更多</van-divider>
  </div>
  <van-empty
    v-else-if="actors.length === 0"
    image="search"
    description="没有了"
  />
</template>
<style>
.actor-cards-container .van-card__content {
  display: flex;
  justify-content: center;
  margin-left: 10px;
}

.actor-cards-container .van-card__title {
  font-size: 1rem;
  font-weight: bold;
}

.actor-cards-container .van-card__desc {
  font-size: .9rem;
  margin-top: 4px;
  margin-bottom: 4px;
}
</style>
