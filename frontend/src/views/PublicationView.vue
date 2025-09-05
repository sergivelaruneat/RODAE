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
            <!-- Avatar: si no hay foto, queda en blanco -->
            <div class="cursor-pointer" @click="irAlPerfil(post.usuario)">
              <img
                v-if="post.usuario?.avatarUrl"
                :src="post.usuario.avatarUrl"
                class="w-10 h-10 rounded-full object-cover"
                :alt="post.usuario?.name || 'Usuario'"
                @error="post.usuario.avatarUrl = ''"  
              />
              <div v-else class="w-10 h-10 rounded-full bg-gray-200"></div>
            </div>
            <!-- Nombre + deporte -->
            <div class="cursor-pointer" @click="irAlPerfil(post.usuario)">
              <p class="font-semibold">{{ post.usuario?.name || 'Usuario' }}</p>
              <p class="text-sm text-gray-500">
                {{ (Array.isArray(post.deporte) ? post.deporte : []).join(', ') }}
              </p>
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
      :current-user="currentUser" 
      @close="modalVisible = false"
      @deleted="handleDeleted" 
    />

    <!-- Formulario para nueva publicación -->
    <AddPublicationForm
      v-if="mostrarFormulario"
      @close="mostrarFormulario = false"
      @created="onCreated"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '@/components/Navbar.vue'
import PublicationItem from '@/components/PublicationItem.vue'
import PublicationModal from '@/components/PublicationModal.vue'
import AddPublicationForm from '@/components/AddPublicationForm.vue'

const publicaciones = ref([])
const modalVisible = ref(false)
const mostrarFormulario = ref(false)
const publicacionSeleccionada = ref(null)
const router = useRouter()

const currentUser = ref(JSON.parse(localStorage.getItem('user') || 'null'))

const abrirModal = (post) => {
  publicacionSeleccionada.value = post
  modalVisible.value = true
}

const irAlPerfil = (usuario) => {
  router.push(`/profile/${usuario}`)
}

const handleDeleted = (id) => {
  publicaciones.value = publicaciones.value.filter(p => p.id !== id)
  modalVisible.value = false
}

onMounted(async () => {
  await cargarFeed()
})

function onCreated(serverPublication) {
  publicaciones.value.unshift(mapPublicationFromApi(serverPublication))
  mostrarFormulario.value = false
}

async function cargarFeed() {
  try {
    const { data } = await axios.get('/publications/feed', {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    const items = Array.isArray(data?.data) ? data.data : []
    publicaciones.value = items.map(mapPublicationFromApi)
  } catch (e) {
    console.error('Error cargando feed', e)
    publicaciones.value = []
  }
}

function mapPublicationFromApi(p) {
  const url = p?.media_url || ''
  const lower = url.toLowerCase()
  const isVideo = lower.endsWith('.mp4') || lower.endsWith('.webm') || lower.endsWith('.mov')

  return {
    id: p.id,
    tipo: isVideo ? 'video' : 'imagen',
    archivos: url ? [url] : [],
    titulo: p.title ?? (p.content?.slice(0, 40) || ''),
    descripcion: p.content || '',
    fecha: p.created_at ? new Date(p.created_at).toLocaleDateString() : '',
    deporte: p.sport ? [p.sport] : [],
    comentarios: [],
    nombreUsuario: p.user?.name ?? 'Usuario',
    usuario: p.user?.username ?? '',
    fotoPerfil: p.user?.avatar_url ?? '/perfilUsuario.jpg',
  }
}

////////////////////////////
// const publicaciones = ref([
//   {
//     id: 1,
//     tipo: 'imagen',
//     archivos: ['/publicacion1.jpg', '/publicacion2.jpg'],
//     titulo: 'Entrenando en casa',
//     descripcion: 'Sesión de fuerza completa',
//     fecha: '2025-05-20',
//     deporte: ['Powerlifting'],
//     comentarios: [],
//     nombreUsuario: 'Elena Serna',
//     usuario: 'elenaserna80',
//     fotoPerfil: '/elenaPerfil.PNG'
//   },
//   {
//     id: 2,
//     tipo: 'video',
//     archivos: ['/videodominadas.mp4'],
//     titulo: 'Dominadas explosivas',
//     descripcion: 'Nuevo récord personal',
//     fecha: '2025-05-19',
//     deporte: ['CrossFit'],
//     comentarios: [],
//     nombreUsuario: 'Omar González',
//     usuario: 'omar_fit',
//     fotoPerfil: '/omarPerfil.PNG'
//   },
//   {
//     id: 3,
//     tipo: 'video',
//     archivos: ['/videopesomuerto.mp4'],
//     titulo: 'Peso muerto top',
//     descripcion: 'Entreno del viernes',
//     fecha: '2025-05-18',
//     deporte: ['Powerlifting'],
//     comentarios: [],
//     nombreUsuario: 'David Conde',
//     usuario: 'dconde97',
//     fotoPerfil: '/davidPerfil.PNG'
//   },
//   {
//     id: 4,
//     tipo: 'imagen',
//     archivos: ['/pesomuertofoto.jpg'],
//     titulo: 'Posando con barra',
//     descripcion: 'Después del levantamiento 💪',
//     fecha: '2025-05-17',
//     deporte: ['Bodybuilding'],
//     comentarios: [],
//     nombreUsuario: 'Diego Cayón',
//     usuario: 'dcayon10',
//     fotoPerfil: '/cayonPerfil.PNG'
//   },
//   {
//     id: 5,
//     tipo: 'imagen',
//     archivos: ['/fotoespejo.jpeg'],
//     titulo: 'Espejito mágico',
//     descripcion: 'Check de progreso',
//     fecha: '2025-05-16',
//     deporte: ['Fitness'],
//     comentarios: [],
//     nombreUsuario: 'Clara',
//     usuario: 'clara_fit',
//     fotoPerfil: '/claraPerfil.PNG'
//   },
//   {
//     id: 6,
//     tipo: 'imagen',
//     archivos: ['/publicacion2.jpg'],
//     titulo: 'Cardio outdoors',
//     descripcion: 'Sesión de senderismo',
//     fecha: '2025-05-15',
//     deporte: ['Senderismo'],
//     comentarios: [],
//     nombreUsuario: 'Sergio Velarde',
//     usuario: 'servar99',
//     fotoPerfil: '/perfilUsuario.jpg'
//   }
// ])
</script>

