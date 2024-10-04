<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from "vue-router";
import ProductList from "@/components/ProductList";
import GridText from "@/components/GridText";
import { fetchMediaUserByUserId } from "@/services/mediaUser";
import { fetchProductList } from "@/services/product";
import { showFailToast } from "vant";

const route = useRoute();

const active = ref('all');

const mediaUser = ref({});
const loadingMediaUser = ref(true);

const productAll = ref({
  list: [],
  currentPage: 1,
  loading: false,
  finished: false,
});

const productMedia = ref({
  list: [],
  currentPage: 1,
  loading: false,
  finished: false,
});

const productSub = ref({
  list: [],
  currentPage: 1,
  loading: false,
  finished: false,
});

const loadMediaUser = async (userId) => {
  loadingMediaUser.value = true;
  mediaUser.value = await fetchMediaUserByUserId(userId);
  loadingMediaUser.value = false;
};

onMounted(async () => {
  await loadMediaUser(route.params.id);
  await loadProductAllList();
});

const loadProductAllList = async () => {
  productAll.value.loading = true;

  try {
    const params = {
      product_user_type: 'user',
      user_id: route.params.id,
      page: productAll.value.currentPage++,
      per_page: 10,
    };

    const response = await fetchProductList(params);
    productAll.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
    productAll.value.list = [
      ...productAll.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取产品列表，请稍后重试！');
    productAll.value.finished = true;
  }


  productAll.value.loading = false;
};

const loadProductMediaList = async () => {
  productMedia.value.loading = true;

  try {
    const params = {
      product_user_type: 'user',
      user_id: route.params.id,
      type: 'media',
      page: productMedia.value.currentPage++,
      per_page: 10,
    };

    const response = await fetchProductList(params);
    productMedia.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
    productMedia.value.list = [
      ...productMedia.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取产品列表，请稍后重试！');
    productMedia.value.finished = true;
  }

  productMedia.value.loading = false;
};

const loadProductSubList = async () => {
  productSub.value.loading = true;

  try {
    const params = {
      product_user_type: 'user',
      user_id: route.params.id,
      type: 'subscription',
      page: productSub.value.currentPage++,
      per_page: 10,
    };

    const response = await fetchProductList(params);
    productSub.value.finished = response.meta.pagination.current_page >= response.meta.pagination.total_pages;
    productSub.value.list = [
      ...productSub.value.list,
      ...response.data,
    ];
  } catch (e) {
    showFailToast('无法获取产品列表，请稍后重试！');
    productSub.value.finished = true;
  }

  productSub.value.loading = false;
};

watch(active, async (newActive) => {
  if (newActive === 'all') {
    await loadProductAllList();
  } else if (newActive === 'media') {
    await loadProductMediaList();
  } else {
    await loadProductSubList();
  }
});
</script>

<template>
  <van-space direction="vertical" fill>
    <van-card
      class="user-product-list-name-container"
      :title="mediaUser.nickname"
      desc="用户商铺"
    />
    <van-grid :border="false" column-num="4">
      <van-grid-item text="全部">
        <template #icon>
          <GridText :text="String(mediaUser?.medias?.medias_count || 0)"/>
        </template>
      </van-grid-item>
      <van-grid-item text="视频">
        <template #icon>
          <GridText :text="String(mediaUser?.medias?.videos_count || 0)"/>
        </template>
      </van-grid-item>
      <van-grid-item text="合集">
        <template #icon>
          <GridText :text="String(mediaUser?.medias?.series_count || 0 )"/>
        </template>
      </van-grid-item>
      <van-grid-item text="图册">
        <template #icon>
          <GridText :text="String(mediaUser?.medias?.albums_count || 0 )"/>
        </template>
      </van-grid-item>
    </van-grid>

    <van-tabs v-model:active="active">
      <van-tab title="全部商品" name="all">
        <ProductList
          :products="productAll.list"
          :loading="productAll.loading"
          :has-more-pages="!productAll.finished"
          :load-next-page="loadProductAllList"
        />
      </van-tab>
      <van-tab title="媒体商品" name="media">
        <ProductList
          :products="productMedia.list"
          :loading="productMedia.loading"
          :has-more-pages="!productMedia.finished"
          :load-next-page="loadProductMediaList"
        />
      </van-tab>
      <van-tab title="订阅服务" name="subscription">
        <ProductList
          :products="productSub.list"
          :loading="productSub.loading"
          :has-more-pages="!productSub.finished"
          :load-next-page="loadProductSubList"
        />
      </van-tab>
    </van-tabs>

  </van-space>
</template>
<style>
.user-product-list-name-container .van-card__content {
  justify-content: center;
}

.user-product-list-name-container .van-card__desc {
  font-size: 1rem;
  line-height: 1rem;
  text-align: center;
  margin-top: .5rem;
}

.user-product-list-name-container .van-card__title {
  font-size: 1.5rem;
  line-height: 2rem;
  text-align: center;
}
</style>
