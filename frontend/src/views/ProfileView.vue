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
                  @click="openSeguidos"
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
            @click="onOpenRoutinesTab"
          >
            Rutinas
          </button>
        </div>

        <!-- Contenido -->
        <div class="mt-6 max-h-[650px] overflow-y-auto">
          <div v-if="cargando" class="text-sm text-gray-500">Cargando…</div>

          <template v-else>
            <!-- PUBLICACIONES -->
            <PublicationFeed
              v-if="tabActiva === 'publicaciones'"
              :posts="publicaciones"
              :current-user="currentUser"
              @deleted="onPostDeleted"
            />

            <!-- RUTINAS -->
            <div v-else>
              <div v-if="routinesLoading" class="text-sm text-gray-500 text-center">
                Cargando rutinas…
              </div>

              <template v-else>
                <!-- Entrenador: creadas por mí -->
                <div v-if="isTrainer">
                  <h3 class="text-base font-semibold mb-2">Creadas por mí</h3>
                  <div class="bg-white rounded border mb-6" v-if="routinesCreated.length">
                    <ul class="divide-y divide-gray-200">
                      <RoutineCard
                        v-for="r in routinesCreated"
                        :key="`c-${r.id}`"
                        :routine="r"
                        @select="openRoutine"
                      />
                    </ul>
                  </div>
                  <p v-else class="text-sm text-gray-500 mb-6">Aún no has creado rutinas.</p>
                </div>

                <!-- Seguidas -->
                <h3 class="text-base font-semibold mb-2">Que sigo</h3>
                <div class="bg-white rounded border" v-if="routinesFollowed.length">
                  <ul class="divide-y divide-gray-200">
                    <RoutineCard
                      v-for="r in routinesFollowed"
                      :key="`f-${r.id}`"
                      :routine="r"
                      @select="openRoutine"
                    />
                  </ul>
                </div>
                <p v-else class="text-sm text-gray-500">
                  {{ isTrainer ? 'No sigues ninguna rutina.' : 'Aún no sigues ninguna rutina.' }}
                </p>
              </template>
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

    <!-- Modal seguidos -->
    <div
      v-if="mostrarSeguidos"
      class="fixed inset-0 backdrop-blur-sm bg-gray-800/20 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md max-h-[80vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold">Usuarios seguidos</h3>
          <button @click="mostrarSeguidos = false" class="text-xl font-bold hover:text-red-500">×</button>
        </div>

        <div v-if="seguidosLoading" class="text-sm text-gray-500">Cargando…</div>

        <template v-else>
          <p v-if="!seguidos.length" class="text-sm text-gray-500">No sigues a nadie todavía.</p>

          <ul v-else class="space-y-2">
            <li v-for="u in seguidos" :key="u.id">
              <UserCard :user="u" @select="goOtherProfile(u.id)" />
            </li>
          </ul>

          <div v-if="segMeta.last_page > 1" class="mt-4 flex items-center justify-between">
            <button
              class="px-3 py-1 rounded border disabled:opacity-50"
              :disabled="segPage <= 1"
              @click="loadSeguidos(segPage - 1)"
            >Anterior</button>

            <span class="text-xs text-gray-500">
              Página {{ segPage }} de {{ segMeta.last_page }}
            </span>

            <button
              class="px-3 py-1 rounded border disabled:opacity-50"
              :disabled="segPage >= segMeta.last_page"
              @click="loadSeguidos(segPage + 1)"
            >Siguiente</button>
          </div>
        </template>
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
import RoutineCard from '@/components/RoutineCard.vue'
import UserCard from '@/components/UserCard.vue'

import { getMyProfile, getUserPublications } from '@/services/profileService'
import { getMyCreatedRoutines, getMyRoutines } from '@/services/routineService'
import { getFollowing, userAvatarUrl } from '@/services/userService'

const router = useRouter()

// ------ usuario logueado (si lo necesitas para pasar al feed) ------
const currentUser = ref(JSON.parse(localStorage.getItem('user') || 'null'))

// ------ estado UI general ------
const ampliarFoto = ref(false)
const tabActiva = ref('publicaciones')
const mostrarFormulario = ref(false)
const cargando = ref(true)

// ------ perfil propio ------
const perfil = ref(null)

