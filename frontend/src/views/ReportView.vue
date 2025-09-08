<template>
  <div class="min-h-screen bg-gray-50">
    <div class="sticky top-0 z-50">
      <Navbar />
    </div>

    <div class="max-w-6xl mx-auto p-6">
      <!-- Cabecera -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">
          {{ routineName }}
        </h2>

        <!-- Botón Editar solo en modo ver y si soy dueño del reporte -->
        <button
          v-if="mode === 'view' && isOwner"
          @click="toEditMode"
          class="px-4 py-2 text-sm font-semibold text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 shadow"
        >
          Editar
        </button>
      </div>

      <!-- Subcabecera -->
      <div v-if="!loading && report" class="mb-4">
        <p class="text-sm text-gray-600">
          Creado por
          <span class="text-blue-700 font-medium">{{ ownerName }}</span>
          · {{ sportLabel }}
        </p>
      </div>

      <!-- Cargando / No encontrado -->
      <div v-if="loading" class="bg-white rounded border p-6 text-center text-gray-600">
        Cargando…
      </div>
      <div v-else-if="!report" class="bg-white rounded border p-6 text-center text-gray-600">
        No se encontró el contenido.
      </div>

      <!-- Contenido -->
      <template v-else>
        <div class="overflow-x-auto">
          <table class="w-full table-auto border rounded overflow-hidden text-sm">
            <thead class="bg-blue-100 text-gray-700">
              <tr>
                <th class="p-2 text-left">Ejercicio</th>
                <th class="p-2 text-left">Descanso</th>
                <th class="p-2 text-left">Series x Repeticiones</th>
                <th class="p-2 text-left">Dificultad</th>
                <th class="p-2 text-left">Peso / Vel. / Dist.</th>
                <th class="p-2 text-center">Completado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in rows" :key="row.localKey" class="border-t">
                <td class="p-2 font-semibold">{{ row.name }}</td>
                <td class="p-2 text-gray-600">{{ row.restText }}</td>
                <td class="p-2 text-gray-600">{{ row.seriesReps }}</td>

                <td class="p-2">
                  <select
                    v-if="mode !== 'view'"
                    v-model.number="row.difficulty"
                    class="border rounded px-2 py-1"
                  >
                    <option v-for="n in 10" :key="n" :value="n">{{ n }}</option>
                  </select>
                  <span v-else>{{ row.difficulty ?? 1 }}</span>
                </td>

                <td class="p-2">
                  <input
                    v-if="mode !== 'view'"
                    type="text"
                    v-model="row.metric"
                    placeholder="kg, km/h, m"
                    class="border rounded px-2 py-1 w-full"
                  />
                  <span v-else>{{ row.metric || '—' }}</span>
                </td>

                <td class="p-2 text-center">
                  <input v-if="mode !== 'view'" type="checkbox" v-model="row.completed" />
                  <span v-else>{{ row.completed ? '✔' : '' }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Botonera inferior -->
        <div class="flex justify-between items-center mt-6">
          <button
            @click="goBack"
            class="px-4 py-2 text-sm text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 shadow"
          >
            ← Volver
          </button>

          <!-- Crear -->
          <div v-if="mode === 'create'" class="flex gap-2">
            <button
              @click="goBack"
              class="px-4 py-2 text-sm text-white rounded bg-gradient-to-r from-red-700 to-red-400 hover:opacity-90 shadow"
              :disabled="saving"
            >
              Cancelar
            </button>
            <button
              @click="guardarCambios"
              class="px-4 py-2 text-sm text-white rounded bg-gradient-to-r from-green-600 to-green-400 hover:opacity-90 shadow"
              :disabled="saving"
            >
              {{ saving ? 'Creando…' : 'Crear reporte' }}
            </button>
          </div>

          <!-- Editar -->
          <div v-else-if="mode === 'edit'" class="flex gap-2">
            <button
              @click="toViewMode()"
              class="px-4 py-2 text-sm text-white rounded bg-gradient-to-r from-red-700 to-red-400 hover:opacity-90 shadow"
              :disabled="saving"
            >
              Cancelar cambios
            </button>
            <button
              @click="guardarCambios"
              class="px-4 py-2 text-sm text-white rounded bg-gradient-to-r from-green-600 to-green-400 hover:opacity-90 shadow"
              :disabled="saving"
            >
              {{ saving ? 'Guardando…' : 'Guardar cambios' }}
            </button>
          </div>

          <div v-else></div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import { getReport, updateReport, createReport } from '@/services/reportService'
import { getRoutine } from '@/services/routineService'

/* ----------------- routing / modo ----------------- */
const route = useRoute()
const router = useRouter()

const mode = computed(() => {
  if (route.name === 'ReportCreate' || route.query.modo === 'crear') return 'create'
  if (route.name === 'ReportEdit'   || route.query.modo === 'editar') return 'edit'
  return 'view'
})

const reportId  = computed(() => route.params.id)
const routineId = computed(() => route.params.routineId ?? route.query.routineId)

/* ----------------- estado ----------------- */
const report = ref(null)
const rows = reactive([])
const loading = ref(true)
const saving = ref(false)

// usuario actual
const currentUser = ref(null)
try { currentUser.value = JSON.parse(localStorage.getItem('user') || 'null') } catch { currentUser.value = null }
// ¿soy el dueño del reporte?
const isOwner = computed(() => !!(report.value && currentUser.value && report.value.user_id === currentUser.value.id))

/* ----------------- cabecera ----------------- */
const routineName = computed(() =>
  mode.value === 'create'
    ? (report.value?.routine?.name || 'Nuevo reporte')
    : (report.value?.routine?.name || 'Reporte')
)
const ownerName = computed(() => report.value?.routine?.owner?.name || '—')
const sportLabel = computed(() => report.value?.routine?.sport_label || report.value?.routine?.sport || '—')

/* ----------------- helpers (back manda texto) ----------------- */
const restText = (t) => (t && String(t).trim() !== '' ? String(t) : '—')
const seriesRepsText = (t) => (t && String(t).trim() !== '' ? String(t) : '—')

/* ----------------- construcción de filas ----------------- */
function buildRowsFromReport(data) {
  rows.splice(0)
  const ordered = [...(data.items || [])].sort(
    (a, b) =>
      ((a.position ?? a.routine_exercise?.position ?? 0) -
       (b.position ?? b.routine_exercise?.position ?? 0))
  )

  ordered.forEach((it, idx) => {
    const ex   = it.routine_exercise || {}
    const name = it.exercise_name ?? ex.name ?? '—'
    const rest = it.rest ?? ex.rest ?? null
    const sr   = it.series_reps ?? ex.series_reps ?? null

    rows.push({
      localKey: `${it.id ?? 'new'}-${idx}`,
      id: it.id ?? null,
      routine_exercise_id: it.routine_exercise_id ?? ex.id,
      name,
      restText: restText(rest),
      seriesReps: seriesRepsText(sr),
      difficulty: it.difficulty ?? 1,
      metric: it.metric ?? '',
      completed: !!it.completed,
    })
  })
}


function buildRowsFromRoutine(rutina) {
  rows.splice(0)

  let list = []

  if (Array.isArray(rutina?.routine_exercises)) {
    list = rutina.routine_exercises.map(re => ({
      id: re.id,
      position: re.position,
      name: re.exercise?.name ?? re.name,
      rest: re.rest,
      series_reps: re.series_reps,
    }))
  } else if (Array.isArray(rutina?.exercises)) {
    list = rutina.exercises.map(e => ({
      id: e?.pivot?.id ?? e.routine_exercise_id ?? e.id,
      position: e?.pivot?.position ?? e.position,
      name: e.name,
      rest: e.rest ?? e?.pivot?.rest ?? null,
      series_reps: e.series_reps ?? e?.pivot?.series_reps ?? null,
    }))
  }

  const ordered = [...list].sort((a, b) => (a.position ?? 0) - (b.position ?? 0))
  ordered.forEach((re, idx) => {
    rows.push({
      localKey: `new-${idx}`,
      id: null,
      routine_exercise_id: re.id,
      name: re.name || 'Ejercicio',
      restText: restText(re.rest),
      seriesReps: seriesRepsText(re.series_reps),
      difficulty: 1,
      metric: '',
      completed: false,
    })
  })
}

/* ----------------- carga según modo ----------------- */
async function load() {
  loading.value = true
  try {
    if (mode.value === 'create') {
      if (!routineId.value) {
        router.push({ name: 'Progress' })
        return
      }
      const rutina = await getRoutine(routineId.value)
      report.value = { routine: rutina } // para cabecera
      buildRowsFromRoutine(rutina)
    } else {
      const data = await getReport(reportId.value)
      report.value = data
      buildRowsFromReport(data)
    }
  } catch (e) {
    console.error('Error al cargar', e)
    report.value = null
  } finally {
    loading.value = false
  }
}

/* ----------------- acciones navegación ----------------- */
function toEditMode() {
  if (route.matched.some(r => r.name === 'ReportEdit')) {
    router.push({ name: 'ReportEdit', params: { id: reportId.value } })
  } else {
    router.replace({ name: 'ReportView', params: { id: reportId.value }, query: { modo: 'editar' } })
  }
}
function toViewMode(id = reportId.value) {
  router.replace({ name: 'ReportView', params: { id }, query: {} })
}
function goBack() {
  // Si venimos navegando desde progreso de otro, esto respeta el historial
  if (window.history.length > 1) {
    router.back()
    return
  }
  // Fallback explícito
  if (!isOwner.value && report.value?.user_id) {
    const q = {}
    if (route.query?.name) q.name = route.query.name
    router.push({ name: 'Progress', params: { user: report.value.user_id }, query: q })
  } else {
    router.push({ name: 'Progress' })
  }
}

/* ----------------- guardar ----------------- */
async function guardarCambios() {
  if (mode.value === 'create') {
    await crearReporte()
  } else {
    await actualizarReporte()
  }
}

async function crearReporte() {
  if (!report.value?.routine) return
  saving.value = true
  try {
    const payload = {
      routine_id: report.value.routine.id,
      items: rows.map(r => ({
        routine_exercise_id: r.routine_exercise_id,
        difficulty: r.difficulty ?? 1,
        metric: r.metric ?? '',
        completed: !!r.completed,
      })),
    }
    const created = await createReport(payload)
    const id = created?.id ?? created?.data?.id
    toViewMode(id)
  } catch (e) {
    console.error('No se pudo crear el reporte', e)
    const msg =
      e?.response?.data?.message ||
      Object.values(e?.response?.data?.errors ?? {})[0]?.[0] ||
      'No se pudo crear el reporte.'
    alert(msg)
  } finally {
    saving.value = false
  }
}

async function actualizarReporte() {
  if (!report.value) return
  saving.value = true
  try {
    const payload = {
      items: rows.map(r => ({
        id: r.id ?? undefined,
        routine_exercise_id: r.routine_exercise_id,
        difficulty: r.difficulty ?? 1,
        metric: r.metric ?? '',
        completed: !!r.completed,
      })),
    }
    await updateReport(report.value.id, payload)
    toViewMode(report.value.id)
  } catch (e) {
    console.error('No se pudieron guardar los cambios', e)
    alert('No se pudieron guardar los cambios del reporte.')
  } finally {
    saving.value = false
  }
}

/* ----------------- montar / reaccionar ----------------- */
onMounted(load)
watch(() => route.fullPath, load)
</script>
