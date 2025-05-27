<template>
  <div class="h-screen flex flex-col bg-gray-50">
    <!-- Navbar -->
    <Navbar />

    <!-- Contenido principal -->
    <div class="flex-1 p-4">
      <div class="max-w-6xl mx-auto">
        <!-- Cabecera -->
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Progreso de {{ nombreUsuario }}</h2>
          <button
            @click="mostrarRutinas = true"
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
              <h3 class="font-semibold text-lg text-gray-700 mb-2">Días entrenados</h3>
              <div class="flex justify-center items-center flex-grow">
                <div class="w-[270px] h-[340px] overflow-hidden">
                  <Datepicker
                    v-model="fechaSeleccionada"
                    inline
                    multi-dates
                    :highlight="fechasEntrenadas"
                    :enable-time-picker="false"
                    auto-apply
                    class="w-full h-full"
                  />
                </div>
              </div>
            </div>

            <!-- Resultados de rutinas -->
            <div class="bg-white rounded-lg shadow p-4 flex flex-col max-h-[280px]">
              <h3 class="font-semibold text-lg text-gray-700 mb-2">Resultados de rutinas</h3>
              <div class="overflow-y-auto custom-scroll flex-1">
                <div
                  v-for="reporte in reportes"
                  :key="reporte.id"
                  @click="verReporte(reporte.id, false)"
                  class="border p-2 rounded mb-2 cursor-pointer hover:bg-gray-100 text-sm"
                >
                  <p class="font-semibold">Reporte de: {{ reporte.titulo }}</p>
                  <p class="text-gray-600">{{ reporte.resumen }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Columna derecha -->
          <div class="grid grid-rows-2 gap-6">
            <!-- Últimas rutinas -->
            <div class="bg-white rounded-lg shadow p-4 flex flex-col max-h-[430px]">
              <h3 class="font-semibold text-lg text-gray-700 mb-2">Últimas rutinas realizadas</h3>
              <div class="overflow-y-auto custom-scroll flex-1">
                <RoutineCard
                  v-for="rutina in ultimasRutinas"
                  :key="rutina.id"
                  :rutina="rutina"
                  @select="verRutina"
                />
              </div>
            </div>

            <!-- Gráfica -->
            <div class="bg-white rounded-lg shadow p-4 flex flex-col justify-between max-h-[280px]">
              <h3 class="font-semibold text-lg text-gray-700 mb-2">Deportes más practicados</h3>
              <div class="h-56">
                <RadialChart :deportes="deportesRealizados" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para elegir rutina -->
    <div
      v-if="mostrarRutinas"
      class="fixed inset-0 backdrop-blur-sm bg-gray-800/20 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Selecciona una rutina para el reporte</h3>
        <div v-for="rutina in ultimasRutinas" :key="rutina.id" class="mb-2">
          <button
            @click="verReporte(rutina.id, true)"
            class="w-full text-left px-4 py-2 bg-gray-100 rounded hover:bg-gray-200"
          >
            {{ rutina.nombre }} - {{ rutina.deporte }}
          </button>
        </div>
        <button
          @click="mostrarRutinas = false"
          class="mt-4 text-sm text-gray-600 hover:underline"
        >
          Cancelar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import RoutineCard from '@/components/RoutineCard.vue'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import RadialChart from '@/components/RadialChart.vue'

const router = useRouter()
const nombreUsuario = 'Sergio Velarde'
const mostrarRutinas = ref(false)
const fechaSeleccionada = ref(null)
const fechasEntrenadas = ref([])

const ultimasRutinas = ref([
  {
    id: 1,
    nombre: 'Rutina 1 - Pecho explosivo',
    creador: 'Elena Serna',
    deporte: 'Powerlifting',
    descripcion: 'Fuerza máxima en press banca',
    valoracion: 4
  },
  {
    id: 2,
    nombre: 'Rutina 2 - Espalda funcional',
    creador: 'David Conde',
    deporte: 'Fitness',
    descripcion: 'Movilidad y fuerza dorsal',
    valoracion: 5
  },
  {
    id: 3,
    nombre: 'Rutina 3 - Senderismo al aire libre',
    creador: 'Clara',
    deporte: 'Senderismo',
    descripcion: 'Ejercicio cardiovascular en exteriores',
    valoracion: 3
  }
])

const reportes = ref([
  {
    id: 1,
    titulo: 'Rutina para pecho explosivo',
    resumen: 'Ejercicio 1: Usé un peso de 74 kg. Ejercicio 2: 3 repeticiones extra.'
  },
  {
    id: 2,
    titulo: 'Rutina espalda funcional',
    resumen: 'Aumenté el peso en remo con barra.'
  }
])

const deportesRealizados = ref({
  Powerlifting: 34,
  Senderismo: 2
})

const verRutina = (rutina) => {
  router.push(`/routine/${rutina.id}`)
}

const verReporte = (id, nuevo) => {
  mostrarRutinas.value = false
  router.push({ path: `/report/${id}`, query: nuevo ? { modo: 'editar' } : {} })
}
</script>

<style>
.custom-scroll::-webkit-scrollbar {
  width: 8px;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}
</style>