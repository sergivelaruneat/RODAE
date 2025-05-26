<template>
  <!-- Contenedor principal con layout vertical -->
  <div class="h-screen flex flex-col bg-gray-50">
    <!-- Navbar fijo arriba -->
    <Navbar />

    <!-- Botón flotante fijo -->
    <div class="fixed top-25 right-15 z-40">
      <button
        @click="mostrarFormulario = true"
        class="px-4 py-2 text-sm font-semibold text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 shadow"
      >
        Añadir publicación
      </button>
    </div>

    <!-- Contenedor scrollable -->
    <div class="flex-1 overflow-y-auto custom-scroll p-4">
      <div class="max-w-2xl mx-auto">
        <div
          v-for="post in publicaciones"
          :key="post.id"
          class="mb-8 bg-white rounded-lg shadow"
        >
          <!-- Banner superior -->
          <div class="flex items-center gap-3 p-4 border-b">
            <img
              :src="post.fotoPerfil"
              class="w-10 h-10 rounded-full object-cover cursor-pointer"
              @click="irAlPerfil(post.usuario)"
            />
            <div class="cursor-pointer" @click="irAlPerfil(post.usuario)">
              <p class="font-semibold">{{ post.nombreUsuario }}</p>
              <p class="text-sm text-gray-500">{{ post.deporte.join(', ') }}</p>
            </div>
          </div>

          <!-- Contenido publicación con click -->
          <div @click="abrirModal(post)" class="cursor-pointer">
            <PublicationItem :post="post" />
          </div>

          <!-- Título y botón ver comentarios -->
          <div class="p-4 border-t">
            <p class="font-semibold">{{ post.titulo }}</p>
            <button
              @click="abrirModal(post)"
              class="text-sm text-blue-600 hover:underline mt-1"
            >
              Ver comentarios
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de publicación ampliada -->
    <PublicationModal
      v-if="modalVisible"
      :post="publicacionSeleccionada"
      @close="modalVisible = false"
    />

    <!-- Formulario para nueva publicación -->
    <AddPublicationForm
      v-if="mostrarFormulario"
      @close="mostrarFormulario = false"
      @publicar="anadirPublicacion"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import PublicationItem from '@/components/PublicationItem.vue'
import PublicationModal from '@/components/PublicationModal.vue'
import AddPublicationForm from '@/components/AddPublicationForm.vue'

const modalVisible = ref(false)
const mostrarFormulario = ref(false)
const publicacionSeleccionada = ref(null)
const router = useRouter()

const abrirModal = (post) => {
  publicacionSeleccionada.value = post
  modalVisible.value = true
}

const irAlPerfil = (usuario) => {
  router.push(`/profile/${usuario}`)
}

const anadirPublicacion = (nueva) => {
  publicaciones.value.unshift({
    ...nueva,
    id: publicaciones.value.length + 1,
    tipo: nueva.archivos[0]?.type.includes('video') ? 'video' : 'imagen',
    nombreUsuario: 'Sergio Velarde',
    usuario: 'servar99',
    fotoPerfil: '/perfilUsuario.jpg',
    comentarios: [],
    deporte: [nueva.deporte]
  })
}

const publicaciones = ref([
  {
    id: 1,
    tipo: 'imagen',
    archivos: ['/publicacion1.jpg', '/publicacion2.jpg'],
    titulo: 'Entrenando en casa',
    descripcion: 'Sesión de fuerza completa',
    fecha: '2025-05-20',
    deporte: ['Powerlifting'],
    comentarios: [],
    nombreUsuario: 'Elena Serna',
    usuario: 'elenaserna80',
    fotoPerfil: '/elenaPerfil.PNG'
  },
  {
    id: 2,
    tipo: 'video',
    archivos: ['/videodominadas.mp4'],
    titulo: 'Dominadas explosivas',
    descripcion: 'Nuevo récord personal',
    fecha: '2025-05-19',
    deporte: ['CrossFit'],
    comentarios: [],
    nombreUsuario: 'Omar González',
    usuario: 'omar_fit',
    fotoPerfil: '/omarPerfil.PNG'
  },
  {
    id: 3,
    tipo: 'video',
    archivos: ['/videopesomuerto.mp4'],
    titulo: 'Peso muerto top',
    descripcion: 'Entreno del viernes',
    fecha: '2025-05-18',
    deporte: ['Powerlifting'],
    comentarios: [],
    nombreUsuario: 'David Conde',
    usuario: 'dconde97',
    fotoPerfil: '/davidPerfil.PNG'
  },
  {
    id: 4,
    tipo: 'imagen',
    archivos: ['/pesomuertofoto.jpg'],
    titulo: 'Posando con barra',
    descripcion: 'Después del levantamiento 💪',
    fecha: '2025-05-17',
    deporte: ['Bodybuilding'],
    comentarios: [],
    nombreUsuario: 'Diego Cayón',
    usuario: 'dcayon10',
    fotoPerfil: '/cayonPerfil.PNG'
  },
  {
    id: 5,
    tipo: 'imagen',
    archivos: ['/fotoespejo.jpeg'],
    titulo: 'Espejito mágico',
    descripcion: 'Check de progreso',
    fecha: '2025-05-16',
    deporte: ['Fitness'],
    comentarios: [],
    nombreUsuario: 'Clara',
    usuario: 'clara_fit',
    fotoPerfil: '/claraPerfil.PNG'
  },
  {
    id: 6,
    tipo: 'imagen',
    archivos: ['/publicacion2.jpg'],
    titulo: 'Cardio outdoors',
    descripcion: 'Sesión de senderismo',
    fecha: '2025-05-15',
    deporte: ['Senderismo'],
    comentarios: [],
    nombreUsuario: 'Sergio Velarde',
    usuario: 'servar99',
    fotoPerfil: '/perfilUsuario.jpg'
  }
])
</script>

