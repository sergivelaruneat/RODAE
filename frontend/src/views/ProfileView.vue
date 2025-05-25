<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />

    <div class="max-w-4xl mx-auto mt-8 p-6 bg-white rounded-lg shadow">
      <!-- Cabecera de perfil -->
      <div class="flex items-center space-x-6">
        <img
          src="/perfilUsuario.jpg"
          alt="Foto de perfil"
          class="w-28 h-28 rounded-full object-cover cursor-pointer"
          @click="ampliarFoto = true"
        />
        <div class="flex-1">
          <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold">{{ nombre }}</h2>
            <button
              class="ml-4 px-4 py-1 bg-gradient-to-r from-blue-900 to-blue-500 text-white text-sm rounded hover:opacity-90"
              @click="goToEditProfile"
            >
              Editar perfil
            </button>
          </div>
          <p class="text-gray-600">{{ correo }}</p>
          <p class="text-sm text-gray-500 mt-1">Edad: {{ edad }}</p>
          <p class="text-sm text-gray-500">Deporte principal: {{ deporte }}</p>
          <span class="inline-block bg-purple-200 text-purple-800 text-xs px-3 py-1 rounded-full mt-1">
            {{ rol }}
          </span>
          <p class="mt-2 text-gray-700">{{ descripcion }}</p>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex justify-center mt-8 border-b">
        <button
          class="px-6 py-2 text-sm font-semibold"
          :class="tabActiva === 'publicaciones' ? 'border-b-2 border-blue-500 text-blue-700' : 'text-gray-500'"
          @click="tabActiva = 'publicaciones'"
        >
          Publicaciones
        </button>
        <button
          class="px-6 py-2 text-sm font-semibold"
          :class="tabActiva === 'rutinas' ? 'border-b-2 border-blue-500 text-blue-700' : 'text-gray-500'"
          @click="tabActiva = 'rutinas'"
        >
          Rutinas
        </button>
      </div>

      <!-- Contenido de las tabs con altura fija y scroll si hay muchas publicaciones -->
      <div class="mt-6 max-h-[650px] overflow-y-auto">
        <PublicationFeed
          v-if="tabActiva === 'publicaciones'"
          :posts="publicaciones"
        />
        <div v-else>
          <p class="text-sm text-gray-500 text-center">Aquí irán las rutinas del usuario.</p>
        </div>
      </div>
    </div>

    <!-- Modal zoom imagen perfil -->
    <div
      v-if="ampliarFoto"
      class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50"
    >
      <div class="relative">
        <button
          class="absolute top-2 right-2 text-white text-3xl font-bold z-50 hover:text-red-500"
          @click="ampliarFoto = false"
        >
          ×
        </button>
        <img src="/perfilUsuario.jpg" class="max-h-[80vh] max-w-full object-contain rounded-lg" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import PublicationFeed from '@/components/PublicationFeed.vue'

const router = useRouter()

const nombre = 'Sergio Velarde Álvarez'
const correo = 'sergio@example.com'
const edad = 26
const deporte = 'Powerlifting'
const descripcion = 'Mi vida se basa en estar entrenando o lesionado.'
const rol = 'Entrenador'

const ampliarFoto = ref(false)
const tabActiva = ref('publicaciones')

// Simulación de publicaciones cargadas en la vista
const publicaciones = ref([
  {
    id: 1,
    tipo: 'imagen',
    archivos: ['/publicacion1.jpg', '/publicacion2.jpg'],
    nombre: 'Entrenando en casa',
    descripcion: 'Sesión de fuerza completa',
    fecha: '2025-05-20',
    deporte: ['Powerlifting'],
    comentarios: []
  },
  {
    id: 2,
    tipo: 'video',
    archivos: ['/videodominadas.mp4'],
    nombre: 'Dominadas explosivas',
    descripcion: 'Nuevo récord personal',
    fecha: '2025-05-19',
    deporte: ['CrossFit'],
    comentarios: []
  },
  {
    id: 3,
    tipo: 'video',
    archivos: ['/videopesomuerto.mp4'],
    nombre: 'Peso muerto top',
    descripcion: 'Entreno del viernes',
    fecha: '2025-05-18',
    deporte: ['Powerlifting'],
    comentarios: []
  },
  {
    id: 4,
    tipo: 'imagen',
    archivos: ['/pesomuertofoto.jpg'],
    nombre: 'Posando con barra',
    descripcion: 'Después del levantamiento 💪',
    fecha: '2025-05-17',
    deporte: ['Bodybuilding'],
    comentarios: []
  },
  {
    id: 5,
    tipo: 'imagen',
    archivos: ['/fotoespejo.jpeg'],
    nombre: 'Espejito mágico',
    descripcion: 'Check de progreso',
    fecha: '2025-05-16',
    deporte: ['Fitness'],
    comentarios: []
  },
  {
    id: 6,
    tipo: 'imagen',
    archivos: ['/publicacion2.jpg'],
    nombre: 'Cardio outdoors',
    descripcion: 'Sesión de senderismo',
    fecha: '2025-05-15',
    deporte: ['Senderismo'],
    comentarios: []
  }
])

const goToEditProfile = () => {
  router.push('/editprofile')
}
</script>