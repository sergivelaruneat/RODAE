// src/services/userService.js
import http from './http';

// ---------- Búsqueda ----------
export const searchUsers = (params = {}) =>
  http.get('/users', { params }).then(r => r.data?.data ?? r.data);

// ---------- Relaciones (seguir / dejar de seguir / estado) ----------
export const followUser   = (userId) => http.post(`/users/${userId}/follow`).then(r => r.data);
export const unfollowUser = (userId) => http.delete(`/users/${userId}/follow`).then(r => r.data);

export const getRelationship = async (userId) => {
  try {
    const r = await http.get(`/users/${userId}/relationship`);
    const data = r?.data?.data ?? r?.data;
    return {
      follows: !!data?.follows,
      followed_by: !!data?.followed_by,
      mutual: !!(data?.follows && data?.followed_by),
    };
  } catch (e) {
    // fallback seguro si la ruta no existe o falla
    return { follows: false, followed_by: false, mutual: false };
  }
};

// ---------- Listas de seguimiento ----------
export const getFollowing = (userId, params = {}) =>
  http.get(`/users/${userId}/following`, { params }).then(r => r.data);
// (opcional, por si lo necesitas más adelante)
export const getFollowers = (userId, params = {}) =>
  http.get(`/users/${userId}/followers`, { params }).then(r => r.data);

// ---------- Utilidades ----------
const apiJoin = (path) => {
  const base = (http.defaults.baseURL || '').replace(/\/+$/, '');
  const clean = String(path || '').replace(/^\/+/, '');
  return `${base}/${clean}`;
};
export const userAvatarUrl = (id, v) =>
  apiJoin(`users/${id}/avatar`) + (v ? `?v=${v}` : '');
