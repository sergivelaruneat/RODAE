<template>
  <div class="h-screen flex flex-col bg-gray-50">
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

    <div class="flex-1 overflow-y-auto custom-scroll p-4">
      <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 mt-4">
        <!-- Cabecera -->
        <div class="flex items-center space-x-6">
          <img
            :src="perfil?.avatarUrl || '/avatars/default.svg'"
            :key="perfil?.avatarUrl"         
            alt="Foto de perfil"
            class="w-28 h-28 rounded-full object-cover cursor-pointer"
            @click="ampliarFoto = true"
          />
          <div class="flex-1">
            <div class="flex items-center justify-between">
              <h2 class="text-2xl font-bold">{{ nombre }}</h2>
              <div class="flex gap-2">
                <button
                  class="px-4 py-1 bg-gradient-to-r from-blue-500 to-blue-300 text-white text-sm rounded hover:opacity-90"
                  @click="mostrarSeguidos = true"
                >
                  Seguidos
                </button>
                <button
                  class="ml-4 px-4 py-1 bg-gradient-to-r from-blue-900 to-blue-500 text-white text-sm rounded hover:opacity-90"
                  @click="goToEditProfile"
                >
                  Editar perfil
                </button>
              </div>
            </div>
            <p class="text-gray-600">{{ correo }}</p>
            <p class="text-sm text-gray-500 mt-1">Edad: {{ edad }}</p>
            <p class="text-sm text-gray-500">
              Deporte principal:
              <span v-if="deporte">{{ deporte }}</span>
              <span v-else class="italic text-gray-400">No definido</span>
            </p>
            <span
              class="inline-block text-xs px-3 py-1 rounded-full mt-1"
              :class="rol === 'trainer'
                ? 'bg-purple-200 text-purple-800'
                : 'bg-blue-200 text-blue-800'"
            >
              {{ rol === 'trainer' ? 'Entrenador' : 'Atleta' }}
            </span>
            <p class="mt-2 text-gray-700" v-if="descripcion">{{ descripcion }}</p>
            <p class="mt-2 text-gray-400 italic" v-else>Sin descripción</p>
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
          <div v-if="cargando" class="text-sm text-gray-500">Cargando…</div>

          <template v-else>
            <PublicationFeed
              v-if="tabActiva === 'publicaciones'"
             :posts="publicaciones"
             :current-user="currentUser"
             @deleted="onPostDeleted"
            />
            <div v-else class="text-center text-sm text-gray-500">
              Aquí irán las rutinas del usuario.
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Modal zoom imagen -->
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
        <img :src="avatarUrl" class="max-h-[80vh] max-w-full object-contain rounded-lg" />
      </div>
    </div>

    <!-- Modal seguidos (placeholder hasta implementar follows) -->
    <div
      v-if="mostrarSeguidos"
      class="fixed inset-0 backdrop-blur-sm bg-gray-800/20 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md max-h-[80vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold">Usuarios seguidos</h3>
          <button @click="mostrarSeguidos = false" class="text-xl font-bold hover:text-red-500">×</button>
        </div>
        <p class="text-sm text-gray-500">Próximamente: aquí verás el listado de seguidos.</p>
      </div>
    </div>

    <AddPublicationForm
      v-if="mostrarFormulario"
      @close="mostrarFormulario = false"
      @publicar="anadirPublicacion"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import PublicationFeed from '@/components/PublicationFeed.vue'
import AddPublicationForm from '@/components/AddPublicationForm.vue'
import { getMyProfile, getUserPublications } from '@/services/profileService'

const currentUser = ref(JSON.parse(localStorage.getItem('user') || 'null'))

const router = useRouter()

// estado UI
const ampliarFoto = ref(false)
const tabActiva = ref('publicaciones')
const mostrarFormulario = ref(false)
const mostrarSeguidos = ref(false)
const cargando = ref(true)

// perfil (dinámico)
const perfil = ref(null)

