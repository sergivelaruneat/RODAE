<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    <div class="max-w-3xl mx-auto p-4">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-bold">Editar rutina</h2>
          <button @click="goBack" class="text-sm px-3 py-1 rounded border hover:bg-gray-50">Volver</button>
        </div>

        <div v-if="loading" class="text-center text-gray-600">Cargando…</div>

        <div v-else-if="routine">
          <!-- Formulario -->
          <div class="space-y-4">
            <input v-model.trim="name" type="text" placeholder="Nombre de la rutina"
                   class="w-full px-4 py-2 border rounded" />
            <select v-model="sport" class="w-full px-4 py-2 border rounded bg-white">
              <option disabled value="">Selecciona un deporte relacionado</option>
              <option v-for="s in sports" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
          </div>

          <h3 class="mt-6 text-lg font-semibold">Ejercicios</h3>
          <table class="w-full mt-2 text-sm border">
            <thead class="bg-gray-100">
              <tr>
                <th class="border px-2 py-1">Ejercicio</th>
                <th class="border px-2 py-1">Descripción</th>
                <th class="border px-2 py-1">Series x Reps</th>
                <th class="border px-2 py-1">Descanso</th>
                <th class="border px-2 py-1">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(ej, index) in exercises" :key="ej.__key">
                <td class="border px-2 py-1"><input v-model.trim="ej.name" class="w-full border rounded px-1" /></td>
                <td class="border px-2 py-1"><input v-model.trim="ej.description" class="w-full border rounded px-1" /></td>
                <td class="border px-2 py-1"><input v-model.trim="ej.series_reps" class="w-full border rounded px-1" placeholder="p.ej. 4x8" /></td>
                <td class="border px-2 py-1"><input v-model.trim="ej.rest" class="w-full border rounded px-1" placeholder="p.ej. 90s" /></td>
                <td class="border px-2 py-1 text-center">
                  <button @click="removeExercise(index)" class="text-red-500 hover:underline text-xs">Eliminar</button>
                </td>
              </tr>
            </tbody>
          </table>

          <button @click="addExercise"
                  class="mt-4 px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
            Añadir ejercicio
          </button>

          <div class="mt-6 flex justify-end gap-2">
            <button type="button" class="px-4 py-2 rounded border hover:bg-gray-50" @click="goBack">Cancelar</button>
            <button type="button"
                    :disabled="!name || !sport || exercises.length === 0"
                    class="px-4 py-2 bg-gradient-to-r from-blue-900 to-blue-500 text-white rounded disabled:opacity-40"
                    @click="onSubmit">
              Guardar
            </button>
          </div>
        </div>

        <div v-else class="text-center text-gray-600">No encontrada.</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import { getRoutine, updateRoutine, getSports } from '@/services/routineService'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const routine = ref(null)
const sports  = ref([])

const name  = ref('')
const sport = ref('')

const exercises   = ref([])   // [{id?, name, description, series_reps, rest, position, __key}]
const deletedIds  = ref([])

const addExercise = () => {
  exercises.value.push({
    id: null, name: '', description: '', series_reps: '', rest: '',
    position: exercises.value.length + 1,
    __key: `tmp-${Date.now()}-${Math.random()}`
  })
}

const removeExercise = (index) => {
  const ex = exercises.value[index]
  if (ex.id) deletedIds.value.push(ex.id)
  exercises.value.splice(index, 1)
}

const hydrateForm = (data) => {
  name.value  = data.name
  sport.value = data.sport
  exercises.value = (data.exercises || []).map((e, i) => ({
    id: e.id ?? null,
    name: e.name ?? '',
    description: e.description ?? '',
    series_reps: e.series_reps ?? '',
    rest: e.rest ?? '',
    position: e.position ?? i + 1,
    __key: e.id ?? `tmp-${Date.now()}-${i}`,
  }))
}

const fetchData = async () => {
  loading.value = true
  try {
    const [sportsData, routineData] = await Promise.all([
      getSports().catch(() => []),
      getRoutine(route.params.id),
    ])
    sports.value  = sportsData
    routine.value = routineData
    hydrateForm(routineData)
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const onSubmit = async () => {
  if (!name.value.trim()) return alert('El nombre es obligatorio')
  if (!sport.value) return alert('Selecciona un deporte')
  if (exercises.value.length === 0) return alert('Añade al menos un ejercicio')
  if (exercises.value.some(e => !e.name?.trim() || !e.description?.trim()))
    return alert('Cada ejercicio debe tener nombre y descripción')

  const payload = {
    name: name.value.trim(),
    sport: sport.value,
    exercises: exercises.value.map((e, idx) => ({
      ...(e.id ? { id: e.id } : {}),
      name: e.name.trim(),
      description: e.description.trim(),
      series_reps: e.series_reps || null,
      rest: e.rest || null,
      position: idx + 1,
    })),
  }
  if (deletedIds.value.length) payload.delete_exercise_ids = deletedIds.value

  try {
    await updateRoutine(route.params.id, payload)
    router.push({ name: 'RoutineSelected', params: { id: route.params.id }, query: { refresh: Date.now() } })
  } catch (e) {
    console.error(e)
    alert('No se pudo actualizar la rutina.')
  }
}

const goBack = () => router.push({ name: 'RoutineSelected', params: { id: route.params.id } })

onMounted(fetchData)
</script>
