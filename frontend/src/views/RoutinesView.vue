<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />

    <div class="max-w-5xl mx-auto p-4">
      <div class="flex items-center justify-between mb-3">
        <h1 class="text-lg sm:text-xl font-semibold">Rutinas</h1>
        <button
          v-if="isTrainer"
          @click="goCreate"
          class="px-3 py-1.5 text-sm font-semibold text-white rounded bg-blue-600 hover:bg-blue-700"
        >
          Añadir rutina
        </button>
      </div>

      <div class="mb-3">
        <input
          v-model="search"
          type="text"
          class="w-full px-3 py-2 border rounded focus:outline-none focus:ring"
          placeholder="Buscar por nombre de rutina o creador…"
          @input="onSearchInput"
        />
      </div>

      <!-- Estado vacío SOLO si eres trainer y no hay ninguna rutina y no estás buscando -->
      <div
        v-if="isTrainer && !loading && routines.length === 0 && search.trim() === ''"
        class="bg-white rounded border p-8 text-center text-gray-700"
      >
        <p class="text-base font-semibold mb-2">¡Empecemos a trabajar!</p>
        <p class="text-sm mb-4">Aún no hay rutinas en la aplicación.</p>
        <button
          @click="goCreate"
          class="px-3 py-1.5 text-sm font-semibold text-white rounded bg-blue-600 hover:bg-blue-700"
        >
          Crear rutina
        </button>
      </div>

      <div class="bg-white rounded border" v-if="!loading && routines.length">
        <ul class="divide-y divide-gray-200">
          <RoutineCard
            v-for="r in routines"
            :key="r.id"
            :routine="r"
            @select="openRoutine"
          />
        </ul>
      </div>

      <div v-if="loading" class="bg-white rounded border p-6 text-center text-gray-600">
        Cargando rutinas…
      </div>

      <div v-if="!loading && routines.length === 0 && search.trim() !== ''"
           class="bg-white rounded border p-6 text-center text-gray-600">
        No se encontraron rutinas para “{{ search }}”.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import RoutineCard from '@/components/RoutineCard.vue'
import { getAllRoutines } from '@/services/routineService'

// rol desde localStorage (ajusta si tienes otro store)
function getStoredRole () {
  try { return JSON.parse(localStorage.getItem('user') || 'null')?.role ?? null }
  catch { return null }
}

const router = useRouter()
const route = useRoute()

const search = ref('')
const routines = ref([])
const loading = ref(false)

const role = getStoredRole()
// Ya no hay 'admin': solo 'trainer'
const isTrainer = computed(() => role === 'trainer')

const fetchRoutines = async () => {
  loading.value = true
  try {
    const params = {}
    const q = search.value.trim()
    if (q) params.q = q
    routines.value = await getAllRoutines(params)  // /routines -> r.data.data
  } catch (e) {
    console.error(e)
    routines.value = []
  } finally {
    loading.value = false
  }
}

let t = null
const onSearchInput = () => {
  clearTimeout(t)
  t = setTimeout(fetchRoutines, 250)
}

onMounted(fetchRoutines)
// refresco al volver de crear con ?refresh=timestamp
watch(() => route.query.refresh, fetchRoutines)

const openRoutine = (id) => router.push({ name: 'RoutineSelected', params: { id } })
const goCreate = () => router.push('/routine/create')
</script>
