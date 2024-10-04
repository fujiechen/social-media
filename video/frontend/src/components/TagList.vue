<script setup>
import {useRouter} from "vue-router";

const router = useRouter();
defineProps({
  tags: {
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

const handleTagClick = (tagId) => {
  router.push({
    name: "mediaTag",
    params: {
      id: tagId,
    },
  });
};

</script>
<template>
  <div
    class="actor-cards-container"
  >
    <van-grid
      :column-num="3"
      :border="false"
      :center="false"
      class="tag-grid-container"
    >
      <van-grid-item
        :border="false"
        v-for="tag in tags"
        :key="tag.id"
        :text="tag.name"
        @click="handleTagClick(tag.id)"
      />
    </van-grid>
  </div>
  <div v-if="loading" class="mt-3 text-center">
    <van-loading class="mb-5" vertical>加载中...</van-loading>
  </div>
  <div v-else-if="hasMorePages" @click="loadNextPage">
    <van-divider class="mb-5" dashed>加载更多</van-divider>
  </div>
  <van-empty
    v-else-if="tags.length === 0"
    image="search"
    description="没有了"
  />
</template>
<style>
.tag-grid-container .van-grid-item {
  padding-left: 1.5rem;
}

.tag-grid-container .van-grid-item__content {
  padding-top: 1rem;
  padding-bottom: 1rem;
}

.tag-grid-container .van-grid-item__text {
  font-size: 1.1rem;
  font-weight: 500;
}

.tag-grid-container .van-grid-item__text::before {
  content: '#';
  color: #6190c9;
  font-weight: 400;
  margin-right: 1px;
}
</style>
