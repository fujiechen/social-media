import { fetchWrapper } from "@/helpers/fetch-wrapper";
import { processParamsObjectToString } from "@/utils";

const baseUrl = `${process.env.VUE_APP_API_URL}/medias/users`;

export const fetchMediaUserList = async (params = null) => {
  const paramsString = processParamsObjectToString(params);
  return await fetchWrapper.get(`${baseUrl}${paramsString && '?'+paramsString}`, null);
};

export const fetchMediaUserByUserId = async (userId) => {
  const response = await fetchWrapper.get(`${baseUrl}/${userId}`, null);
  return response.data;
};
