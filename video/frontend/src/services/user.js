import { fetchWrapper } from "@/helpers/fetch-wrapper";
import { processParamsObjectToString } from "@/utils";

const baseUrl = `${process.env.VUE_APP_API_URL}/user`;

export const getUserShares = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/shares${paramsString && '?'+paramsString}`, null);
};

export const getUserShareById = async (id) => {
  const response = await fetchWrapper.get(`${baseUrl}/shares/${id}`, null);
  return response.data;
};

export const createUserShare = async (type, shareableId, url) => {
  const response = await fetchWrapper.post(`${baseUrl}/shares`, {
    type: type,
    shareable_id: shareableId,
    url: url,
  });

  return response.data;
};

export const getUserChildren = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/children${paramsString && '?'+paramsString}`, null);
};

export const getUserChildrenOrders = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/children/orders${paramsString && '?'+paramsString}`, null);
};

export const getUserPayouts = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/payouts${paramsString && '?'+paramsString}`, null);
};

export const getOtherUserPayouts = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/payouts/others${paramsString && '?'+paramsString}`, null);
};

export const getUserStat = async () => {
  const response = await fetchWrapper.get(`${baseUrl}/statistics`, null);
  return response.data;
};
