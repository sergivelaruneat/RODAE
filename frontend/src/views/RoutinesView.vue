<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    <div class="fixed top-25 right-15 z-40">
      <button
        @click="irACrearRutina"
        class="px-4 py-2 text-sm font-semibold text-white rounded bg-gradient-to-r from-blue-900 to-blue-500 hover:opacity-90 shadow"
      >
        Añadir rutina
      </button>
    </div>
    <div class="max-w-4xl mx-auto p-4">
      <!-- Buscador -->
      <input
        v-model="busqueda"
        type="text"
        placeholder="Buscar rutina..."
        class="w-full px-4 py-2 border rounded mb-4"
      />

      <!-- Lista de rutinas -->
      <RoutineCard
        v-for="rutina in rutinasFiltradas"
        :key="rutina.id"
        :rutina="rutina"
        @select="irARutina"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import RoutineCard from '@/components/RoutineCard.vue'

const router = useRouter()

const busqueda = ref('')
const rutinas = ref([
  {
    id: 1,
    nombre: 'Rutina Pecho Explosivo',
    creador: 'Elena Serna',
    deporte: 'Powerlifting',
    descripcion: 'Entrenamiento enfocado a fuerza máxima en press banca.',
    valoracion: 4
  },
  {
    id: 2,
    nombre: 'Fullbody funcional',
    creador: 'David Conde',
    deporte: 'Fitness',
    descripcion: 'Ideal para empezar y trabajar todo el cuerpo.',
    valoracion: 5
  }
])

const rutinasFiltradas = computed(() =>
  rutinas.value.filter(r => r.nombre.toLowerCase().includes(busqueda.value.toLowerCase()))
)

const irARutina = (rutina) => {
  router.push(`/routine/${rutina.id}`)
}
const irACrearRutina = () => {
  router.push('/routine/create')
}

</script>