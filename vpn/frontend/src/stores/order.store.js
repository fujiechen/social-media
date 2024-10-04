import {defineStore} from 'pinia';

import {fetchWrapper} from '@/helpers/fetch-wrapper';
import {showFailToast, showSuccessToast} from "vant";
import {useGlobalStore} from "@/stores/global.store";


const apiBaseUrl = `${process.env.VUE_APP_API_URL}`;
const baseUrl = apiBaseUrl + '/orders';

export const useOrdersStore = defineStore({
  id: 'orders',
  state: () => ({
    orders: [],
    order: {},
    waitingForVpnSetup: false,
  }),

  actions: {
    async fetchAll(status) {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      let url = `${baseUrl}`;
      if (status !== 'all') {
        url = `${baseUrl}` + '?status=' + status;
      }
      await fetchWrapper.get(url, null).then(response => {
        this.orders = response.data;
        globalStore.loading = false;
      })
    },

    async fetchOne(orderId) {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      await fetchWrapper.get(`${baseUrl}/` + orderId, null).then(response => {
        this.order = response.data;
        globalStore.loading = false;
      })
    },

    async create(product_id, qty) {
      const globalStore = useGlobalStore();
      globalStore.loading = true;

      await fetchWrapper.post(`${baseUrl}`, {
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
        .then(response => {
          const payment = response.data;
          globalStore.loading = false;

          if (payment.status === 'failed') {
            showFailToast({
              message: '支付失败',
              wordBreak: 'break-word',
            });
          } else {
            showSuccessToast('支付成功');
            this.waitingForVpnSetup = true;
            this.$router.push(`/vpn?categoryId=${payment.category_id}`);
          }
        }).catch(error => {
          globalStore.loading = false;
          if (error.reason === 'user_account.balance') {
            showFailToast({
              message: '您的余额不足，请充值重试',
              wordBreak: 'break-word',
            });
          } else {
            showFailToast({
              message: '钱包服务异常，请稍后重试',
              wordBreak: 'break-word',
            });
          }
        });
    }
  }
});
