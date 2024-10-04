import client from './api';

const BASE_URL = 'user/addresses';

class UserAddress {
  list() {
    return client
      .get(BASE_URL)
      .then((axiosResponse) => axiosResponse.data);
  }

  get(addressId) {
    return client
      .get(`${BASE_URL}/${addressId}`)
      .then((axiosResponse) => axiosResponse.data);
  }

  delete(addressId) {
    return client.delete(`${BASE_URL}/${addressId}`);
  }

  create(address) {
    return client
      .post(BASE_URL, {
        name: address.name,
        address: address.address,
        phone: address.phone,
        country: address.country,
        province: address.province,
        city: address.city,
        zip: address.zip,
        comment: address.comment,
      })
      .then((axiosResponse) => axiosResponse.data);
  }

  update(id, address) {
    return client
      .put(`${BASE_URL}/${id}`, {
        name: address.name,
        address: address.address,
        phone: address.phone,
        country: address.country,
        province: address.province,
        city: address.city,
        zip: address.zip,
        comment: address.comment,
      })
      .then((axiosResponse) => axiosResponse.data);
  }
}

export default new UserAddress();
