// src/services/reportService.js
import http from './http'

// ------- CRUD básicos -------
export const listReports = (params = {}) =>
  http.get('/reports', { params }).then(r => r.data)

export const getReport = (id) =>
  http.get(`/reports/${id}`).then(r => (r.data?.data ?? r.data))

export const createReport = (payload) =>
  http.post('/reports', payload).then(r => r.data)

export const updateReport = (id, payload) =>
  http.put(`/reports/${id}`, payload).then(r => r.data)

export const deleteReport = (id) =>
  http.delete(`/reports/${id}`)

// ------- Endpoints del dashboard -------
export const getCalendar = (params = {}) =>
  http.get('/reports/calendar', { params }).then(r => r.data)

export const getRecentRoutines = (limit = 3, extra = {}) =>
  http.get('/reports/recent-routines', { params: { limit, ...extra } })
      .then(r => r.data?.data ?? r.data)

export const getRecentReports = (limit = 5, extra = {}) =>
  http.get('/reports/recent', { params: { limit, ...extra } })
      .then(r => r.data?.data ?? r.data)

export const getSportBreakdown = (params = {}) =>
  http.get('/reports/sport-breakdown', { params }).then(r => r.data)

