<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />

    <div class="max-w-2xl mx-auto p-4">
      <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h1 class="text-xl font-bold mb-4">Editar perfil</h1>

        <!-- Cargando -->
        <div v-if="cargando" class="text-gray-500">Cargando…</div>

        <form v-else @submit.prevent="guardar">
          <!-- Avatar -->
          <div class="mb-6">
            <span class="block text-sm font-medium text-gray-700 mb-2">Avatar</span>

            <div class="flex items-center gap-4">
              <img
                :src="avatarPreview || perfil.avatarUrl || placeholderAvatar"
                class="w-20 h-20 rounded-full object-cover border"
                alt="avatar"
              />

              <div class="flex flex-col gap-2">
                <button
                  type="button"
                  class="px-3 py-2 text-sm rounded bg-gray-100 hover:bg-gray-200"
                  @click="() => $refs.inputAvatar.click()"
                >
                  Cambiar avatar
                </button>
                <input
                  ref="inputAvatar"
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="onAvatarChange"
                />
                <p class="text-xs text-gray-500" v-if="avatarFile">
                  {{ avatarFile.name }} ({{ prettySize(avatarFile.size) }})
                </p>
                <p v-if="errors.avatar" class="text-xs text-red-600">{{ errors.avatar }}</p>
              </div>
            </div>
          </div>

          <!-- Name + Email -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-3 py-2 border rounded"
                maxlength="120"
              />
              <p v-if="errors.name" class="text-xs text-red-600 mt-1">{{ errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input
                :value="perfil.email"
                type="email"
                class="w-full px-3 py-2 border rounded bg-gray-50 text-gray-500"
                disabled
              />
            </div>
          </div>

          <!-- Birthdate -->
          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento</label>
            <input
              v-model="form.birthdate"
              type="date"
              class="w-full px-3 py-2 border rounded"
            />
            <p v-if="errors.birthdate" class="text-xs text-red-600 mt-1">{{ errors.birthdate }}</p>
          </div>

          <!-- Sport -->
          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Deporte principal</label>
            <input
              v-model="form.sport"
              type="text"
              class="w-full px-3 py-2 border rounded"
              maxlength="80"
              placeholder="Ej: Powerlifting"
            />
            <p v-if="errors.sport" class="text-xs text-red-600 mt-1">{{ errors.sport }}</p>
          </div>

          <!-- Bio -->
          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea
              v-model="form.bio"
              rows="4"
              class="w-full px-3 py-2 border rounded"
              maxlength="2000"
              placeholder="Cuéntanos algo sobre ti…"
            ></textarea>
            <div class="flex justify-between">
              <p v-if="errors.bio" class="text-xs text-red-600 mt-1">{{ errors.bio }}</p>
              <p class="text-xs text-gray-500 mt-1 ml-auto">{{ (form.bio || '').length }}/2000</p>
            </div>
          </div>

          <!-- Acciones -->
          <div class="mt-6 flex items-center justify-end gap-3">
            <button
              type="button"
              class="px-4 py-2 rounded border hover:bg-gray-50"
              @click="cancelar"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 disabled:opacity-60"
              :disabled="guardando"
            >
              {{ guardando ? 'Guardando…' : 'Guardar cambios' }}
            </button>
          </div>

          <!-- Error global -->
          <p v-if="errorGlobal" class="text-sm text-red-600 mt-3">{{ errorGlobal }}</p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import { getMyProfile, updateMyProfile } from '@/services/profileService'

const router = useRouter()

const cargando = ref(true)
const guardando = ref(false)
const errorGlobal = ref('')
const errors = reactive({})

// Perfil actual para placeholders y email
const perfil = reactive({
  avatarUrl: null,
  email: '',
})

// Form reactivo
const form = reactive({
  name: '',
  bio: '',
  sport: '',
  birthdate: '', // YYYY-MM-DD
})

// Avatar
const avatarFile = ref(null)
const avatarPreview = ref('')
const inputAvatar = ref(null)

const placeholderAvatar =
  'data:image/svg+xml;utf8,' +
  encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160"><rect width="100%" height="100%" fill="#f1f5f9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#94a3b8" font-family="Arial" font-size="14">Sin avatar</text></svg>`)

onMounted(async () => {
  await cargarPerfil()
})

async function cargarPerfil () {
  try {
    cargando.value = true
    clearErrors()
    const p = await getMyProfile() // <- viene “desenvuelto”: { id, name, email, role, bio, sport, birthdate, avatarUrl, age }
    perfil.avatarUrl = p.avatarUrl || null
    perfil.email     = p.email || ''

    form.name       = p.name || ''
    form.role       = p.role || 'athlete'
    form.bio        = p.bio || ''
    form.sport      = p.sport || ''
    form.birthdate  = p.birthdate || '' // formato YYYY-MM-DD; tu Request acepta dd/mm/yyyy también
  } catch (e) {
    errorGlobal.value = 'No se pudo cargar el perfil.'
    console.error(e)
  } finally {
    cargando.value = false
  }
}

function onAvatarChange (e) {
  clearFieldError('avatar')
  errorGlobal.value = ''
  const f = e.target.files?.[0]
  if (!f) { avatarFile.value = null; avatarPreview.value = ''; return }

  // Validación cliente = 4 MB igual que tu Request (max:4096)
  const MAX_MB = 4
  if (!/^image\//i.test(f.type)) {
    errors.avatar = 'El avatar debe ser una imagen válida.'
    avatarFile.value = null
    avatarPreview.value = ''
    return
  }
  if (f.size > MAX_MB * 1024 * 1024) {
    errors.avatar = `El avatar no puede superar los ${MAX_MB} MB.`
    avatarFile.value = null
    avatarPreview.value = ''
    return
  }

  avatarFile.value = f
  const url = URL.createObjectURL(f)
  avatarPreview.value = url
}

function prettySize (bytes) {
  return (bytes / 1024 / 1024).toFixed(2) + ' MB'
}

function clearErrors () {
  errorGlobal.value = ''
  for (const k of Object.keys(errors)) delete errors[k]
}
function clearFieldError (k) { if (errors[k]) delete errors[k] }

async function guardar () {
  try {
    guardando.value = true
    clearErrors()

    const fd = new FormData()
    if (form.name)      fd.append('name', form.name)
    if (form.bio !== '')   fd.append('bio', form.bio)
    if (form.sport !== '') fd.append('sport', form.sport)
    if (form.birthdate) fd.append('birthdate', form.birthdate ?? '')    // YYYY-MM-DD o dd/mm/yyyy aceptado por tu Request
    if (avatarFile.value) fd.append('avatar', avatarFile.value)
    if (avatarFile.value) fd.append('avatar', avatarFile.value)

    const resp = await updateMyProfile(fd) // POST /profile?_method=PUT en el service
    // Podemos redirigir al perfil
    router.push('/profile')
  } catch (e) {
    const api = e?.response?.data
    if (api?.errors) {
      // Copia errores field -> mensaje
      for (const [k, msgs] of Object.entries(api.errors)) {
        errors[k] = Array.isArray(msgs) ? msgs[0] : String(msgs)
      }
    } else if (api?.message) {
      errorGlobal.value = api.message
    } else {
      errorGlobal.value = 'No se pudieron guardar los cambios.'
    }
    console.error(e)
  } finally {
    guardando.value = false
  }
}

function cancelar () {
  router.back()
}
</script>
