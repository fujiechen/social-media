import client from './api';

const BASE_URL = 'user/accounts';

class UserAccount {
  list(params = {}) {
    return client
      .get(BASE_URL, {
        params: {
          currency_id: params.currencyId,
        },
      })
      .then((axiosResponse) => axiosResponse.data);
  }
}

export default new UserAccount();
