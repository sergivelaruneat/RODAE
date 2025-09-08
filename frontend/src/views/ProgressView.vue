<template>
  <div class="h-screen flex flex-col bg-gray-50">
    <Navbar />

    <div class="flex-1 p-4">
      <div class="max-w-6xl mx-auto">
        <!-- Cabecera -->
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Progreso de {{ nombreUsuario }}</h2>

          <button
            v-if="showAddButton"
            @click="openPickModal"
            class="px-4 py-2 text-sm font-semibold text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 shadow"
          >
            Añadir reporte de entrenamiento
          </button>
        </div>

        <!-- Grid 2x2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Columna izquierda -->
          <div class="grid grid-rows-2 gap-6">
            <!-- Calendario -->
            <div class="bg-white rounded-lg shadow p-4 flex flex-col justify-between h-[430px]">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold text-lg text-gray-700">Días entrenados</h3>
              </div>

              <div class="flex justify-center items-center flex-grow">
                <div class="w-[270px] h-[340px] overflow-hidden">
                  <Datepicker
                    v-model="dummyModel"
                    inline
                    :multi-dates="false"
                    :enable-time-picker="false"
                    :start-date="startDate"
                    @update-month-year="onMonthYearChange"
                    :day-class="dayClass"
                    auto-apply
                    class="w-full h-full"
                  />
                </div>
              </div>
            </div>

            <!-- Últimos reportes (TODOS, espacio fijo, scroll interno) -->
            <div class="bg-white rounded-lg shadow p-4 flex flex-col h-[280px]">
              <h3 class="font-semibold text-lg text-gray-700 mb-2">Últimos reportes</h3>
              <div class="flex-1 min-h-0 overflow-y-auto custom-scroll">
                <ul
                  v-if="Array.isArray(recentReports) && recentReports.length > 0"
                  class="divide-y divide-gray-200 m-0 p-0"
                >
                  <ReportCard
                    v-for="(r, idx) in recentReports"
                    :key="(r && r.id) || ('rep' + idx)"
                    :report="r"
                    :reporte="r"
                    @select="goReport"
                  />
                </ul>
                <p v-else class="text-sm text-gray-500">Aún no has creado reportes.</p>
              </div>
            </div>
          </div>

          <!-- Columna derecha -->
          <div class="grid grid-rows-2 gap-6">
            <!-- Últimas rutinas (máx 3, distintas, espacio fijo, scroll si hace falta) -->
            <div class="bg-white rounded-lg shadow p-4 flex flex-col h-[430px]">
              <h3 class="font-semibold text-lg text-gray-700 mb-2">Últimas rutinas realizadas</h3>
              <div class="flex-1 min-h-0 overflow-y-auto custom-scroll">
                <ul v-if="recentRoutinesTop3.length" class="space-y-3 m-0 p-0">
                  <RoutineCard
                    v-for="(rutina, idx) in recentRoutinesTop3"
                    :key="rutina.id ?? ('rut' + idx)"
                    :routine="rutina"
                    @select="goRoutine"
                  />
                </ul>
                <p v-else class="text-sm text-gray-500">Aún no tienes rutinas realizadas.</p>
              </div>
            </div>

            <!-- Gráfica -->
            <div class="bg-white rounded-lg shadow p-4 flex flex-col justify-between h-[280px]">
              <h3 class="font-semibold text-lg text-gray-700 mb-2">Deportes más practicados</h3>
              <div class="h-56">
                <RadialChart :deportes="deportesRealizados" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: elegir rutina (las que SIGUES) -->
    <div
      v-if="pickOpen"
      class="fixed inset-0 backdrop-blur-sm bg-gray-800/20 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Selecciona una rutina que sigues</h3>
          <button class="text-xl font-bold hover:text-red-500" @click="pickOpen = false">×</button>
        </div>

        <div v-if="pickLoading" class="py-8 text-center text-gray-600">Cargando…</div>
        <div v-else-if="pickError" class="py-4 text-red-600">{{ pickError }}</div>

        <template v-else>
          <div v-if="followedRoutines.length === 0" class="text-sm text-gray-500">
            No sigues ninguna rutina. Ve a “Rutinas” para seguir alguna.
          </div>

          <ul v-else class="divide-y">
            <li v-for="(r, idx) in followedRoutines" :key="r?.id ?? ('fol' + idx)" class="py-3 flex items-center justify-between">
              <div class="min-w-0">
                <p class="font-medium truncate">{{ r.name }}</p>
                <p class="text-xs text-gray-500 truncate">
                  {{ r.sport_label || r.sport }} · {{ r.exercises_count ?? 0 }} ejercicios
                </p>
              </div>
              <button
                class="px-3 py-1.5 text-sm font-semibold text-white rounded bg-gradient-to-r from-indigo-900 to-indigo-500 hover:opacity-90 disabled:opacity-60"
                :disabled="creatingReport"
                @click="createFromRoutine(r.id)"
              >
                {{ creatingReport ? 'Creando…' : 'Usar' }}
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
import { useRouter, useRoute } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import RoutineCard from '@/components/RoutineCard.vue'
import ReportCard from '@/components/ReportCard.vue'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import RadialChart from '@/components/RadialChart.vue'

// reportes (paneles + calendario + breakdown)
import {
  getCalendar,
  getRecentRoutines,
  getRecentReports,
  getSportBreakdown,
} from '@/services/reportService'

// rutas para cargar rutinas seguidas del usuario autenticado
import { getMyRoutines } from '@/services/routineService'

const router = useRouter()
const route  = useRoute()

