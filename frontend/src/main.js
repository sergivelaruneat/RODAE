import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'            
import './style.css'

// Cargar token guardado
const token = localStorage.getItem('token')   
if (token) axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

// (Opcional) baseURL global con tu .env
axios.defaults.baseURL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'

createApp(App).use(router).mount('#app')
