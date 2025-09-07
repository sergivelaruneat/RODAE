<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />

    <div class="max-w-4xl mx-auto p-4">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">{{ routine?.name || 'Rutina' }}</h1>
        <router-link
          to="/routines"
          class="text-sm px-3 py-1 rounded border hover:bg-gray-50"
        >
          Volver
        </router-link>
      </div>

      <div v-if="loading" class="bg-white rounded border p-6 text-center text-gray-600">
        Cargando…
      </div>

      <div v-else-if="routine" class="bg-white rounded border p-6">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-600">
            <span class="font-medium">{{ routine.owner?.name ?? '—' }}</span>
            <span class="mx-2 text-gray-400">·</span>
            <span>{{ routine.sport_label || routine.sport || '—' }}</span>
            <span class="mx-2 text-gray-400">·</span>
            <span>{{ routine.exercises_count ?? 0 }} ejercicios</span>
          </div>

          <div class="flex items-center gap-2">
            <!-- Botón editar SOLO si soy trainer y propietario -->
            <button
              v-if="isTrainerOwner"
              @click="goEdit"
              class="px-3 py-1 text-sm rounded border hover:bg-gray-50"
            >
              Editar rutina
            </button>

            <button
              v-if="!following"
              @click="onFollow"
              class="px-3 py-1 text-sm text-white rounded bg-blue-600 hover:bg-blue-700"
            >Seguir</button>
            <button
              v-else
              @click="onUnfollow"
              class="px-3 py-1 text-sm rounded border hover:bg-gray-50"
            >Dejar de seguir</button>

            <span class="text-sm text-yellow-700">
              ★ {{ Number(routine.rating_avg ?? 0).toFixed(1) }}
            </span>
          </div>
        </div>

        <h3 class="mt-6 font-semibold">Ejercicios</h3>
        <table class="w-full mt-2 text-sm border">
          <thead class="bg-gray-100">
            <tr>
              <th class="border px-2 py-1 text-left">#</th>
              <th class="border px-2 py-1 text-left">Ejercicio</th>
              <th class="border px-2 py-1 text-left">Descripción</th>
              <th class="border px-2 py-1 text-left">Series x Reps</th>
              <th class="border px-2 py-1 text-left">Descanso</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(ex, i) in routine.exercises" :key="ex.id ?? i">
              <td class="border px-2 py-1">{{ ex.position }}</td>
              <td class="border px-2 py-1">{{ ex.name }}</td>
              <td class="border px-2 py-1">{{ ex.description }}</td>
              <td class="border px-2 py-1">{{ ex.series_reps || '—' }}</td>
              <td class="border px-2 py-1">{{ ex.rest || '—' }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Valoración (si sigo la rutina) -->
        <div v-if="following" class="mt-4">
          <label class="text-sm text-gray-700 mr-2">Valorar:</label>
          <select v-model.number="myRating" class="border rounded px-2 py-1 text-sm">
            <option :value="n" v-for="n in 5" :key="n">{{ n }}</option>
          </select>
          <button @click="onRate" class="ml-2 px-3 py-1 text-sm rounded border hover:bg-gray-50">Guardar</button>
          <button @click="onUnrate" class="ml-1 px-3 py-1 text-sm rounded border hover:bg-gray-50">Quitar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import { getRoutine, followRoutine, unfollowRoutine, rateRoutine, unrateRoutine } from '@/services/routineService'

const route = useRoute()
const router = useRouter()

const routine   = ref(null)
const loading   = ref(false)
const following = ref(false)
const myRating  = ref(5)

// usuario logueado desde storage
function getStoredUser () {
  try { return JSON.parse(localStorage.getItem('user') || 'null') }
  catch { return null }
}
const currentUser = getStoredUser()

// soy trainer y además propietario de la rutina
const isTrainerOwner = computed(() =>
  currentUser?.role === 'trainer' && routine.value?.owner_user_id === currentUser?.id
)

const goEdit = () => {
  router.push({ name: 'RoutineEdit', params: { id: routine.value.id } })
}

const fetchRoutine = async () => {
  loading.value = true
  try {
    const data = await getRoutine(route.params.id)
    routine.value = data
    // preferimos is_following; si no llega, caemos a user_rating
    following.value = (data.is_following !== undefined) ? !!data.is_following : !!data.user_rating
    if (data.user_rating != null) myRating.value = data.user_rating
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const onFollow = async () => {
  try {
    await followRoutine(routine.value.id)
    following.value = true
    if (routine.value) routine.value.is_following = true
  } catch (e) { console.error(e) }
}

const onUnfollow = async () => {
  try {
    await unfollowRoutine(routine.value.id)
    following.value = false
    if (routine.value) {
      routine.value.is_following = false
      routine.value.user_rating = null
    }
  } catch (e) { console.error(e) }
}

const onRate = async () => {
  try {
    const resp = await rateRoutine(routine.value.id, myRating.value)
    if (routine.value) {
      routine.value.rating_avg = resp?.rating_avg ?? routine.value.rating_avg
      routine.value.user_rating = myRating.value
    }
  } catch (e) { console.error(e) }
}

const onUnrate = async () => {
  try {
    const resp = await unrateRoutine(routine.value.id)
    if (routine.value) {
      routine.value.rating_avg = resp?.rating_avg ?? routine.value.rating_avg
      routine.value.user_rating = null
    }
  } catch (e) { console.error(e) }
}

onMounted(fetchRoutine)
// si vuelves desde editar con ?refresh=..., recarga
watch(() => route.query.refresh, (v) => { if (v) fetchRoutine() })
</script>

