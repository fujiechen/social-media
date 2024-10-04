import UserProduct from '@/constants/UserProduct';
import client from './api';

const BASE_URL = 'user/products';

class UserProfile {
  getActiveUserProducts({ page = 1 }) {
    return client
      .get(`${BASE_URL}/index`, {
        params: {
          page,
        },
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  getActiveUserProductReturns({ id, page = 1 }) {
    return client
      .get(`${BASE_URL}/${id}/returns`, {
        params: {
          order_by_direction: UserProduct.USER_PRODUCT_RETURNS_ORDER_BY_DIRECTION,
          limit: UserProduct.USER_PRODUCT_RETURNS_LIMIT,
          page,
        },
      })
      .then((axiosResponse) => axiosResponse.data);
  }
}

export default new UserProfile();
