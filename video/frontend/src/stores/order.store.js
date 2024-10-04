import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';
import {showFailToast, showSuccessToast} from "vant";
import {useGlobalStore} from "@/stores/global.store";
import { getOrders } from "@/services/order";

const apiBaseUrl = process.env.VUE_APP_API_URL;
const baseUrl = `${apiBaseUrl}/orders`;

export const useOrdersStore = defineStore({
  id: 'orders',
  state: () => ({
    orders: [],
    ordersMeta: {
      loading: false,
      currentPage: 1,
      hasMorePages: false,
    },
    order: {},
  }),

  actions: {
    async fetchAll(status) {
      this.ordersMeta.loading = true;

      let params = {
        page: this.ordersMeta.currentPage++,
        per_page: 10,
      };
      if (status !== 'all') {
        params.status = status;
      }
      try {
        const response = await getOrders(params);
        this.ordersMeta.hasMorePages = response.meta.pagination.current_page < response.meta.pagination.total_pages;
        if (response.meta.pagination.current_page === 1) {
          this.orders = [...response.data];
        } else {
          this.orders = [
            ...this.orders,
            ...response.data,
          ];
        }
      } catch (e) {
        showFailToast('无法获取订单列表，请稍后重试！');
      }
      this.ordersMeta.loading = false;
    },

    async fetchOne(orderId) {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      await fetchWrapper.get(`${baseUrl}/${orderId}`, null).then(response => {
        this.order = response.data;
        globalStore.loading = false;
      })
    },

    async create(product_id, qty) {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      await fetchWrapper.post(baseUrl, {
        product_id, qty
      }).then(response => {
        this.order = response.data;
        globalStore.loading = false;
        this.$router.push('/order/' + this.order.id);
      }).catch(error => {
        globalStore.loading = false;
        if (error.reason === 'product.order_num_allowance') {
          showFailToast({
            message: '对不起，此产品只能购买一次',
            wordBreak: 'break-word',
          });
        } else {
          showFailToast({
            message: '创建订单失败',
            wordBreak: 'break-word',
          });
        }
      });
    },

    async pay() {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      await fetchWrapper.post(`${baseUrl}/` + this.order.id + '/payment/create', null)
        .then(async (response) => {
          const payment = response.data;
          globalStore.loading = false;

          if (payment.status === 'failed') {
            showFailToast({
              message: '支付失败',
              wordBreak: 'break-word',
            });
          } else {
            showSuccessToast('支付成功');
            await this.fetchOne(payment.order_id);
            this.$router.push(`/order/` + payment.order_id);
          }
        }).catch(error => {
          globalStore.loading = false;
          if (error.reason === 'user_account.balance') {
            showFailToast({
              message: '您的余额不足',
              wordBreak: 'break-word',
            });
          } else {
            showFailToast({
              message: '钱包服务异常，请稍后重试',
              wordBreak: 'break-word',
            });
          }
        });
    },

    async instantPayment(product_id, qty) {
      try {
        const response = await fetchWrapper.post(`${baseUrl}` + '/instant', {
          product_id, qty
        });
        const payment = response.data;
        if (payment.status === 'failed') {
          showFailToast({
            message: '支付失败',
            wordBreak: 'break-word',
          });
          return false;
        } else {
          showSuccessToast('支付成功');
          return true;
        }
      } catch (error) {
        if (error.reason === 'user_account.balance') {
          showFailToast({
            message: '您的余额不足',
            wordBreak: 'break-word',
          });
        } else {
          showFailToast({
            message: '钱包服务异常，请稍后重试',
            wordBreak: 'break-word',
          });
        }
        return false;
      }
    }
  }
});
