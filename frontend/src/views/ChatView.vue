<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />

    <!-- Cabecera del chat con botón volver -->
    <div class="max-w-4xl mx-auto p-4 relative">
      <!-- Botón volver con icono -->
      <router-link
        to="/messages"
        class="absolute top-4 right-4 flex items-center gap-1 text-sm border-2 border-transparent bg-white text-blue-700 font-medium px-3 py-1 rounded hover:text-white hover:bg-gradient-to-r from-blue-900 to-blue-500 hover:border-transparent transition"
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

      <!-- Cabecera del contacto -->
      <div class="flex items-center border-b pb-3 mb-4">
        <img :src="contacto.foto" alt="Perfil" class="w-12 h-12 rounded-full object-cover mr-4" />
        <div>
          <h2 class="font-semibold text-lg">{{ contacto.nombre }}</h2>
          <span
            class="inline-block bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded mt-1"
          >
            {{ contacto.rol }}
          </span>
        </div>
      </div>

      <!-- Mensajes -->
      <div class="space-y-4 mb-24">
        <div
          v-for="(mensaje, index) in mensajes"
          :key="index"
          class="flex"
          :class="mensaje.enviado ? 'justify-end' : 'justify-start'"
        >
          <div>
            <div
              :class="[ 
                'px-4 py-2 rounded-lg max-w-xs text-sm',
                mensaje.enviado 
                  ? 'bg-gradient-to-r from-blue-900 to-blue-500 text-white'
                  : 'bg-gray-200 text-black'
              ]"
            >
              {{ mensaje.texto }}
            </div>
            <p
              class="text-xs mt-1 text-gray-400"
              :class="mensaje.enviado ? 'text-right pr-2' : 'text-left pl-2'"
            >
              {{ mensaje.hora }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Barra de escritura -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-2">
      <div class="max-w-4xl mx-auto relative">
        <div class="flex items-center gap-2">
          <button @click="mostrarEmojis = !mostrarEmojis">
            <Smile class="w-6 h-6 text-gray-500" />
          </button>
          <input
            v-model="nuevoMensaje"
            type="text"
            placeholder="Escribe un mensaje..."
            class="flex-1 border border-gray-300 rounded-full px-4 py-2 focus:outline-none"
            @keyup.enter="enviarMensaje"
          />
          <button @click="enviarMensaje" class="text-blue-600 hover:text-blue-800">
            <SendHorizontal class="w-6 h-6" />
          </button>
        </div>

        <!-- Lista de emojis -->
        <div
          v-if="mostrarEmojis"
          class="absolute bottom-12 left-0 bg-white border border-gray-300 rounded shadow-md p-2 flex flex-wrap gap-2"
        >
          <span
            v-for="(emoji, idx) in emojis"
            :key="idx"
            class="cursor-pointer text-xl hover:scale-110 transition"
            @click="nuevoMensaje += emoji"
          >
            {{ emoji }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref } from 'vue'
import Navbar from '@/components/Navbar.vue'
import { Smile, SendHorizontal, ArrowLeft } from 'lucide-vue-next'

// Contacto simulado
const contacto = {
  nombre: 'Elena Serna',
  rol: 'Atleta',
  foto: '/publicacion1.jpg'
}

// Mensajes con hora
const mensajes = ref([
  {
    texto: 'Hola buenas tardes que tal ha ido la rutina nueva?',
    enviado: true,
    hora: '20:29'
  },
  {
    texto: 'Muy bien, estoy muy contenta',
    enviado: false,
    hora: '20:30'
  },
  {
    texto: 'Quiero que sigas entrenando un par de días y me comentas',
    enviado: true,
    hora: '20:31'
  },
  {
    texto: 'En solo dos semanas ya me noto más fuerte y más definida',
    enviado: false,
    hora: '20:32'
  },
  {
    texto: 'Posiblemente pasemos a un enfoque distinto la semana que viene',
    enviado: true,
    hora: '20:33'
  }
])

const nuevoMensaje = ref('')
const mostrarEmojis = ref(false)
const emojis = ['😄', '💪', '🔥', '❤️', '👏', '😉']

const enviarMensaje = () => {
  if (nuevoMensaje.value.trim()) {
    const ahora = new Date()
    const horaFormateada = ahora.toLocaleTimeString([], {
      hour: '2-digit',
      minute: '2-digit'
    })

    mensajes.value.push({
      texto: nuevoMensaje.value,
      enviado: true,
      hora: horaFormateada
    })

    nuevoMensaje.value = ''
    mostrarEmojis.value = false
  }
}
</script>