const nombre = computed(() => perfil.value?.name ?? '')
const correo = computed(() => perfil.value?.email ?? '')
const edad = computed(() => perfil.value?.age ?? '—')
const deporte = computed(() => perfil.value?.sport ?? '')
const descripcion = computed(() => perfil.value?.bio ?? '')
const rol = computed(() => perfil.value?.role ?? 'athlete')
const isTrainer = computed(() => rol.value === 'trainer')

const placeholderAvatar =
  'data:image/svg+xml;utf8,' +
  encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160"><rect width="100%" height="100%" fill="#f1f5f9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#94a3b8" font-family="Arial" font-size="14">Sin avatar</text></svg>`)

const avatarUrl = computed(() => perfil.value?.avatarUrl || placeholderAvatar)

// ------ publicaciones ------
const publicaciones = ref([])

async function cargarPublicaciones (userId, page = 1) {
  const resp = await getUserPublications(userId, page)
  const items = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? resp : [])
  publicaciones.value = items.map(mapToFeedItem)
}

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

function onPostDeleted (id) {
  const nid = Number(id)
  publicaciones.value = publicaciones.value.filter(p => Number(p.id) !== nid)
}

// ------ rutinas (tab) ------
const routinesLoading = ref(false)
const routinesCreated = ref([])
const routinesFollowed = ref([])

const onOpenRoutinesTab = async () => {
  tabActiva.value = 'rutinas'
  if (routinesCreated.value.length || routinesFollowed.value.length) return
  await fetchRoutines()
}

async function fetchRoutines () {
  try {
    routinesLoading.value = true
    const [followed, created] = await Promise.all([
      getMyRoutines().catch(() => []),
      isTrainer.value ? getMyCreatedRoutines().catch(() => []) : Promise.resolve([])
    ])
    // Si el trainer sigue alguna suya, la quitamos del bloque "seguidas" para no duplicar
    const createdIds = new Set(created.map(r => r.id))
    routinesFollowed.value = followed.filter(r => !createdIds.has(r.id))
    routinesCreated.value = created
  } catch (e) {
    console.error('Error cargando rutinas', e)
    routinesFollowed.value = []
    routinesCreated.value = []
  } finally {
    routinesLoading.value = false
  }
}

const openRoutine = (id) => router.push({ name: 'RoutineSelected', params: { id } })

// ------ modal "Seguidos" ------
const mostrarSeguidos = ref(false)
const seguidosLoading = ref(false)
const seguidos = ref([]) // [{id,name,email,role,avatarUrl}]
const segPage = ref(1)
const segMeta = ref({ last_page: 1, current_page: 1, total: 0 })

const openSeguidos = async () => {
  mostrarSeguidos.value = true
  await loadSeguidos(1)
}

const loadSeguidos = async (page = 1) => {
  if (!perfil.value?.id) return
  seguidosLoading.value = true
  try {
    const resp = await getFollowing(perfil.value.id, { page, per_page: 20 })
    const items = Array.isArray(resp?.data) ? resp.data : (resp?.data ?? [])

    seguidos.value = items.map(u => ({
      id: u.id,
      name: u.name,
      email: u.email,
      role: u.role,
      avatarUrl: userAvatarUrl(u.id, u.avatar_updated_at)
    }))

    segMeta.value = resp?.meta || { last_page: 1, current_page: page, total: items.length }
    segPage.value = segMeta.value.current_page || page
  } catch (e) {
    console.error('No se pudo cargar seguidos', e)
    seguidos.value = []
    segMeta.value = { last_page: 1, current_page: 1, total: 0 }
  } finally {
    seguidosLoading.value = false
  }
}

const goOtherProfile = (id) => {
  mostrarSeguidos.value = false
  router.push({ name: 'OtherProfile', params: { user: id } })
}

// ------ navegación ------
const goToEditProfile = () => router.push('/editprofile')

// ------ montaje ------
onMounted(async () => {
  try {
    cargando.value = true
    const p = await getMyProfile()
    perfil.value = p
    await cargarPublicaciones(p.id)
  } catch (e) {
    console.error('Error cargando perfil o publicaciones', e)
  } finally {
    cargando.value = false
  }
})
</script>
