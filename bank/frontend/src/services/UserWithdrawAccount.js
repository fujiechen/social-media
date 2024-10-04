import client from './api';

const BASE_URL = 'user/withdraw/accounts';

class UserWithdrawAccount {
  list() {
    return client
      .get(BASE_URL)
      .then((axiosResponse) => axiosResponse.data);
  }

  get(id) {
    return client
      .get(`${BASE_URL}/${id}`)
      .then((axiosResponse) => axiosResponse.data);
  }

  delete(id) {
    return client.delete(`${BASE_URL}/${id}`);
  }

  create(withdrawAccount) {
    return client
      .post(BASE_URL, {
        name: withdrawAccount.name,
        account_number: withdrawAccount.accountNumber,
        bank_name: withdrawAccount.bankName,
        bank_address: withdrawAccount.bankAddress,
        branch_code: withdrawAccount.branchCode,
        transit_code: withdrawAccount.transitCode,
        swift: withdrawAccount.swift,
        comment: withdrawAccount.comment,
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  update(id, withdrawAccount) {
    return client
      .put(`${BASE_URL}/${id}`, {
        name: withdrawAccount.name,
        account_number: withdrawAccount.accountNumber,
        bank_name: withdrawAccount.bankName,
        bank_address: withdrawAccount.bankAddress,
        branch_code: withdrawAccount.branchCode,
        transit_code: withdrawAccount.transitCode,
        swift: withdrawAccount.swift,
        comment: withdrawAccount.comment,
      })
      .then((axiosResponse) => axiosResponse.data);
  }
}

export default new UserWithdrawAccount();
