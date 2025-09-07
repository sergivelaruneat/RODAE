<template>
  <div class="h-screen flex flex-col bg-gray-50">
    <Navbar />

    <div class="flex-1 overflow-y-auto custom-scroll p-4">
      <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 mt-4">
        <!-- Cabecera -->
        <div v-if="loading" class="text-center text-gray-600">Cargando…</div>

        <template v-else-if="profile">
          <div class="flex items-center space-x-6">
            <img
              :src="profile.avatarUrl || '/avatars/default.svg'"
              :key="profile.avatarUrl"
              alt="Foto de perfil"
              class="w-28 h-28 rounded-full object-cover cursor-pointer"
              @click="ampliarFoto = true"
            />

            <div class="flex-1">
              <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold">{{ profile.name }}</h2>

                <div class="flex gap-2">
                  <!-- Asignar rutina: solo si soy trainer y hay follow mutuo -->
                  <button
                    v-if="showAssignButton"
                    @click="openAssignModal"
                    class="px-4 py-1 bg-gradient-to-r from-indigo-900 to-indigo-500 text-white text-sm rounded hover:opacity-90"
                  >
                    Asignar rutina
                  </button>

                  <!-- Seguir / Dejar de seguir -->
                  <button
                    v-if="!relationship.follows"
                    @click="onFollow"
                    class="px-4 py-1 bg-gradient-to-r from-blue-900 to-blue-500 text-white text-sm rounded hover:opacity-90"
                  >
                    Seguir
                  </button>
                  <button
                    v-else
                    @click="onUnfollow"
                    class="px-4 py-1 bg-gradient-to-r from-red-600 to-red-500 text-white text-sm rounded hover:opacity-90"
                  >
                    Dejar de seguir
                  </button>
                </div>
              </div>

              <p class="text-gray-600">{{ profile.email }}</p>

              <p class="text-sm text-gray-500 mt-1">
                Edad: <span>{{ profile.age ?? '—' }}</span>
              </p>
              <p class="text-sm text-gray-500">
                Deporte principal:
                <span v-if="profile.sport">{{ profile.sport }}</span>
                <span v-else class="italic text-gray-400">No definido</span>
              </p>

              <span
                class="inline-block text-xs px-3 py-1 rounded-full mt-1"
                :class="profile.role === 'trainer'
                  ? 'bg-purple-200 text-purple-800'
                  : 'bg-blue-200 text-blue-800'"
              >
                {{ profile.role === 'trainer' ? 'Entrenador' : 'Atleta' }}
              </span>

              <p class="mt-2 text-gray-700" v-if="profile.bio">{{ profile.bio }}</p>
              <p class="mt-2 text-gray-400 italic" v-else>Sin descripción</p>
            </div>
          </div>

          <!-- Tabs (centradas) -->
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
            <!-- PUBLICACIONES -->
            <PublicationFeed
              v-if="tabActiva === 'publicaciones'"
              :posts="publicaciones"
              :current-user="currentUser"
            />

            <!-- RUTINAS -->
            <div v-else>
              <div v-if="routinesLoading" class="text-sm text-gray-500 text-center">
                Cargando rutinas…
              </div>

              <template v-else>
                <!-- Si el perfil visitado es trainer, mostramos creadas + seguidas -->
                <div v-if="profile.role === 'trainer'">
                  <h3 class="text-base font-semibold mb-2">Creadas por {{ profile.name }}</h3>
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
                  <p v-else class="text-sm text-gray-500 mb-6">Aún no ha creado rutinas.</p>

                  <h3 class="text-base font-semibold mb-2">Que sigue</h3>
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
                  <p v-else class="text-sm text-gray-500">No sigue ninguna rutina.</p>
                </div>

                <!-- Si es atleta, solo seguidas -->
                <div v-else>
                  <h3 class="text-base font-semibold mb-2">Rutinas que sigue</h3>
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
                  <p v-else class="text-sm text-gray-500">Aún no sigue ninguna rutina.</p>
                </div>
              </template>
            </div>
          </div>
        </template>

        <div v-else class="text-center text-gray-600">Perfil no encontrado.</div>
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
        <img :src="profile?.avatarUrl || '/avatars/default.svg'" class="max-h-[80vh] max-w-full object-contain rounded-lg" />
      </div>
    </div>

    <!-- Modal asignar rutina -->
    <div
      v-if="assignOpen"
      class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow w-full max-w-lg p-4">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-semibold">Asignar rutina a {{ profile?.name }}</h3>
          <button @click="assignOpen = false" class="text-xl font-bold hover:text-red-500">×</button>
        </div>

        <div v-if="myCreatedLoading" class="text-center text-gray-600 py-6">Cargando mis rutinas…</div>

        <template v-else>
          <div v-if="myCreated.length === 0" class="text-sm text-gray-500">
            No tienes rutinas creadas. Crea una para poder asignarla.
          </div>

          <ul v-else class="divide-y">
            <li
              v-for="r in myCreated"
              :key="r.id"
              class="flex items-center justify-between py-2"
            >
              <div>
                <p class="font-medium">{{ r.name }}</p>
                <p class="text-xs text-gray-500">
                  {{ r.sport_label || r.sport }} · {{ r.exercises_count }} ejercicios
                </p>
              </div>
              <button
                class="px-3 py-1.5 text-sm font-semibold text-white rounded bg-gradient-to-r from-indigo-900 to-indigo-500 hover:opacity-90"
                @click="assign(r.id)"
              >
                Asignar
              </button>
            </li>
          </ul>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import PublicationFeed from '@/components/PublicationFeed.vue'
