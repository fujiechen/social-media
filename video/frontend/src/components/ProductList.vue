<script setup>
import {useRouter} from "vue-router/dist/vue-router";
import {
  convertProductCurrencyNameToHumanReadable,
  convertProductTypeToHumanReadable,
  convertProductUserTypeToHumanReadable,
} from "@/utils";

defineProps({
  products: {
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

const router = useRouter();

</script>

<template>
  <div class="product-list-container">
    <van-card
      v-for="product in products"
      :key="product.id"
      :thumb="product?.thumbnail_file?.url || 'error'"
      :price="product.unit_price_formatted.substring(1)"
      :currency="product.unit_price_formatted.substring(0, 1)"
      @click="router.push({name:'product',params:{id:product.id}})"
      :centered="true"
      :title="product.name"
      :desc="product.description"
    >
      <template #tags>
        <div class="product-tag-list">
          <van-tag>
            {{ convertProductUserTypeToHumanReadable(product.product_user_type) }}
          </van-tag>
          <van-tag>
            {{ convertProductTypeToHumanReadable(product.type) }}
          </van-tag>
          <van-tag>
            {{ convertProductCurrencyNameToHumanReadable(product.currency_name) }}
          </van-tag>
        </div>
      </template>
      <template #footer>
        <van-button
          v-if="!product.media_product_bought"
          round
          type="primary"
          icon="shopping-cart-o"
          size="small"
          :to= "{ name: 'product', params: { id: product.id } }"
        >
          立即购买
        </van-button>
        <van-button
          v-else
          round
          disabled
          plain
          type="primary"
          icon="shopping-cart-o"
          size="small"
        >
          已购买
        </van-button>
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
    v-else-if="products.length === 0"
    image="search"
    description="未找到商品"
  />
</template>

<style scoped>
.product-tag-list .van-tag {
  margin-right: .5em;
}

.product-list-container {
  margin-top: 1rem;
}

.product-list-container .van-card__title {
  font-size: 1rem;
}

.product-list-container .van-card__desc {
  font-size: .8rem;
}

.product-list-container .van-card__content {
  justify-content: start;
}

.product-list-container .van-card__footer {
  margin-bottom: .2rem;
}
</style>
