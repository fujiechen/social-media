import client from './api';

const BASE_URL = 'user/transactions';

class UserAccount {
  list(params) {
    return client
      .get(`${BASE_URL}/index`, {
        params: {
          user_account_id: params.userAccountId,
          last_days: params.lastDays,
        },
      })
      .then((axiosResponse) => axiosResponse.data);
  }
}

export default new UserAccount();
