import { fetchWrapper } from "@/helpers/fetch-wrapper";
import { processParamsObjectToString } from "@/utils";

const baseUrl = `${process.env.VUE_APP_API_URL}/medias/tags`;

export const fetchTagList = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}${paramsString && '?'+paramsString}`, null);
};

export const getTag = async (tagId) => {
  const response = await fetchWrapper.get(`${baseUrl}/${tagId}`, null);
  return response.data;
};
