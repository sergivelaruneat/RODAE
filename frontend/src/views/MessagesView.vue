<template>
  <div class="h-screen flex flex-col bg-gray-50">
    <Navbar />

    <div class="flex-1 overflow-y-auto custom-scroll p-4">
      <div class="max-w-4xl mx-auto">
        <!-- Buscador y filtro -->
        <div class="flex items-center justify-between mb-4 gap-2">
          <input
            type="text"
            v-model="busqueda"
            placeholder="Buscar ..."
            class="flex-1 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <div class="relative">
            <button
              @click="mostrarFiltro = !mostrarFiltro"
              class="p-2 rounded hover:bg-gray-200 text-gray-700"
            >
              <Filter class="w-5 h-5" />
            </button>
            <div
              v-if="mostrarFiltro"
              class="absolute right-0 mt-2 w-32 bg-white border border-gray-300 rounded shadow z-10"
            >
              <ul class="text-sm text-gray-700">
                <li><button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="filtroRol = ''; mostrarFiltro = false">Todos</button></li>
                <li><button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="filtroRol = 'Atleta'; mostrarFiltro = false">Atletas</button></li>
                <li><button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="filtroRol = 'Entrenador'; mostrarFiltro = false">Entrenadores</button></li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Lista de chats -->
        <router-link
          v-for="chat in chatsFiltrados"
          :key="chat.id"
          :to="'/chat'"
          class="flex items-center gap-4 mb-4 hover:bg-gray-100 p-2 rounded cursor-pointer"
        >
          <img :src="chat.foto" alt="perfil" class="w-12 h-12 rounded-full object-cover" />
          <div class="flex-1">
            <p class="font-semibold flex items-center gap-2">
              {{ chat.nombre }}
              <span class="text-xs px-2 py-1 rounded-full"
                :class="chat.rol === 'Entrenador' ? 'bg-purple-200 text-purple-800' : 'bg-blue-200 text-blue-800'">
                {{ chat.rol }}
              </span>
            </p>
            <p class="text-sm text-gray-600 truncate">{{ chat.ultimoMensaje }}</p>
          </div>
          <span class="text-xs text-gray-400">{{ chat.fecha }}</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Navbar from '@/components/Navbar.vue'
import { Filter } from 'lucide-vue-next'

const busqueda = ref('')
const filtroRol = ref('')
const mostrarFiltro = ref(false)

const chats = ref([
  {
    id: 1,
    nombre: 'Elena Serna',
    foto: '/publicacion1.jpg',
    ultimoMensaje: 'Muy bien, yo estoy empezando a hacer ejercicio y me cuesta un poco',
    fecha: '1 h',
    rol: 'Atleta'
  },
  {
    id: 2,
    nombre: 'David',
    foto: '/davidPerfil.PNG',
    ultimoMensaje: 'Tú: Eso será con el tiempo, irás notando las rutinas nuevas',
    fecha: '4 h',
    rol: 'Entrenador'
  },
  {
    id: 3,
    nombre: 'Diego Cayón Terán',
    foto: '/cayonPerfil.PNG',
    ultimoMensaje: 'Poco a poco va calando',
    fecha: '1 d',
    rol: 'Entrenador'
  },
  {
    id: 4,
    nombre: 'Clara 🥲',
    foto: '/claraPerfil.PNG',
    ultimoMensaje: 'El otro día salí a correr y me dolió la rodilla',
    fecha: '2 d',
    rol: 'Atleta'
  },
  {
    id: 5,
    nombre: 'Omar 🐐🍺',
    foto: '/omarPerfil.PNG',
    ultimoMensaje: 'Mañana entrenaré pierna, a ver qué tal me va',
    fecha: '2 d',
    rol: 'Atleta'
  }
])


const chatsFiltrados = computed(() =>
  chats.value.filter((chat) =>
    chat.nombre.toLowerCase().includes(busqueda.value.toLowerCase()) &&
    (filtroRol.value === '' || chat.rol === filtroRol.value)
  )
)
</script>
