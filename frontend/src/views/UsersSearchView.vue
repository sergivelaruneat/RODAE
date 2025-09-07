<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />

    <div class="max-w-5xl mx-auto p-4">
      <div class="flex items-center justify-between mb-3">
        <h1 class="text-lg sm:text-xl font-semibold">Buscar usuarios</h1>
      </div>

      <div class="mb-3">
        <input
          v-model="q"
          type="text"
          class="w-full px-3 py-2 border rounded focus:outline-none focus:ring"
          placeholder="Buscar por nombre o correo…"
          @keydown.enter="doSearch"
          @input="onTyping"
        />
      </div>

      <div class="bg-white rounded border" v-if="!loading && users.length">
        <ul class="divide-y divide-gray-200">
          <UserCard
            v-for="u in users"
            :key="u.id"
            :user="u"
            @select="openProfile"
          />
        </ul>
      </div>

      <div v-if="loading" class="bg-white rounded border p-6 text-center text-gray-600">
        Buscando…
      </div>

      <div v-if="!loading && users.length === 0 && q.trim() !== ''"
           class="bg-white rounded border p-6 text-center text-gray-600">
        No se encontraron usuarios para “{{ q }}”.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import UserCard from '@/components/UserCard.vue'
import { searchUsers } from '@/services/userService'

const router = useRouter()
const route = useRoute()

const q = ref(route.query.q?.toString() ?? '')
const users = ref([])
const loading = ref(false)

const fetchUsers = async () => {
  if (!q.value.trim()) { users.value = []; return }
  loading.value = true
  try {
    users.value = await searchUsers({ q: q.value.trim(), per_page: 30 })
  } catch (e) {
    console.error(e)
    users.value = []
  } finally {
    loading.value = false
  }
}

let t = null
const onTyping = () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.replace({ name: 'UsersSearch', query: { q: q.value.trim() } })
    fetchUsers()
  }, 250)
}
const doSearch = () => {
  router.replace({ name: 'UsersSearch', query: { q: q.value.trim() } })
  fetchUsers()
}

const openProfile = (id) => router.push({ name: 'OtherProfile', params: { user: id } })

onMounted(fetchUsers)
watch(() => route.query.q, (val) => {
  q.value = val?.toString() ?? ''
  fetchUsers()
})
</script>
