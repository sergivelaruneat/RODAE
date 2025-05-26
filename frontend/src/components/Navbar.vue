<template>
  <nav class="bg-white shadow px-6 py-3 flex justify-between items-center">
    <!-- Logo -->
    <div class="flex items-center space-x-3">
      <img src="/logoSF.png" alt="RODAE Logo" class="h-12 w-auto" />
    </div>

    <!-- Menú centrado con enlaces y buscador -->
    <div class="flex-1 flex justify-center">
      <div class="flex items-center space-x-10">
        <router-link
          to="/publications"
          class="flex items-center space-x-1 hover:text-blue-600"
        >
          <ImageIcon class="w-5 h-5" />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-500">Publicaciones</span>
        </router-link>
        <router-link
          to="/messages"
          class="flex items-center space-x-1 hover:text-blue-600"
        >
          <MailIcon class="w-5 h-5" />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-500">Mensajes</span>
        </router-link>
        <router-link
          to="/routines"
          class="flex items-center space-x-1 hover:text-blue-600"
        >
          <DumbbellIcon class="w-5 h-5" />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-500">Rutinas</span>
        </router-link>
        <router-link
          to="/progress"
          class="flex items-center space-x-1 hover:text-blue-600"
        >
          <BarChartIcon class="w-5 h-5" />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-500">Progreso</span>
        </router-link>
        <router-link
          to="/profile"
          class="flex items-center space-x-1 hover:text-blue-600"
        >
          <UserIcon class="w-5 h-5" />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-500">Perfil</span>
        </router-link>

        <!-- Buscador con icono lupa -->
        <div class="relative">
          <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar usuario"
            class="pl-3 pr-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
          <svg
            class="absolute right-2 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2.5a7.5 7.5 0 010 14.15z"
            ></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Logout a la derecha -->
    <button
      @click="logout"
      class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 mr-10"
    >
      Logout
    </button>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { ImageIcon, MailIcon, DumbbellIcon, BarChartIcon, UserIcon } from 'lucide-vue-next'

const router = useRouter()
const busqueda = ref('')

const logout = () => {
  localStorage.removeItem('token')
  router.push('/login')
}
</script>