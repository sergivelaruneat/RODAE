// src/services/profileService.js
import http from './http';

// helper que soporta {data:{...}} o {...}
const unwrap = (r) => (r?.data?.data ?? r?.data);

// Mi perfil (siempre objeto plano)
export const getMyProfile = () =>
  http.get('/profile').then(unwrap);

// Perfil público por id de usuario (siempre objeto plano)
export const getProfileByUserId = (userId) =>
  http.get(`/users/${userId}/profile`).then(unwrap);

// Publicaciones de un usuario (normalmente paginadas)
// si tu backend envuelve, aquí quizá quieras devolver tal cual para conservar meta
export const getUserPublications = (userId, page = 1) =>
  http.get('/publications', { params: { user_id: userId, page } })
     .then(r => r.data);

// Actualizar perfil (multipart)
export const updateMyProfile = (formData) =>
  http.post('/profile?_method=PUT', formData).then(unwrap);