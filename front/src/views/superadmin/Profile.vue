<script setup lang="ts">
import { useRouter } from 'vue-router'
import ProfileInfo from '../../components/ProfileInfo.vue'

const router = useRouter()
</script>

<template>
  <div class="bg-slate-50 min-h-screen font-display text-text-main antialiased md:py-12">
    <div
      class="relative flex h-full md:h-[calc(100vh-6rem)] min-h-screen md:min-h-0 w-full flex-col overflow-x-hidden max-w-2xl mx-auto bg-white md:shadow-2xl md:rounded-3xl shadow-subtle border border-slate-100"
    >
      <header
        class="sticky top-0 z-20 bg-white/95 backdrop-blur-sm border-b border-slate-100 px-6 pt-4 pb-4 md:rounded-t-3xl"
      >
        <div class="flex items-center justify-between mb-4">
          <button
            @click="router.push({ name: 'users' })"
            class="text-text-main flex size-10 shrink-0 items-center justify-center rounded-full hover:bg-slate-50 transition-colors"
          >
            <span class="material-symbols-outlined text-2xl">arrow_back</span>
          </button>
          <h2 class="text-text-main text-xl font-bold tracking-tight">
            Mi Perfil
          </h2>
          <button
            class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-white border border-slate-100 transition-transform active:scale-95 hover:bg-slate-50"
          >
            <span class="material-symbols-outlined text-slate-900" style="font-size: 26px">notifications</span>
            <span class="absolute top-3 right-3 h-2.5 w-2.5 rounded-full bg-primary ring-2 ring-white"></span>
          </button>
        </div>
      </header>

      <main class="flex-1 px-6 space-y-6 pb-28 pt-6 overflow-y-auto no-scrollbar">
        <ProfileInfo />
      </main>

      <nav
        class="absolute bottom-0 z-50 w-full border-t border-slate-100 bg-white/95 pb-8 px-6 backdrop-blur-xl md:rounded-b-3xl"
      >
        <div class="flex h-20 items-center justify-between">
          <button @click="router.push({ name: 'users' })" class="group flex flex-col items-center gap-1.5 w-14">
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >people</span
            >
          </button>
          <button @click="router.push({ name: 'superadmin-categories' })" class="group flex flex-col items-center gap-1.5 w-14">
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >category</span
            >
          </button>
          <button class="group flex flex-col items-center gap-1.5 w-14">
            <div
              class="flex h-10 w-12 items-center justify-center rounded-xl bg-primary/10"
            >
              <span
                class="material-symbols-outlined text-primary"
                style="font-size: 26px; font-variation-settings: 'FILL' 1"
                >account_circle</span
              >
            </div>
          </button>
        </div>
      </nav>
    </div>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