import RoutineCard from '@/components/RoutineCard.vue'

import { getProfileByUserId, getUserPublications } from '@/services/profileService'
import {
  getUserFollowedRoutines,
  getUserCreatedRoutines,
  getMyCreatedRoutines,
  assignRoutine
} from '@/services/routineService'
import { getRelationship, followUser, unfollowUser } from '@/services/userService'

// usuario actual (como en ProfileView)
const currentUser = ref(JSON.parse(localStorage.getItem('user') || 'null'))

const route = useRoute()
const router = useRouter()

// estado general
const loading = ref(true)
const profile = ref(null)
const ampliarFoto = ref(false)

// relación de follow (yo->él y él->yo)
const relationship = ref({ follows: false, followed_by: false })

// tabs / contenido
const tabActiva = ref('publicaciones')
const publicaciones = ref([])
const pubsLoading = ref(false)

// rutinas
const routinesLoading = ref(false)
const routinesFollowed = ref([])
const routinesCreated = ref([])

// modal asignar rutina
const assignOpen = ref(false)
const myCreated = ref([])
const myCreatedLoading = ref(false)

// helpers
const roleLabel = computed(() => (profile.value?.role === 'trainer' ? 'Entrenador' : 'Atleta'))
const showAssignButton = computed(() =>
  currentUser.value?.role === 'trainer' &&
  relationship.value.follows &&
  relationship.value.followed_by
)

watch(() => route.params.user, () => cargarVista())

onMounted(cargarVista)

async function cargarVista () {
  try {
    loading.value = true
    tabActiva.value = 'publicaciones'
    publicaciones.value = []
    routinesFollowed.value = []
    routinesCreated.value = []
    relationship.value = { follows: false, followed_by: false }

    const userId = route.params.user
    if (!userId) return

    const p = await getProfileByUserId(userId)
    profile.value = p

    await Promise.all([loadRelationship(userId), cargarPublicaciones(userId)])
  } catch (e) {
    console.error('No se pudo cargar el perfil', e)
    profile.value = null
  } finally {
    loading.value = false
  }
}

async function loadRelationship (userId) {
  try {
    relationship.value = await getRelationship(userId)
  } catch {
    relationship.value = { follows: false, followed_by: false }
  }
}

/* ====== Publicaciones (usar PublicationFeed como en ProfileView) ====== */
async function cargarPublicaciones (userId, page = 1) {
  try {
    pubsLoading.value = true
    const resp = await getUserPublications(userId, page)
    const items = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? resp : [])

    publicaciones.value = items.map(mapToFeedItem)
  } finally {
    pubsLoading.value = false
  }
}

function mapToFeedItem (apiItem) {
  const mediaUrl =
    apiItem.media_url || apiItem.image_url || apiItem.mediaUrl || apiItem.url || null
  const isVideo =
    (apiItem.media_type && apiItem.media_type === 'video') ||
    /\.mp4$|\.webm$|\.ogg$/i.test(mediaUrl || '')

  return {
    id: apiItem.id,
    tipo: isVideo ? 'video' : 'imagen',
    archivos: mediaUrl ? [mediaUrl] : [],
    nombre: apiItem.title ?? apiItem.titulo ?? 'Publicación',
    descripcion: apiItem.content ?? apiItem.contenido ?? '',
    fecha: (apiItem.created_at || apiItem.fecha || '').slice(0, 10),
    deporte: apiItem.sport ? [apiItem.sport] : [],
    comentarios: Array.isArray(apiItem.comments) ? apiItem.comments : [],
    user_id: profile.value?.id,
    usuario: {
      id: profile.value?.id,
      name: profile.value?.name ?? 'Usuario',
      username: profile.value?.username ?? '',
      avatarUrl: profile.value?.avatarUrl ?? '',
      sport: profile.value?.sport ?? ''
    }
  }
}

/* ====== Rutinas ====== */
const onOpenRoutinesTab = async () => {
  tabActiva.value = 'rutinas'
  if (routinesFollowed.value.length || routinesCreated.value.length) return
  await fetchRoutines()
}

async function fetchRoutines () {
  try {
    routinesLoading.value = true
    const userId = profile.value.id

    const followed = await getUserFollowedRoutines(userId).catch(() => [])
    routinesFollowed.value = followed

    if (profile.value.role === 'trainer') {
      const created = await getUserCreatedRoutines(userId).catch(() => [])
      routinesCreated.value = created
    }
  } finally {
    routinesLoading.value = false
  }
}

const openRoutine = (id) => router.push({ name: 'RoutineSelected', params: { id } })

/* ====== Follow / Unfollow ====== */
async function onFollow () {
  try {
    await followUser(profile.value.id)
    relationship.value.follows = true
  } catch (e) {
    console.error(e)
  }
}

async function onUnfollow () {
  try {
    await unfollowUser(profile.value.id)
    relationship.value.follows = false
  } catch (e) {
    console.error(e)
  }
}

/* ====== Asignar rutina (trainer con follow mutuo) ====== */
function openAssignModal () {
  assignOpen.value = true
  loadMyCreated()
}

async function loadMyCreated () {
  try {
    myCreatedLoading.value = true
    myCreated.value = await getMyCreatedRoutines().catch(() => [])
  } finally {
    myCreatedLoading.value = false
  }
}

async function assign (routineId) {
  try {
    await assignRoutine(routineId, profile.value.id)
    assignOpen.value = false
    // refrescamos el tab de rutinas seguidas del atleta
    if (tabActiva.value === 'rutinas') await fetchRoutines()
  } catch (e) {
    console.error(e)
    alert('No se pudo asignar la rutina.')
  }
}
</script>

