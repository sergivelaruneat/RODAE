<template>
  <div class="h-screen flex flex-col bg-gray-50">
    <Navbar />

    <div class="flex-1 overflow-y-auto custom-scroll p-4">
      <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 mt-4">
        <!-- Cabecera -->
        <div class="flex items-center space-x-6">
          <img
            src="/elenaPerfil.PNG"
            alt="Foto de perfil"
            class="w-28 h-28 rounded-full object-cover cursor-pointer"
            @click="ampliarFoto = true"
          />
          <div class="flex-1">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-bold">Elena Serna</h2>
                <p class="text-gray-600">elena@example.com</p>
              </div>
              <button
                @click="seguir = !seguir"
                class="ml-4 px-4 py-1 w-28 text-white text-sm rounded hover:opacity-90 text-center"
                :class="seguir
                  ? 'bg-gradient-to-r from-gray-400 to-gray-600'
                  : 'bg-gradient-to-r from-blue-900 to-blue-500'"
              >
                {{ seguir ? 'Seguido' : 'Seguir' }}
              </button>
            </div>
            <p class="text-sm text-gray-500 mt-1">Edad: 24</p>
            <p class="text-sm text-gray-500">Deporte principal: Powerlifting</p>
            <span class="inline-block bg-blue-200 text-blue-800 text-xs px-3 py-1 rounded-full mt-1">
              Atleta
            </span>
            <p class="mt-2 text-gray-700">
              Siempre motivada por superar mis marcas y crecer cada día en el entrenamiento.
            </p>
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

        <!-- Contenido -->
        <div class="mt-6 max-h-[650px] overflow-y-auto">
          <PublicationFeed v-if="tabActiva === 'publicaciones'" :posts="publicaciones" />
          <div v-else class="text-center text-sm text-gray-500">
            Aquí irán las rutinas del usuario.
          </div>
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
        <img src="/elenaPerfil.PNG" class="max-h-[80vh] max-w-full object-contain rounded-lg" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Navbar from '@/components/Navbar.vue'
import PublicationFeed from '@/components/PublicationFeed.vue'

const tabActiva = ref('publicaciones')
const ampliarFoto = ref(false)
const seguir = ref(false)

const publicaciones = ref([
  {
    id: 1,
    tipo: 'imagen',
    archivos: ['/fotoespejo.jpeg'],
    nombre: 'Espejito mágico',
    descripcion: 'Check de progreso',
    fecha: '2025-05-16',
    deporte: ['Fitness'],
    comentarios: []
  },
  {
    id: 2,
    tipo: 'imagen',
    archivos: ['/publicacion2.jpg'],
    nombre: 'Cardio outdoors',
    descripcion: 'Sesión de senderismo',
    fecha: '2025-05-15',
    deporte: ['Senderismo'],
    comentarios: []
  },
  {
    id: 3,
    tipo: 'imagen',
    archivos: ['/elenaPerfil.PNG'],
    nombre: 'Rostro de esfuerzo',
    descripcion: 'Una buena sesión termina con una sonrisa',
    fecha: '2025-05-14',
    deporte: ['Powerlifting'],
    comentarios: []
  }
])
</script>