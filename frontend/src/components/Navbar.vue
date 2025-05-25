<template>
  <nav class="bg-white shadow px-6 py-3 flex justify-between items-center">
    <!-- Logo -->
    <div class="flex items-center space-x-3">
      <img src="/logoSF.png" alt="RODAE Logo" class="h-12 w-auto" />
    </div>

    <!-- Menú centrado -->
    <div class="flex-1 flex justify-center space-x-10">
      <router-link
        to="/feed"
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
    </div>

    <!-- Logout a la derecha -->
    <button
      @click="logout"
      class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
    >
      Logout
    </button>
  </nav>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { ImageIcon, MailIcon, DumbbellIcon, BarChartIcon, UserIcon } from 'lucide-vue-next'

const router = useRouter()
const logout = () => {
  localStorage.removeItem('token')
  router.push('/login')
}
</script>