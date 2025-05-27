<template>
  <div class="min-h-screen bg-gray-50">
    <div class="sticky top-0 z-50">
      <Navbar />
    </div>

    <div class="max-w-6xl mx-auto p-6">
      <!-- Cabecera -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Rutina de {{ reporte.titulo }}</h2>
        <button
          v-if="!modoEdicion"
          @click="modoEdicion = true"
          class="px-4 py-2 text-sm font-semibold text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 shadow"
        >
          Editar
        </button>
      </div>

      <p class="text-sm text-gray-600 mb-1">
        Creado por <span class="text-blue-700 font-medium">{{ reporte.creador }}</span> · {{ reporte.deporte }}
      </p>
      <p class="text-gray-700 mb-4">{{ reporte.descripcion }}</p>

      <!-- Tabla de ejercicios -->
      <div class="overflow-x-auto">
        <table class="w-full table-auto border rounded overflow-hidden text-sm">
          <thead class="bg-blue-100 text-gray-700">
            <tr>
              <th class="p-2 text-left">Ejercicio</th>
              <th class="p-2 text-left">Descripción</th>
              <th class="p-2 text-left">Descanso</th>
              <th class="p-2 text-left">Series x Repeticiones</th>
              <th class="p-2 text-left">Dificultad</th>
              <th class="p-2 text-left">Peso / Vel. / Dist.</th>
              <th class="p-2 text-center">Completado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(ejercicio, index) in reporte.ejercicios" :key="index" class="border-t">
              <td class="p-2 font-semibold">{{ ejercicio.nombre }}</td>
              <td class="p-2 text-gray-600">{{ ejercicio.descripcion }}</td>
              <td class="p-2 text-gray-600">{{ ejercicio.descanso }}</td>
              <td class="p-2 text-gray-600">{{ ejercicio.series }}</td>
              <td class="p-2">
                <select v-if="modoEdicion" v-model="ejercicio.dificultad" class="border rounded px-2 py-1">
                  <option v-for="n in 11" :key="n - 1" :value="n - 1">{{ n - 1 }}</option>
                </select>
                <span v-else>{{ ejercicio.dificultad }}</span>
              </td>
              <td class="p-2">
                <input
                  v-if="modoEdicion"
                  type="text"
                  v-model="ejercicio.valorRealizado"
                  placeholder="kg, km/h, m"
                  class="border rounded px-2 py-1 w-full"
                />
                <span v-else>{{ ejercicio.valorRealizado }}</span>
              </td>
              <td class="p-2 text-center">
                <input
                  v-if="modoEdicion"
                  type="checkbox"
                  v-model="ejercicio.completado"
                />
                <span v-else>{{ ejercicio.completado ? '✔' : '' }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Botones -->
      <div class="flex justify-between items-center mt-6">
        <button
          @click="router.push('/progress')"
          class="px-4 py-2 text-sm text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 shadow"
        >
          ← Volver
        </button>

        <div class="flex gap-2">
          <button
            v-if="modoEdicion"
            @click="modoEdicion = false"
            class="px-4 py-2 text-sm text-white rounded bg-gradient-to-r from-red-700 to-red-400 hover:opacity-90 shadow"
          >
            Cancelar cambios
          </button>

          <button
            v-if="modoEdicion"
            @click="guardarCambios"
            class="px-4 py-2 text-sm text-white rounded bg-gradient-to-r from-green-600 to-green-400 hover:opacity-90 shadow"
          >
            Guardar cambios
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'

const router = useRouter()
const route = useRoute()

const reportesSimulados = [
  {
    id: 1,
    titulo: 'pecho',
    creador: 'Elena Serna',
    deporte: 'Powerlifting',
    descripcion: 'Entrenamiento enfocado a fuerza máxima en press banca.',
    ejercicios: [
      {
        nombre: 'Press banca plano',
        descripcion: 'Ejercicio principal para desarrollar fuerza...',
        descanso: '2-3 min',
        series: '5 x 3',
        dificultad: 0,
        valorRealizado: '',
        completado: false,
      },
      {
        nombre: 'Press inclinado con barra',
        descripcion: 'Parte superior del pectoral, también hombros y tríceps.',
        descanso: '2 min',
        series: '4 x 5',
        dificultad: 0,
        valorRealizado: '',
        completado: false,
      },
      {
        nombre: 'Press con mancuernas en banco plano',
        descripcion: 'Mayor rango de movimiento para una activación profunda.',
        descanso: '90 seg',
        series: '4 x 8',
        dificultad: 0,
        valorRealizado: '',
        completado: false,
      },
      {
        nombre: 'Fondos en paralelas',
        descripcion: 'Pectoral inferior y tríceps, útil para fuerza funcional.',
        descanso: '2 min',
        series: '3 x 6-8',
        dificultad: 0,
        valorRealizado: '',
        completado: false,
      },
      {
        nombre: 'Aperturas con mancuernas',
        descripcion: 'Aislamiento del pecho, ideal para el estiramiento.',
        descanso: '60 seg',
        series: '3 x 12',
        dificultad: 0,
        valorRealizado: '',
        completado: false,
      },
    ],
  },
]

const id = Number(route.params.id)
const modoEdicion = ref(route.query.modo === 'editar')
const reporte = reactive(reportesSimulados.find((r) => r.id === id))

const toggleEditar = () => {
  modoEdicion.value = true
  router.replace({ query: { modo: 'editar' } })
}

const guardarCambios = () => {
  modoEdicion.value = false
  router.replace({ query: { modo: 'lectura' } })
  // Aquí se podría enviar al backend más adelante
}

const goBack = () => {
  router.push('/progress')
}
</script>
