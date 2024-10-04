<script setup>
import {useRouter} from "vue-router";

const router = useRouter();
defineProps({
  categories: {
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

const handleCategoryClick = (categoryId) => {
  router.push({
    name: "mediaCategory",
    params: {
      id: categoryId,
    },
  });
};

</script>
<template>
  <van-grid
    :column-num="3"
    :border="false"
  >
    <van-grid-item
      :border="false"
      v-for="category in categories"
      :key="category.id"
      @click="handleCategoryClick(category.id)"
    >
      <van-image
        :src="category.avatar_file.url"
        class="category-image-container"
      />
      <span class="category-list-category-text">
        {{ category.name }}
      </span>
    </van-grid-item>
  </van-grid>
  <div v-if="loading" class="mt-3 text-center">
    <van-loading class="mb-5" vertical>加载中...</van-loading>
  </div>
  <div v-else-if="hasMorePages" @click="loadNextPage">
    <van-divider class="mb-5" dashed>加载更多</van-divider>
  </div>
  <van-empty
    v-else-if="categories.length === 0"
    image="search"
    description="没有了"
  />
</template>
<style>
.category-image-container {
  display: flex;
  width: 100%;
  padding-bottom: 5px;
  position: relative;
}

.category-image-container img {
  aspect-ratio: 1;
  width: 100%;
  height: auto;
  border-radius: 13px;
  object-fit: cover;
}

.category-list-category-text {
  font-size: .9rem;
  font-weight: 500;
}

.category-list-category-text::before {
  content: '//';
  color: #ff8382;
  font-weight: 800;
  margin-right: 1px;
  font-size: .8rem;
}
</style>
