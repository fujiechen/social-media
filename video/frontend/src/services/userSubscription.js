import { fetchWrapper } from "@/helpers/fetch-wrapper";
import { processParamsObjectToString } from "@/utils";

const baseUrl = `${process.env.VUE_APP_API_URL}/user/subscriptions`;

export const fetchUserSubscriptionMedias = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/medias${paramsString && '?'+paramsString}`, null);
};

export const fetchUserSubscriptions = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}${paramsString && '?'+paramsString}`, null);
};

export const fetchSubscribers = async (params = null) => {
  const paramsString = processParamsObjectToString(params);

  return await fetchWrapper.get(`${baseUrl}/subscribers${paramsString && '?'+paramsString}`, null);
};

export const fetchFriends = async (params = null) => {
  const paramsString = processParamsObjectToString(params);

  return await fetchWrapper.get(`${baseUrl}/friends${paramsString && '?'+paramsString}`, null);
};
