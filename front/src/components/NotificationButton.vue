<script setup lang="ts">
defineProps<{
  unreadCount: number
}>()

defineEmits<{
  click: []
}>()
</script>

<template>
  <button
    @click="$emit('click')"
    class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-white border border-slate-100 transition-transform active:scale-95 hover:bg-slate-50"
  >
    <span class="material-symbols-outlined text-slate-900" style="font-size: 26px">notifications</span>
    <span v-if="unreadCount > 0" class="absolute top-3 right-3 h-2.5 w-2.5 rounded-full bg-primary ring-2 ring-white"></span>
  </button>
</template>
