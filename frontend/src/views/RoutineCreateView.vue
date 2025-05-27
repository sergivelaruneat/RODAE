<template>
  <div class="h-screen flex flex-col bg-gray-50">
    <Navbar />
    <div class="flex-1 overflow-y-auto custom-scroll p-4">
      <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
        <!-- Encabezado con título y botón volver alineado a la derecha -->
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-bold">Crear nueva rutina</h2>
          <router-link
            to="/routines"
            class="flex items-center gap-1 text-sm border-2 border-transparent bg-white text-blue-700 font-medium px-3 py-1 rounded hover:text-white hover:bg-gradient-to-r from-blue-900 to-blue-500 hover:border-transparent transition"
            :style="{
              borderImage: 'linear-gradient(to right, #1e3a8a, #3b82f6) 1',
              borderStyle: 'solid',
              borderWidth: '2px',
              borderRadius: '0.5rem'
            }"
          >
            <ArrowLeft class="w-4 h-4" />
            Volver
          </router-link>
        </div>
        <!-- Formulario general -->
        <div class="space-y-4">
          <input v-model="nombre" type="text" placeholder="Nombre de la rutina" class="w-full px-4 py-2 border rounded" />
          <textarea v-model="descripcion" placeholder="Descripción..." class="w-full px-4 py-2 border rounded" rows="3" />
          <input v-model="deporte" type="text" placeholder="Deporte relacionado" class="w-full px-4 py-2 border rounded" />
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
              <td class="border px-2 py-1"><input v-model="ej.nombre" class="w-full border rounded px-1" /></td>
              <td class="border px-2 py-1"><input v-model="ej.descripcion" class="w-full border rounded px-1" /></td>
              <td class="border px-2 py-1"><input v-model="ej.seriesReps" class="w-full border rounded px-1" /></td>
              <td class="border px-2 py-1"><input v-model="ej.descanso" class="w-full border rounded px-1" /></td>
              <td class="border px-2 py-1 text-center">
                <button @click="eliminarEjercicio(index)" class="text-red-500 hover:underline text-xs">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>

        <button
          @click="añadirEjercicio"
          class="mt-4 px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
        >
          Añadir ejercicio
        </button>

        <!-- Botón publicar -->
        <div class="mt-6 text-right">
          <button
            @click="crearRutina"
            class="px-4 py-2 bg-gradient-to-r from-blue-900 to-blue-500 text-white rounded hover:opacity-90 shadow"
          >
            Publicar rutina
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import { ArrowLeft } from 'lucide-vue-next'

const router = useRouter()

const nombre = ref('')
const descripcion = ref('')
const deporte = ref('')
const ejercicios = ref([])

const añadirEjercicio = () => {
  ejercicios.value.push({
    nombre: '',
    descripcion: '',
    seriesReps: '',
    descanso: ''
  })
}

const eliminarEjercicio = (index) => {
  ejercicios.value.splice(index, 1)
}

const crearRutina = () => {
  // Lógica de validación y envío aquí
  console.log('Rutina creada:', {
    nombre: nombre.value,
    descripcion: descripcion.value,
    deporte: deporte.value,
    ejercicios: ejercicios.value
  })
  router.push('/routines')
}
</script>
