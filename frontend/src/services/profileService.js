import http from "./http";


export const getMyProfile = () =>
  http.get('/profile').then(r => r.data.data);  // << antes r.data

export const getProfileByUserId = (userId) =>
  http.get(`/users/${userId}/profile`).then(r => r.data.data); // << antes r.data

export const getUserPublications = (userId, page = 1) =>
  http.get('/publications', { params: { user_id: userId, page } })
     .then(r => r.data); // paginación suele venir { data:[], meta:{} }

export const updateMyProfile = (formData) =>
  http.post("/profile?_method=PUT", formData).then(r => r.data); // para multipart
