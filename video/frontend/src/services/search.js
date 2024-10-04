import { fetchWrapper } from "@/helpers/fetch-wrapper";
import { processParamsObjectToString } from "@/utils";

const baseUrl = `${process.env.VUE_APP_API_URL}/searches`;

export const fetchSearchHot = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/hot${paramsString && '?'+paramsString}`, null);
};

export const fetchSearchHistory = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}/history${paramsString && '?'+paramsString}`, null);
};
