<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />

    <div class="max-w-3xl mx-auto p-4">
      <!-- Cargando -->
      <div v-if="cargando" class="animate-pulse">
        <div class="h-24 bg-white rounded-lg shadow mb-4"></div>
        <div class="h-48 bg-white rounded-lg shadow mb-4"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="bg-red-50 text-red-700 p-4 rounded">
        {{ error }}
      </div>

      <!-- Perfil -->
      <div v-else class="space-y-6">
        <div class="bg-white rounded-lg shadow p-4 flex items-center gap-4">
          <img
            :src="perfil.avatarUrl || placeholderAvatar"
            alt="avatar"
            class="w-20 h-20 rounded-full object-cover border"
          />
          <div class="flex-1">
            <div class="flex items-center gap-2 flex-wrap">
              <h1 class="text-xl font-bold">{{ perfil.name }}</h1>
              <span
                class="text-xs px-2 py-0.5 rounded-full border"
                :class="perfil.role === 'trainer'
                  ? 'border-purple-400 text-purple-700'
                  : 'border-blue-400 text-blue-700'"
              >
                {{ roleLabel }}
              </span>
            </div>
            <p class="text-sm text-gray-600">
              <span v-if="perfil.age">Edad: {{ perfil.age }} · </span>
              <span v-if="perfil.sport">Deporte: {{ perfil.sport }}</span>
              <span v-else class="italic text-gray-400">Deporte no definido</span>
            </p>
            <p class="text-sm text-gray-700 mt-1" v-if="perfil.bio">{{ perfil.bio }}</p>
            <p class="text-sm text-gray-400 mt-1" v-else>Sin descripción</p>
          </div>

          <!-- Botón seguir: lo activaremos cuando implementemos follows -->
          <button
            class="px-3 py-2 text-sm font-semibold rounded border hover:bg-gray-50"
            disabled
            title="Próximamente"
          >
            Seguir
          </button>
        </div>

        <!-- Publicaciones del usuario -->
        <div class="bg-white rounded-lg shadow">
          <div class="p-4 border-b flex items-center justify-between">
            <h2 class="text-lg font-semibold">Publicaciones</h2>
            <div class="text-sm text-gray-500" v-if="paginacion.total">
              {{ paginacion.from }}–{{ paginacion.to }} de {{ paginacion.total }}
            </div>
          </div>

          <div v-if="bloqueadoPublicaciones" class="p-6 text-gray-500">
            Inicia sesión para ver las publicaciones de este usuario.
          </div>

          <div v-else-if="publicaciones.length === 0" class="p-6 text-gray-500">
            Este usuario aún no tiene publicaciones.
          </div>

          <div v-else class="divide-y">
            <div
              v-for="post in publicaciones"
              :key="post.id"
              class="p-4"
            >
              <PublicationItem :post="post" @abrir-modal="abrirModal(post)" />
            </div>
          </div>

          <!-- Paginación -->
          <div class="p-4 flex items-center justify-between" v-if="paginacion.totalPages > 1">
            <button
              class="px-3 py-1 rounded border disabled:opacity-50"
              :disabled="paginaActual <= 1"
              @click="cambiarPagina(paginaActual - 1)"
            >
              Anterior
            </button>
            <span class="text-sm text-gray-600">Página {{ paginaActual }} de {{ paginacion.totalPages }}</span>
            <button
              class="px-3 py-1 rounded border disabled:opacity-50"
              :disabled="paginaActual >= paginacion.totalPages"
              @click="cambiarPagina(paginaActual + 1)"
            >
              Siguiente
            </button>
          </div>
        </div>

        <!-- (Seguidores/Seguidos) — se activará con el backend de follows -->
        <!-- <FollowList :user-id="perfil.id" /> -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import PublicationItem from '@/components/PublicationItem.vue'
import { getProfileByUserId, getUserPublications } from '@/services/profileService'

const route = useRoute()

const cargando = ref(true)
const error = ref(null)
const perfil = ref(null)

const publicaciones = ref([])
const paginaActual = ref(1)
const paginacion = ref({ total: 0, totalPages: 1, from: 0, to: 0 })
const bloqueadoPublicaciones = ref(false)

const placeholderAvatar =
  'data:image/svg+xml;utf8,' +
  encodeURIComponent(
    `<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160"><rect width="100%" height="100%" fill="#f1f5f9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#94a3b8" font-family="Arial" font-size="14">Sin avatar</text></svg>`
  )

const roleLabel = computed(() => {
  if (!perfil.value?.role) return ''
  return perfil.value.role === 'trainer' ? 'Entrenador' : 'Atleta'
})

onMounted(() => cargarVista())
watch(() => route.params.user, () => cargarVista())

async function cargarVista() {
  try {
    cargando.value = true
    error.value = null
    bloqueadoPublicaciones.value = false
    publicaciones.value = []
    paginacion.value = { total: 0, totalPages: 1, from: 0, to: 0 }
    paginaActual.value = 1

    const userId = route.params.user
    if (!userId) throw new Error('Falta el parámetro :user en la ruta.')

    // 1) Perfil público (no requiere login)
    const p = await getProfileByUserId(userId)
    perfil.value = p

    // 2) Publicaciones (requiere login en tu backend: puede devolver 401)
    await cargarPublicaciones(p.id, paginaActual.value)
  } catch (e) {
    console.error(e)
    error.value = 'No se pudo cargar este perfil.'
  } finally {
    cargando.value = false
  }
}

async function cargarPublicaciones(userId, page) {
  try {
    const resp = await getUserPublications(userId, page)
    const items = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? resp : [])

    publicaciones.value = items.map(adaptarPublicacion)

    const meta = resp?.meta || {}
    paginacion.value = {
      total: meta.total ?? items.length,
      totalPages: meta.last_page ?? 1,
      from: meta.from ?? (items.length ? 1 : 0),
      to: meta.to ?? items.length,
    }
  } catch (e) {
    // Si no hay token o expira, tu backend devolverá 401
    if (e?.response?.status === 401) {
      bloqueadoPublicaciones.value = true
      return
    }
    throw e
  }
}

function cambiarPagina(p) {
  paginaActual.value = p
  cargarPublicaciones(perfil.value.id, p)
}

// Mapea lo que devuelve tu API a lo que espera PublicationItem
function adaptarPublicacion(apiItem) {
  return {
    id: apiItem.id,
    titulo: apiItem.title ?? apiItem.titulo ?? '',
    contenido: apiItem.content ?? apiItem.contenido ?? '',
    fecha: apiItem.created_at ?? apiItem.fecha ?? null,

    // usuario:
    nombreUsuario: perfil.value?.name ?? apiItem.user_name ?? '',
    fotoPerfil: perfil.value?.avatarUrl ?? apiItem.user_avatar_url ?? placeholderAvatar,
    deporte: apiItem.sport ? [apiItem.sport] : (perfil.value?.sport ? [perfil.value.sport] : []),

    // medios (ajusta según tu PublicationResource)
    mediaUrl: apiItem.media_url ?? apiItem.image_url ?? null,
    esVideo: apiItem.media_type === 'video' || /\.mp4$|\.webm$/i.test(apiItem.media_url || ''),
  }
}

function abrirModal(post) {
  // Si tu PublicationItem emite @abrir-modal, podrías manejar un modal aquí.
}
</script>
