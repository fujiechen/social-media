import { fetchWrapper } from "@/helpers/fetch-wrapper";

const baseUrl = `${process.env.VUE_APP_API_URL}/user/subscriptions`;

export const subscribeUserByUserId = async (userId) => {
  const response = await fetchWrapper.post(`${baseUrl}/${userId}`, null);
  return response.data;
};

export const unsubscribeUserByUserId = async (userId) => {
  const response = await fetchWrapper.delete(`${baseUrl}/${userId}`, null);
  return response.data;
};
