<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/axios'
import { toast } from 'vue3-toastify'

const router = useRouter()
const services = ref<any[]>([])
const isLoading = ref(true)

const fetchServices = async () => {
  try {
    const response = await api.get('/products')
    services.value = response.data.data
  } catch (error: any) {
    toast.error('Error al cargar los servicios')
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchServices()
})

const handleLogout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="bg-slate-50 min-h-screen font-display text-text-main antialiased md:py-12">
    <div
      class="relative flex h-full md:h-[calc(100vh-6rem)] min-h-screen md:min-h-0 w-full flex-col overflow-x-hidden max-w-2xl mx-auto bg-white md:shadow-2xl md:rounded-3xl shadow-subtle border border-slate-100"
    >
      <header
        class="sticky top-0 z-20 bg-white/95 backdrop-blur-md transition-colors duration-200 border-b border-transparent md:rounded-t-3xl"
      >
        <div class="flex items-center justify-between px-6 py-6">
          <div>
            <p class="text-xs font-bold text-secondary uppercase tracking-widest mb-2">
              Bienvenido
            </p>
            <h2 class="text-3xl font-extrabold leading-tight tracking-tight text-slate-900">
              Descubre <span class="text-primary">Servicios</span>
            </h2>
          </div>
          <button
            class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-white border border-slate-100 transition-transform active:scale-95 hover:bg-slate-50"
          >
            <span class="material-symbols-outlined text-slate-900" style="font-size: 26px">notifications</span>
            <span class="absolute top-3 right-3 h-2.5 w-2.5 rounded-full bg-primary ring-2 ring-white"></span>
          </button>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto no-scrollbar pb-32">
        <div class="px-6 py-2">
          <div
            class="group flex w-full items-center rounded-2xl bg-white border border-slate-200 transition-all focus-within:border-primary focus-within:ring-1 focus-within:ring-primary/20"
          >
            <div class="flex h-14 w-14 items-center justify-center text-slate-400">
              <span class="material-symbols-outlined" style="font-size: 26px">search</span>
            </div>
            <input
              class="h-14 w-full border-none bg-transparent px-2 text-base font-semibold placeholder:text-slate-400 focus:ring-0 text-slate-900"
              placeholder="¿Qué buscas celebrar?"
              type="text"
            />
            <button
              class="flex h-14 w-14 items-center justify-center text-primary hover:bg-slate-50 rounded-r-2xl transition-colors"
            >
              <span class="material-symbols-outlined" style="font-size: 26px">tune</span>
            </button>
          </div>
        </div>

        <div class="mt-6 w-full">
          <div class="flex w-full gap-3 overflow-x-auto px-6 py-2 no-scrollbar">
            <button
              class="flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-primary px-6 transition-transform active:scale-95 hover:bg-primary/90 shadow-sm"
            >
              <span class="material-symbols-outlined text-white" style="font-size: 20px">grid_view</span>
              <span class="text-sm font-bold text-white">Todos</span>
            </button>
            <button
              class="flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-white border border-slate-200 px-6 transition-transform active:scale-95 hover:bg-slate-50"
            >
              <span class="material-symbols-outlined text-slate-400" style="font-size: 20px">restaurant</span>
              <span class="text-sm font-bold text-slate-600">Catering</span>
            </button>
            <button
              class="flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-white border border-slate-200 px-6 transition-transform active:scale-95 hover:bg-slate-50"
            >
              <span class="material-symbols-outlined text-slate-400" style="font-size: 20px">music_note</span>
              <span class="text-sm font-bold text-slate-600">Música</span>
            </button>
          </div>
        </div>

        <div class="px-6 pt-10 pb-4 flex items-end justify-between">
          <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">
            Populares
          </h3>
          <a href="#" class="text-sm font-bold text-primary hover:text-primary/80 transition-colors mb-0.5">
            Ver todos
          </a>
        </div>

        <div v-if="isLoading" class="flex justify-center py-10">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <div v-else-if="services.length === 0" class="text-center py-10">
          <span class="material-symbols-outlined text-5xl text-slate-300 mb-2">inventory_2</span>
          <p class="text-slate-500">No hay servicios registrados</p>
        </div>

        <div v-else class="flex flex-col gap-8 px-6 pb-4">
          <div v-for="service in services" :key="service.id" class="@container">
            <div
              class="group flex flex-col overflow-hidden rounded-[2rem] bg-white transition-all hover:shadow-lg ring-1 ring-slate-100 shadow-card"
            >
              <div class="relative aspect-[16/9] w-full overflow-hidden bg-gray-100">
                <div
                  class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-105"
                  style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuA9RUNxl9tHHK5_zyGVDe3Y4nxWW46rYnc2aoXnryOR5T-PMZHYahFeoOOjv4S6jAPr2btB0PnzOhxQ2yOxNwOXk3DKVfykMznasrY9QhnjjdpYnO8qqMLte-sxWvj5yPYa5vo0CMXkAH594dHKN8RGyZTnLniufh1J3idSonDORJ4QSWlWIvVm01S2vorzxNPX5ccnIPhN2zD8F3J0yuicu8bgh3kaPZ7FlW54eUEP7xgPWOAb5RgEksPPBu44DZxatjGjpoYmSXc');"
                ></div>
                <div class="absolute top-4 right-4 rounded-lg bg-white px-2 py-1 shadow-sm border border-slate-100">
                  <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-yellow-400" style="font-size: 16px">star</span>
                    <span class="text-xs font-bold text-slate-900">4.8</span>
                  </div>
                </div>
                <div class="absolute bottom-4 left-4">
                  <span
                    class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-bold text-slate-900 shadow-sm border border-slate-100"
                  >
                    <span class="material-symbols-outlined mr-1.5 text-primary" style="font-size: 14px">restaurant</span>
                    Servicio
                  </span>
                </div>
              </div>
              <div class="flex flex-col gap-4 p-6">
                <div class="flex justify-between items-start">
                  <div>
                    <h3
                      class="text-xl font-extrabold leading-tight text-slate-900 group-hover:text-primary transition-colors line-clamp-1"
                    >
                      {{ service.name }}
                    </h3>
                    <p class="text-sm font-semibold text-slate-500 mt-1.5 line-clamp-1">
                      {{ service.description }}
                    </p>
                  </div>
                  <div
                    class="h-10 w-10 flex items-center justify-center rounded-full bg-slate-50 text-slate-300 hover:text-primary hover:bg-primary/10 transition-colors cursor-pointer"
                  >
                    <span class="material-symbols-outlined" style="font-size: 22px">favorite</span>
                  </div>
                </div>
                <div class="flex items-center justify-between border-t border-slate-100 pt-5">
                  <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Precio</span>
                    <span class="text-2xl font-black text-primary">
                      ${{ service.price }}
                      <span class="text-sm font-bold text-slate-400">/evento</span>
                    </span>
                  </div>
                  <button
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900 hover:bg-slate-800 text-white transition-transform active:scale-95"
                  >
                    <span class="material-symbols-outlined" style="font-size: 24px">arrow_forward</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      <nav
        class="absolute bottom-0 z-50 w-full border-t border-slate-100 bg-white/95 pb-8 px-6 backdrop-blur-xl md:rounded-b-3xl"
      >
        <div class="flex h-20 items-center justify-between">
          <button @click="router.push('/')" class="group flex flex-col items-center gap-1.5 w-14">
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >home</span
            >
          </button>
          <button class="group flex flex-col items-center gap-1.5 w-14">
            <div class="flex h-10 w-12 items-center justify-center rounded-xl bg-primary/10">
              <span
                class="material-symbols-outlined text-primary"
                style="font-size: 26px; font-variation-settings: 'FILL' 1"
                >manage_search</span
              >
            </div>
          </button>
          <button
            class="relative -top-8 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-white shadow-lg transition-transform active:scale-90 hover:bg-primary/90"
          >
            <span class="material-symbols-outlined" style="font-size: 32px">add</span>
          </button>
          <button class="group flex flex-col items-center gap-1.5 w-14">
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >calendar_month</span
            >
          </button>
          <button @click="handleLogout" class="group flex flex-col items-center gap-1.5 w-14">
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >logout</span
            >
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
