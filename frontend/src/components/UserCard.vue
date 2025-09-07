<template>
  <li @click="$emit('select', user.id)" class="flex items-center gap-4 p-4 hover:bg-gray-100 cursor-pointer">
    <img
      :src="avatarSrc"
      alt="avatar"
      class="w-12 h-12 rounded-full object-cover border"
      @error="onImgErr"
    />
    <div class="flex-1">
      <p class="text-base font-semibold leading-5">{{ user.name }}</p>
      <p class="text-sm text-gray-600">{{ user.email }}</p>
      <span
        class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full"
        :class="user.role === 'trainer' ? 'bg-purple-200 text-purple-800' : 'bg-blue-200 text-blue-800'"
      >
        {{ user.role === 'trainer' ? 'Entrenador' : 'Atleta' }}
      </span>
    </div>
  </li>
</template>


<script setup>
import { computed, ref } from 'vue'
import { userAvatarUrl } from '@/services/userService'

const props = defineProps({ user: { type: Object, required: true } })
const fallback = ref(
  'data:image/svg+xml;utf8,' +
  encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96"><rect width="100%" height="100%" fill="#f1f5f9"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#94a3b8" font-family="Arial" font-size="12">Sin avatar</text></svg>')
)

const avatarSrc = computed(() => {
  const v = props.user.avatar_updated_at || props.user.updated_at || ''
  return props.user.avatarUrl || props.user.avatar_url || userAvatarUrl(props.user.id, v)
})

const onImgErr = (e) => { e.target.src = fallback.value }
defineEmits(['select'])
</script>