// usuario autenticado
const currentUser = (() => { try { return JSON.parse(localStorage.getItem('user') || 'null') } catch { return null } })()

// id del usuario cuyo progreso se está viendo (si no hay param, eres tú)
const viewedUserId = computed(() =>
  route.params.userId ? Number(route.params.userId) : currentUser?.id
)

// nombre para la cabecera (si viene por query desde OtherProfile, úsalo)
const nombreUsuario = computed(() =>
  route.query.name
    ?? (viewedUserId.value === currentUser?.id ? (currentUser?.name || 'Mi perfil') : 'Progreso')
)

// botón “Añadir reporte” solo si es tu propio progreso
const showAddButton = computed(() => viewedUserId.value === currentUser?.id)

/* ====== calendario ====== */
const visibleYear  = ref(new Date().getFullYear())
const visibleMonth = ref(new Date().getMonth() + 1) // 1..12
const startDate = computed(() => new Date(visibleYear.value, visibleMonth.value - 1, 1))

// modelo ficticio (no seleccionamos días)
const dummyModel = ref(null)

// días con reporte como 'YYYY-MM-DD'
const fechasEntrenadas = ref([])
// set para lookup O(1)
const entrenadasSet = computed(() => new Set(fechasEntrenadas.value))

function onMonthYearChange({ month, year }) {
  visibleYear.value = year
  visibleMonth.value = month + 1
}

function dayClass(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  const key = `${y}-${m}-${d}`
  return entrenadasSet.value.has(key) ? 'dp__active_date' : ''
}

const cargarCalendario = async () => {
  try {
    const rows = await getCalendar({
      year: visibleYear.value,
      month: visibleMonth.value,
      user_id: viewedUserId.value
    })
    const list = Array.isArray(rows) ? rows : []
    fechasEntrenadas.value = list.map(r => r.date).filter(Boolean)
  } catch (e) {
    console.error('No se pudo cargar el calendario', e)
    fechasEntrenadas.value = []
  }
}

/* ====== datos paneles ====== */
const recentRoutines = ref([])
const recentReports  = ref([])
const deportesRealizados = ref({})

// Top 3 rutinas distintas
const recentRoutinesTop3 = computed(() => {
  const arr = Array.isArray(recentRoutines.value) ? recentRoutines.value : []
  const seen = new Set()
  const uniq = []
  for (const r of arr) {
    const id = r?.id
    if (id != null && !seen.has(id)) {
      seen.add(id)
      uniq.push(r)
      if (uniq.length === 3) break
    }
  }
  return uniq
})

const cargarRoutinesYReports = async () => {
  try {
    // NOTA: estas funciones deben aceptar 2º parámetro opcional con params extra.
    // getRecentRoutines(3, { user_id }), getRecentReports(1000, { user_id })
    const [r1, r2] = await Promise.all([
      getRecentRoutines(3,    { user_id: viewedUserId.value }),
      getRecentReports(1000,  { user_id: viewedUserId.value })
    ])
    recentRoutines.value = Array.isArray(r1) ? r1 : (r1?.data ?? [])
    recentReports.value  = Array.isArray(r2) ? r2 : (r2?.data ?? [])
  } catch (e) {
    console.error('No se pudo cargar rutinas/reportes recientes', e)
    recentRoutines.value = []
    recentReports.value = []
  }
}

const cargarBreakdown = async () => {
  try {
    // histórico (sin rango), filtrado por usuario visto
    const rows = await getSportBreakdown({ user_id: viewedUserId.value })
    const obj = {}
    for (const r of rows || []) {
      obj[r.label || r.sport || 'Otro'] = r.count ?? 0
    }
    deportesRealizados.value = obj
  } catch (e) {
    console.error('No se pudo cargar el breakdown de deportes', e)
    deportesRealizados.value = {}
  }
}

// recargar al cambiar mes visible
watch([visibleYear, visibleMonth], () => {
  cargarCalendario()
  cargarBreakdown()
})

// si cambia el usuario visto (navegación entre perfiles), recargamos todo
watch(viewedUserId, () => {
  cargarCalendario()
  cargarRoutinesYReports()
  cargarBreakdown()
})

/* ====== modal: seleccionar rutina seguida y CREAR reporte ====== */
const pickOpen = ref(false)
const pickLoading = ref(false)
const pickError = ref('')
const followedRoutines = ref([])
const creatingReport = ref(false)

async function openPickModal () {
  pickOpen.value = true
  pickLoading.value = true
  pickError.value = ''
  followedRoutines.value = []

  try {
    const list = await getMyRoutines()
    followedRoutines.value = Array.isArray(list) ? list : (Array.isArray(list?.data) ? list.data : [])
  } catch (e) {
    console.error('No se pudieron cargar tus rutinas seguidas', e)
    pickError.value = 'No se pudieron cargar tus rutinas seguidas.'
  } finally {
    pickLoading.value = false
  }
}

async function createFromRoutine (routineId) {
  try {
    creatingReport.value = true
    pickOpen.value = false
    router.push({ name: 'ReportCreate', params: { routineId } })
  } catch (e) {
    console.error(e)
    alert('No se pudo abrir la creación del reporte.')
  } finally {
    creatingReport.value = false
  }
}

/* ====== navegación ====== */
const goRoutine = (id) => router.push({ name: 'RoutineSelected', params: { id } })
const goReport  = (id) => router.push({ name: 'ReportView', params: { id } })

onMounted(async () => {
  await Promise.all([
    cargarCalendario(),
    cargarRoutinesYReports(),
    cargarBreakdown(),
  ])
})
</script>
