import { fetchWrapper } from "@/helpers/fetch-wrapper";
import { processParamsObjectToString } from "@/utils";

const baseUrl = `${process.env.VUE_APP_API_URL}/products`;

export const fetchProductList = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}${paramsString && '?'+paramsString}`, null);
};

export const fetchProductById = async (productId) => {
  const response = await fetchWrapper.get(`${baseUrl}/${productId}`, null);
  return response.data;
};
