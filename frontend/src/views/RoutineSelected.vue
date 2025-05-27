<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    
    <div class="max-w-4xl mx-auto py-10 px-6">
      <!-- Cabecera -->
      <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-bold">Rutina de pecho</h2>
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
      <p class="text-sm text-gray-600">
        Creado por
        <span
          class="text-blue-600 hover:underline cursor-pointer"
          @click="irAlPerfil(rutina.creadorUsuario)"
        >
          {{ rutina.creador }}
        </span>
        • {{ rutina.deporte }}
      </p>
      <p class="text-gray-700 mt-2">{{ rutina.descripcion }}</p>

      <!-- Tabla de ejercicios -->
      <div class="mt-6 overflow-x-auto">
        <table class="min-w-full border rounded shadow">
          <thead>
            <tr class="bg-blue-100 text-left">
              <th class="px-4 py-2">Ejercicio</th>
              <th class="px-4 py-2">Descripción</th>
              <th class="px-4 py-2">Descanso</th>
              <th class="px-4 py-2">Series x Repeticiones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(ejercicio, index) in rutina.ejercicios" :key="index" class="border-t">
              <td class="px-4 py-2 font-semibold">{{ ejercicio.nombre }}</td>
              <td class="px-4 py-2">{{ ejercicio.descripcion }}</td>
              <td class="px-4 py-2">{{ ejercicio.descanso }}</td>
              <td class="px-4 py-2">{{ ejercicio.repeticiones }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Botones -->
      <div class="flex justify-between mt-6">
        <button
          class="px-4 py-2 text-sm bg-gradient-to-r from-purple-600 to-purple-400 text-white rounded hover:opacity-90"
        >
          Valorar
        </button>
        <button
          class="px-4 py-2 text-sm bg-gradient-to-r from-blue-900 to-blue-500 text-white rounded hover:opacity-90"
        >
          Añadir a mis rutinas
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import { ArrowLeft } from 'lucide-vue-next'

const router = useRouter()

const rutina = {
  creador: 'Elena Serna',
  creadorUsuario: 'elenaserna80',
  deporte: 'Powerlifting',
  descripcion: 'Entrenamiento enfocado a fuerza máxima en press banca.',
  ejercicios: [
    {
      nombre: 'Press banca plano',
      descripcion: 'Ejercicio principal para desarrollar fuerza en el pectoral mayor, tríceps y deltoides anterior.',
      descanso: '2-3 min',
      repeticiones: '5 x 3'
    },
    {
      nombre: 'Press inclinado con barra',
      descripcion: 'Trabaja la parte superior del pectoral, también implica tríceps y hombros.',
      descanso: '2 min',
      repeticiones: '4 x 5'
    },
    {
      nombre: 'Press con mancuernas en banco plano',
      descripcion: 'Mayor rango de movimiento para una activación profunda del pectoral.',
      descanso: '90 seg',
      repeticiones: '4 x 8'
    },
    {
      nombre: 'Fondos en paralelas',
      descripcion: 'Enfocado en pectoral inferior y tríceps, muy útil para fuerza funcional.',
      descanso: '2 min',
      repeticiones: '3 x 6-8'
    },
    {
      nombre: 'Aperturas con mancuernas',
      descripcion: 'Aislamiento del pecho para finalizar la rutina, ideal para mejorar el estiramiento.',
      descanso: '60 seg',
      repeticiones: '3 x 12'
    }
  ]
}

const irAlPerfil = (usuario) => {
  router.push(`/profile/${usuario}`)
}
</script>

