import { fetchWrapper } from "@/helpers/fetch-wrapper";
import { processParamsObjectToString } from "@/utils";

const baseUrl = `${process.env.VUE_APP_API_URL}/medias/categories`;

export const fetchCategoryList = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}${paramsString && '?'+paramsString}`, null);
};

export const getCategory = async (categoryId) => {
  const response = await fetchWrapper.get(`${baseUrl}/${categoryId}`, null);
  return response.data;
};
