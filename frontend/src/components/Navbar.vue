<template>
  <nav class="bg-white shadow px-6 py-3 flex justify-between items-center">
    <!-- Logo -->
    <div class="flex items-center space-x-3">
      <img src="/logoSF.png" alt="RODAE Logo" class="h-12 w-auto" />
    </div>

    <!-- Menú centrado con enlaces y buscador -->
    <div class="flex-1 flex justify-center">
      <div class="flex items-center space-x-10">
        <router-link to="/publications" class="flex items-center space-x-1 hover:text-blue-600">
          <ImageIcon class="w-5 h-5" />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-500">Publicaciones</span>
        </router-link>
        <router-link to="/messages" class="flex items-center space-x-1 hover:text-blue-600">
          <MailIcon class="w-5 h-5" />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-500">Mensajes</span>
        </router-link>
        <router-link to="/routines" class="flex items-center space-x-1 hover:text-blue-600">
          <DumbbellIcon class="w-5 h-5" />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-500">Rutinas</span>
        </router-link>
        <router-link to="/progress" class="flex items-center space-x-1 hover:text-blue-600">
          <BarChartIcon class="w-5 h-5" />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-500">Progreso</span>
        </router-link>
        <router-link to="/profile" class="flex items-center space-x-1 hover:text-blue-600">
          <UserIcon class="w-5 h-5" />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-500">Perfil</span>
        </router-link>

        <!-- Buscador -->
        <div class="relative">
          <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar usuario"
            class="pl-3 pr-8 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
            @keydown.enter="goSearchUsers"
          />
          <button
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
            aria-label="Buscar"
            @click="goSearchUsers"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2.5a7.5 7.5 0 010 14.15z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Logout -->
    <button @click="logout" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 mr-10">
      Logout
    </button>
  </nav>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ImageIcon, MailIcon, DumbbellIcon, BarChartIcon, UserIcon } from 'lucide-vue-next'
import http from '@/services/http'

const router = useRouter()
const route  = useRoute()

const busqueda = ref(route.query.q?.toString() ?? '')

const goSearchUsers = () => {
  const q = busqueda.value.trim()
  if (!q) return
  // si ya estoy en la vista, replace para no apilar historial
  const nav = { name: 'UsersSearch', query: { q } }
  if (router.currentRoute.value.name === 'UsersSearch') router.replace(nav)
  else router.push(nav)
}

watch(() => route.query.q, v => { busqueda.value = (v ?? '').toString() })

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  delete http.defaults.headers.common.Authorization // quitar header en caliente
  router.push('/login')
}
</script>
