export const decodeBase64UriSafeToJson = (encodedString) => {
  // Convert URI-safe Base64 back to standard Base64
  let standardBase64 = encodedString.replace(/-/g, '+').replace(/_/g, '/');

  // Pad with "=" to make the length a multiple of 4 if necessary
  while (standardBase64.length % 4) {
    standardBase64 += '=';
  }

  // Decode from Base64 to a UTF-8 encoded byte string
  const byteString = atob(standardBase64);

  // Decode byte string from UTF-8
  const jsonString = decodeURIComponent(byteString);

  // Parse the JSON string back to an object
  return JSON.parse(jsonString);
};

export const encodeJSONToBase64UriSafe = (jsonObject) => {
  // Convert JSON object to string
  const jsonString = JSON.stringify(jsonObject);

  // Encode the string to UTF-8 and then to Base64
  const utf8EncodedString = encodeURIComponent(jsonString);
  const base64String = btoa(utf8EncodedString);

  // Make the Base64 string URI safe
  return base64String.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
};
