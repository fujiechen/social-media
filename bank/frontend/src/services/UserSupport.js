import client from './api';

const BASE_URL = 'user/support';

class UserSupport {
  create(support) {
    return client
      .post(BASE_URL, {
        comment: support.comment ?? '',
      })
      .then((axiosResponse) => axiosResponse.data);
  }
}

export default new UserSupport();
