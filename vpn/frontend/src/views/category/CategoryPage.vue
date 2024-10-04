<script setup>

import {storeToRefs} from "pinia";
import {useCategoriesStore} from "@/stores/category.store";
import {showToast} from "vant";
import {useOrdersStore} from "@/stores/order.store";
import {useRoute} from "vue-router/dist/vue-router";
import {computed, onMounted, ref, watch} from 'vue'
import { useUserShareStore } from "@/stores/userShare.store";
import {useGlobalStore} from "@/stores/global.store";
import {useRouter} from "vue-router";
import useDebounce from "@/helpers/debouncer";

const route = useRoute();
const router = useRouter();
const active = computed({
  get() {
    return Number(route.params.categoryId);
  },
  // eslint-disable-next-line no-unused-vars
  set(value) {
  }
}
);

const globalStore = useGlobalStore();
const userShareStore = useUserShareStore();
const { newUserShare } = storeToRefs(userShareStore);

const categoriesStore = useCategoriesStore();
const {categories} = storeToRefs(categoriesStore);

const selectedCategory = ref({});
const selectedProductId = ref(0);
const totalPrice = ref(0);


onMounted(async () => {
  globalStore.loading = true;
  await categoriesStore.fetchAll();
  await loadCategory();
  globalStore.loading = false;
});

watch(selectedProductId, async () => {
  totalPrice.value = selectedCategory.value.products.find(product => product.id === selectedProductId.value).unit_price * 100;
})

watch(active, async (newActive) => {
  if (newActive) {
    globalStore.loading = true;
    await loadCategory();
    globalStore.loading = false;
  }
});

const loadCategory = async () => {
  selectedCategory.value = categories.value.find(c => c.id === active.value);
  selectedProductId.value = selectedCategory.value.products[0].id;
  totalPrice.value = selectedCategory.value.products[0].unit_price * 100;
};

const ordersStore = useOrdersStore();
const qty = ref(1);
const showShare = ref(false);
const options = [
  {id: 'link', name: '复制链接', icon: 'link'},
  {id: 'poster', name: '分享海报', icon: 'poster'},
];

const onSelect = async (option) => {
  await userShareStore.createUserShares(
    'category',
    selectedCategory.value.id,
    `${process.env.VUE_APP_URL}/`
  );

  if (option.id === 'link') {
    if (newUserShare) {
      if (window.isSecureContext) {
        await navigator.clipboard.writeText(newUserShare.value.share_url);
      }
      showToast('链接已复制');
    } else {
      showToast('分享链接生成失败，请稍后再试')
    }
  } else if (option.id === 'poster'){
    userShareStore.showUserSharePopup = true;
  }

  showShare.value = false;
};

const createOrder = async () => {
  useDebounce(await ordersStore.create(selectedProductId.value, qty.value));
}

</script>

<template>
  <van-space direction="vertical" fill>
    <van-tabs v-model:active="active">
      <van-tab v-for="category in categories"
               :key="category.id"
               :title="category.name"
               :name="category.id"
               :to="{name:'category',params:{categoryId:category.id}}">
        <van-row justify="center">
          <van-image
            :src="category.thumbnail_file.url"
            fit="contain"
          />
        </van-row>
        <div style="margin: 20px">
          <h3>
            {{ category.name }}
          </h3>
          <div>
            {{ category.description }}
          </div>
        </div>

        <van-row>&nbsp;</van-row>

        <van-cell-group inset>
          <div class="van-ellipsis"><b>购买服务</b></div>
          <van-row>&nbsp;</van-row>
        </van-cell-group>
        <van-cell-group insert>
          <van-radio-group v-model="selectedProductId">
            <van-cell-group inset>
              <van-cell v-for="p in category.products" :key="p.id" clickable>
                <template #title>
                  {{ p.name }} ({{p.frequency_as_extend_days}}天)
                </template>
                <template #right-icon>
                  <b>{{p.unit_price_formatted}} &nbsp;</b>
                  <van-radio :name="p.id"/>
                </template>
              </van-cell>
            </van-cell-group>
          </van-radio-group>
        </van-cell-group>

        <van-row>&nbsp;</van-row>

        <van-cell-group inset>
          <div class="van-ellipsis"><b>配置详情</b></div>
          <van-row>&nbsp;</van-row>
        </van-cell-group>
        <van-cell-group inset>
          <van-cell :title="highlight.name" v-for="highlight in category.highlights" :key="highlight.name">
            <template #value>
              <van-icon v-if="highlight.switch === '1'" name="success" color="#1989fa"/>
              <van-icon v-else name="cross" color="#1989fa"/>
            </template>
          </van-cell>
        </van-cell-group>

        <van-row>&nbsp;</van-row>

        <van-cell-group inset>
          <div class="van-ellipsis"><b>安装教程</b></div>
          <van-row>&nbsp;</van-row>
        </van-cell-group>
        <van-cell-group inset>
          <van-cell title="苹果" is-link :to="{name:'tutorial',params:{os:'mac'}}"/>
          <van-cell title="安卓" is-link :to="{name:'tutorial',params:{os:'android'}}"/>
          <van-cell title="Windows" is-link :to="{name:'tutorial',params:{os:'win'}}"/>
          <van-cell title="Mac" is-link :to="{name:'tutorial',params:{os:'mac'}}"/>
        </van-cell-group>

        <van-row>&nbsp;</van-row>
        <van-row>&nbsp;</van-row>

        <van-share-sheet
            v-model:show="showShare"
            title="立即分享给好友"
            :options="options"
            @select="onSelect"
        />

        <van-submit-bar :price="totalPrice" button-text="提交订单" @submit="createOrder">
          <van-action-bar-icon icon="home-o" text="主页" @click="router.push({name:'home'})"/>
          <van-action-bar-icon icon="share-o" text="分享" @click="showShare = true"/>
          <van-action-bar-icon icon="chat-o" text="客服" @click="globalStore.showCustomerServicePopup = true"/>
        </van-submit-bar>
      </van-tab>
    </van-tabs>
  </van-space>
</template>
