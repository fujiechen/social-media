import client from './api';

const BASE_URL = 'products';

class Product {
  list(params) {
    return client
      .get(`${BASE_URL}/index`, {
        params: {
          limit: params.limit,
          is_recommend: params.isRecommend,
          order_by: params.orderBy,
          sort: params.sort,
        },
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  get(id) {
    return client
      .get(`${BASE_URL}/${id}`)
      .then((axiosResponse) => axiosResponse.data);
  }

  listGroupByCategories() {
    return client
      .get(`${BASE_URL}/categories/index`)
      .then((axiosResponse) => axiosResponse.data);
  }
}

export default new Product();
