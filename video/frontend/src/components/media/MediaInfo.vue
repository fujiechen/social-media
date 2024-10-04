<script setup>
import { computed } from "vue";
import {useRouter} from "vue-router";
import { timestampToDatetime } from "@/helpers/date";

const router = useRouter();

const props = defineProps({
  data: {
    type: Object,
  },
  seriesCount: {
    type: Object,
  },
});

const description = computed(() => {
  let metaString = '';
  if (props.data.media_meta && props.data.media_meta.length > 0) {
    props.data.media_meta.forEach((meta) => {
      metaString += `\n${meta.meta_key}: ${meta.meta_value}`;
    });
  }
  return props.data.description ?? '' + metaString;
});

const handleActorClick = (actorId) => {
  router.push({
    name: "mediaActor",
    params: {
      id: actorId,
    },
  });
};

const handleTagClick = (tagId) => {
  router.push({
    name: "mediaTag",
    params: {
      id: tagId,
    },
  });
};

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
  <div class="mx-3">
    <h5 class="my-1">{{ props.data.name }}</h5>
    <div
      v-if="props.data.actors && props.data.actors.length > 0"
      class="fs-m"
    >
      <span>演员：</span>
      <span
        v-for="actor in props.data.actors"
        :key="actor.id"
        @click="handleActorClick(actor.id)"
        class="link-secondary me-1 media-info-actor-text"
      >
        {{ actor.name }}
      </span>
    </div>
    <div
      v-if="props.data.categories && props.data.categories.length > 0"
      class="fs-m"
    >
      <span>分类：</span>
      <span
        v-for="category in props.data.categories"
        :key="category.id"
        @click="handleCategoryClick(category.id)"
        class="link-secondary me-1 media-info-category-text"
      >
        {{ category.name }}
      </span>
    </div>
    <div
      v-if="props.data.tags && props.data.tags.length > 0"
      class="fs-m"
    >
      <span>标签：</span>
      <span
        v-for="tag in props.data.tags"
        :key="tag.id"
        @click="handleTagClick(tag.id)"
        class="link-secondary me-1 media-info-tag-text"
      >
        {{ tag.name }}
      </span>
    </div>
    <van-text-ellipsis
      v-if="description"
      rows="2"
      :content="description"
      expand-text="展开"
      collapse-text="收起"
      class="fs-m"
    />
    <div>
      <span
        v-if="props.seriesCount?.video"
        class="my-1 me-2 fs-s text-secondary"
      >
        {{ `${props.seriesCount.video}个视频` }}
      </span>
      <span
        v-if="props.seriesCount?.album"
        class="my-1 me-2 fs-s text-secondary"
      >
        {{ `${props.seriesCount.album}个图集` }}
      </span>
      <span
        v-if="props.seriesCount?.series"
        class="my-1 me-2 fs-s text-secondary"
      >
        {{ `${props.seriesCount.series}个合集` }}
      </span>
      <span class="my-1 fs-s text-secondary">
        {{ timestampToDatetime(props.data.created_at) }}
      </span>
      <a
        v-if="props.data?.download_file?.url"
        :href="props.data.download_file.url"
        target="_blank"
        download
        class="d-block mt-1 download-link"
      >
        点击下载该媒体
      </a>
    </div>
  </div>
</template>
<style>
.media-info-actor-text {
  font-size: .9rem;
  font-weight: 500;
}

.media-info-category-text {
  font-size: .9rem;
  font-weight: 500;
}

.media-info-category-text::before {
  content: '//';
  color: #ff8382;
  font-weight: 800;
  margin-right: 1px;
  font-size: .8rem;
}

.media-info-tag-text {
  font-size: .9rem;
  font-weight: 500;
}

.media-info-tag-text::before {
  content: '#';
  color: #6190c9;
  font-weight: 800;
  margin-right: 1px;
  font-size: .8rem;
}

.download-link {
  font-weight: 500;
  font-size: .9rem;
  color: #fca3a3;
}
</style>
