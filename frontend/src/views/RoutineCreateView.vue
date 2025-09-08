<template>
  <div class="h-screen flex flex-col bg-gray-50">
    <Navbar />
    <div class="flex-1 overflow-y-auto custom-scroll p-4">
      <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-bold">Crear nueva rutina</h2>
          <router-link
            to="/routines"
            class="flex items-center gap-1 text-sm border px-3 py-1 rounded hover:bg-gray-50"
          >
            Volver
          </router-link>
        </div>

        <!-- Formulario general -->
        <div class="space-y-4">
          <input v-model.trim="nombre" type="text" placeholder="Nombre de la rutina" class="w-full px-4 py-2 border rounded" />
          <select v-model="deporte" class="w-full px-4 py-2 border rounded bg-white">
            <option disabled value="">Selecciona un deporte relacionado</option>
            <option v-for="s in sports" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>

        <!-- Tabla de ejercicios -->
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
            <tr v-for="(ej, index) in ejercicios" :key="index">
              <td class="border px-2 py-1"><input v-model.trim="ej.nombre" class="w-full border rounded px-1" /></td>
              <td class="border px-2 py-1"><input v-model.trim="ej.descripcion" class="w-full border rounded px-1" /></td>
              <td class="border px-2 py-1"><input v-model.trim="ej.seriesReps" placeholder="p.ej. 4x8" class="w-full border rounded px-1" /></td>
              <td class="border px-2 py-1"><input v-model.trim="ej.descanso" placeholder="p.ej. 90s o 2-3 min" class="w-full border rounded px-1" /></td>
              <td class="border px-2 py-1 text-center">
                <button @click="eliminarEjercicio(index)" class="text-red-500 hover:underline text-xs">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>

        <button @click="añadirEjercicio" class="mt-4 px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
          Añadir ejercicio
        </button>

        <div class="mt-6 text-right">
          <button
            type="button"
            :disabled="!nombre || !deporte || ejercicios.length === 0"
            class="px-4 py-2 bg-gradient-to-r from-blue-900 to-blue-500 text-white rounded disabled:opacity-40 disabled:cursor-not-allowed"
            @click="crearRutina"
          >
            Publicar rutina
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import { getSports, createRoutine } from '@/services/routineService'

const router = useRouter()

const nombre = ref('')
const deporte = ref('')
const sports = ref([])
const ejercicios = ref([])

const añadirEjercicio = () => {
  ejercicios.value.push({ nombre: '', descripcion: '', seriesReps: '', descanso: '' })
}
const eliminarEjercicio = (index) => { ejercicios.value.splice(index, 1) }

const parseSeriesReps = (txt) => {
  if (!txt) return { series: null, reps: null }
  const m = String(txt).toLowerCase().replace(/\s+/g,'').match(/^(\d+)[x×](\d+)(?:-\d+)?$/)
  return m ? { series: parseInt(m[1]), reps: parseInt(m[2]) } : { series: null, reps: null }
}
const parseRest = (txt) => {
  if (!txt) return null
  const s = String(txt).toLowerCase().replace(/\s+/g,'')
  let m = s.match(/^(\d+)-(\d+)min$/); if (m) return Math.round((+m[1]+ +m[2])/2)*60
  m = s.match(/^(\d+)(?:min|m)$/);      if (m) return +m[1]*60
  m = s.match(/^(\d+)(?:s|seg|secs?)$/);if (m) return +m[1]
  m = s.match(/^(\d+)$/);               if (m) return +m[1]
  return null
}

const crearRutina = async () => {
  if (!nombre.value.trim()) return alert('El nombre es obligatorio')
  if (!deporte.value) return alert('Selecciona un deporte')
  if (ejercicios.value.length === 0) return alert('Añade al menos un ejercicio')
  if (ejercicios.value.some(e => !e.nombre?.trim() || !e.descripcion?.trim()))
    return alert('Cada ejercicio debe tener nombre y descripción')

  const payload = {
    name: nombre.value.trim(),
    sport: deporte.value,
    exercises: ejercicios.value.map((e, idx) => ({
      name: e.nombre.trim(),
      description: e.descripcion?.trim() || null,
      position: idx + 1,
      // 👇 claves que espera el back
      series_reps: e.seriesReps?.trim() || null,
      rest: e.descanso?.trim() || null,
    })),
  }

  try {
    await createRoutine(payload)
    router.push({ path: '/routines', query: { refresh: String(Date.now()) } })
  } catch (err) {
    console.error(err)
    alert('No se pudo crear la rutina. Revisa los campos.')
  }
}

onMounted(async () => {
  try { sports.value = await getSports() } catch { sports.value = [] }
})
</script>