const nombre = computed(() => perfil.value?.name ?? '')
const correo = computed(() => perfil.value?.email ?? '')
const edad = computed(() => perfil.value?.age ?? '—')
const deporte = computed(() => perfil.value?.sport ?? '')
const descripcion = computed(() => perfil.value?.bio ?? '')
const rol = computed(() => perfil.value?.role ?? 'athlete')
const avatarUrl = computed(() => perfil.value?.avatarUrl || placeholderAvatar)

const placeholderAvatar =
  'data:image/svg+xml;utf8,' +
  encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160"><rect width="100%" height="100%" fill="#f1f5f9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#94a3b8" font-family="Arial" font-size="14">Sin avatar</text></svg>`)

// publicaciones (dinámicas)
const publicaciones = ref([])

onMounted(async () => {
  try {
    cargando.value = true
    // 1) Perfil propio
    const p = await getMyProfile() // <- devuelve el objeto “desenvuelto”
    perfil.value = p

    // 2) Publicaciones del usuario en el formato que espera PublicationFeed
    await cargarPublicaciones(p.id)
  } catch (e) {
    console.error('Error cargando perfil o publicaciones', e)
  } finally {
    cargando.value = false
  }
})

async function cargarPublicaciones (userId, page = 1) {
  const resp = await getUserPublications(userId, page)

  // Tu endpoint suele devolver { data: [...], meta: {...} }
  const items = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? resp : [])

  publicaciones.value = items.map(mapToFeedItem)
}

/**
 * Mapea la publicación de API al formato que usa PublicationFeed
 * Formato esperado por tu feed “quemado”:
 *  {
 *    id, tipo: 'imagen' | 'video',
 *    archivos: [urls...], nombre, descripcion, fecha(YYYY-MM-DD), deporte: [..],
 *    comentarios: []
 *  }
 */
function mapToFeedItem (apiItem) {
  const mediaUrl =
    apiItem.media_url || apiItem.image_url || apiItem.mediaUrl || apiItem.url || null
  const isVideo =
    (apiItem.media_type && apiItem.media_type === 'video') ||
    /\.mp4$|\.webm$|\.ogg$/i.test(mediaUrl || '')
    const ownerId = apiItem.user?.id ?? apiItem.user_id ?? perfil.value?.id ?? null

  return {
    id: apiItem.id,
    tipo: isVideo ? 'video' : 'imagen',
    archivos: mediaUrl ? [mediaUrl] : [],
    nombre: apiItem.title ?? apiItem.titulo ?? 'Publicación',
    descripcion: apiItem.content ?? apiItem.contenido ?? '',
    fecha: (apiItem.created_at || apiItem.fecha || '').slice(0, 10),
    deporte: apiItem.sport ? [apiItem.sport] : [],
    comentarios: Array.isArray(apiItem.comments) ? apiItem.comments : [],
    user_id: ownerId,
    usuario: {
      id: ownerId,
      name: apiItem.user?.name ?? perfil.value?.name ?? 'Usuario',
      username: apiItem.user?.username ?? perfil.value?.username ?? '',
      avatarUrl: apiItem.user?.avatar_url ?? perfil.value?.avatarUrl ?? '',
      sport: apiItem.user?.sport ?? perfil.value?.sport ?? ''
   }
  }
}

// Al publicar desde el modal, refrescamos el feed
const anadirPublicacion = async (_nueva) => {
  try {
    mostrarFormulario.value = false
    if (perfil.value?.id) {
      await cargarPublicaciones(perfil.value.id, 1)
    }
  } catch (e) {
    console.error('No se pudo refrescar el feed tras publicar', e)
  }
}


const goToEditProfile = () => {
  router.push('/editprofile')
}
function onPostDeleted(id) {
  const nid = Number(id)
  publicaciones.value = publicaciones.value.filter(p => Number(p.id) !== nid)
}
</script>
