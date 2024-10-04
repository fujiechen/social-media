<script setup>

import {storeToRefs} from "pinia";
import {showToast} from "vant";
import {useOrdersStore} from "@/stores/order.store";
import {useRoute} from "vue-router/dist/vue-router";
import { onMounted, ref, watch } from "vue";
import {useUserShareStore} from "@/stores/userShare.store";
import {useGlobalStore} from "@/stores/global.store";
import {useRouter} from "vue-router";
import useDebounce from "@/helpers/debouncer";
import VideoItem from "@/components/media/VideoItem";
import AlbumItem from "@/components/media/AlbumItem";
import SeriesItem from "@/components/media/SeriesItem";
import VideoListItem from "@/components/mediaList1/VideoItem";
import AlbumListItem from "@/components/mediaList1/AlbumItem";
import SeriesListItem from "@/components/mediaList1/SeriesItem";
import { fetchProductById } from "@/services/product";
import SharePopup from "@/components/SharePopup.vue";

const route = useRoute();
const router = useRouter();

const globalStore = useGlobalStore();
const userShareStore = useUserShareStore();
const {sharePopup} = storeToRefs(userShareStore);

const active = ref('summary');

const product = ref({});
const loadingProduct = ref(true);

const fetchData = async (id) => {
  loadingProduct.value = true;
  try {
    product.value = await fetchProductById(id);
  } catch (e) {
    showToast('该产品暂时无法访问');
    await router.push({
      name: "products",
    });
  }
  loadingProduct.value = false;
};

onMounted(async () => {
  if (route.params.id) {
    await fetchData(route.params.id);
  }
});

watch(() => route.params.id, async (newId) => {
  if (newId) {
    await fetchData(newId);
    window.scrollTo(0,0);
  }
});

const ordersStore = useOrdersStore();
const qty = ref(1);
const showShare = ref(false);
const options = [
  {id: 'link', name: '复制链接', icon: 'link'},
  {id: 'poster', name: '分享海报', icon: 'poster'},
];

const onSelect = async (option) => {
  globalStore.loading = true;
  await userShareStore.createUserShare(
    'product',
    route.params.id,
    window.location.href
  );
  globalStore.loading = false;

  if (option.id === 'link') {
    if (sharePopup.value.userShare) {
      if (window.isSecureContext) {
        await navigator.clipboard.writeText(sharePopup.value.userShare.share_url);
      }
      showToast('链接已复制');
    } else {
      showToast('分享链接生成失败，请稍后再试')
    }
  } else if (option.id === 'poster') {
    userShareStore.sharePopup.show = true;
  }

  showShare.value = false;
};

const createOrder = async () => {
  useDebounce(await ordersStore.create(route.params.id, qty.value));
}
</script>

<template>
  <van-space direction="vertical" fill>
    <van-row justify="center">
      <div v-if="product.type === 'media'" class="w-100">
        <VideoItem v-if="product.media.type === 'Video'" :data="product.media" :media-only="true" />
        <AlbumItem v-else-if="product.media.type === 'Album'" :data="product.media" :media-only="true" />
        <SeriesItem v-else-if="product.media.type === 'Series'" :data="product.media"  :media-only="true" />
      </div>
      <span v-else>
        <van-image
          :src="product?.thumbnail_file?.url || 'error'"
          fit="contain"
        />
      </span>
    </van-row>
    <div style="margin: 20px">
      <h3>
        {{ product.name }}
      </h3>
      <div>
        {{ product.description }}
      </div>
    </div>

    <van-tabs v-model:active="active">
      <van-tab title="简介" name="summary">
        <van-cell-group inset>
          <span v-if="product.type === 'membership'">
            <van-cell title="购买会员" value="VIP"/>
            <van-cell title="周期" :value="product.frequency_in_days + '天'"/>
          </span>
          <span v-if="product.type === 'media'">
            <van-cell title="购买媒体" :value="product.name"/>
            <van-cell title="周期" value="永久"/>
          </span>
          <span v-if="product.type === 'subscription'">
            <van-cell
              title="订阅服务"
              is-link
              :value="`${product.frequency_in_days}天`"
              :to= "{ name: 'mediaUser', params: { id: product.publisher_user_id } }"
            />
          </span>
          <span v-if="product.user">
            <van-cell
              title="店铺"
              is-link
              :value="product.user.nickname"
              :to= "{ name: 'userProducts', params: { id: product.user.id } }"
            />
          </span>
          <span v-else>
            <van-cell title="店铺">
              <template #value>
                  Video
              </template>
            </van-cell>
          </span>
          <van-cell title="价格" :value="product.unit_price_formatted" />
        </van-cell-group>
      </van-tab>
      <van-tab title="详情" name="images">
        <van-grid :border="false" :column-num="1">
          <van-grid-item>
            <div v-if="product.type === 'media'" class="w-100">
              <VideoListItem v-if="product.media.type === 'Video'" :data="product.media" />
              <AlbumListItem v-else-if="product.media.type === 'Album'" :data="product.media" />
              <SeriesListItem v-else-if="product.media.type === 'Series'" :data="product.media" />
            </div>
            <span v-else>
              <van-image
                v-for="image in product.image_files"
                :key="image.id"
                :src="image.url"
                class="w-100"
              />
            </span>
          </van-grid-item>
        </van-grid>
      </van-tab>
    </van-tabs>

    <van-share-sheet
      v-model:show="showShare"
      title="立即分享给好友"
      :options="options"
      @select="onSelect"
    />

    <van-submit-bar
      :disabled="product.media_product_bought"
      :price="product.unit_price * 100"
      :button-text="product.media_product_bought ? '已购买' : '提交订单'"
      :currency="product.currency_name === 'CNY' ? '¥' : 'C'"
      @submit="createOrder"
    >
      <van-action-bar-icon icon="home-o" text="主页" @click="router.push({name:'home'})"/>
      <van-action-bar-icon icon="share-o" text="分享" @click="showShare = true"/>
      <van-action-bar-icon icon="chat-o" text="客服" @click="globalStore.showCustomerServicePopup = true"/>
    </van-submit-bar>
  </van-space>
  <SharePopup />
</template>
