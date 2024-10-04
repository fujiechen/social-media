import client from './api';

const BASE_URL = 'user/orders';

class UserOrder {
  list(params) {
    return client
      .get(`${BASE_URL}/index`, {
        params: {
          user_account_id: params.userAccountId,
          order_type: params.orderType,
          product_id: params.productId,
          limit: params.limit,
          page: params.page,
        },
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  listFriendsForTransfer(params) {
    return client
      .get(`${BASE_URL}/transfer/users`, {
        params: {
          limit: params.limit,
        },
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  purchase(data) {
    return client
      .post(`${BASE_URL}/purchase`, {
        product_id: data.productId,
        amount: data.amount,
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  deposit(data) {
    return client
      .post(`${BASE_URL}/deposit`, {
        amount: data.amount,
        user_account_id: data.user_account_id,
        payment_method: data.payment_method,
        callback_url: data.callback_url,
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  withdraw(data) {
    return client
      .post(`${BASE_URL}/withdraw`, {
        amount: data.amount,
        user_account_id: data.userAccountId,
        user_withdraw_account_id: data.userWithdrawAccountId,
        user_address_id: data.userAddressId,
        comment: data.comment,
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  exchange(data) {
    return client
      .post(`${BASE_URL}/exchange`, {
        amount: data.amount,
        from_user_account_id: data.fromUserAccountId,
        to_user_account_id: data.toUserAccountId,
        comment: data.comment,
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  transfer(data) {
    return client
      .post(`${BASE_URL}/transfer`, {
        amount: data.amount,
        from_user_account_id: data.fromUserAccountId,
        to_user_email: data.toUserEmail,
        to_user_name: data.toUserName,
        comment: data.comment,
      })
      .then((axiosResponse) => axiosResponse.data);
  }
}

export default new UserOrder();
