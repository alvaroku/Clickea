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

const goBack = () => {
  router.push({ name: 'welcome' })
}

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
        class="sticky top-0 z-20 bg-white/95 backdrop-blur-sm border-b border-slate-100 px-6 pt-4 pb-4 md:rounded-t-3xl"
      >
        <div class="flex items-center justify-between mb-4">
          <button
            @click="goBack"
            class="text-text-main flex size-10 shrink-0 items-center justify-center rounded-full hover:bg-slate-50 transition-colors"
          >
            <span class="material-symbols-outlined text-2xl">arrow_back</span>
          </button>
          <h2 class="text-text-main text-xl font-bold tracking-tight">
            Servicios
          </h2>
          <div class="flex items-center justify-end">
            <button
              class="flex size-10 cursor-pointer items-center justify-center rounded-full bg-transparent text-text-main hover:bg-slate-50 transition-colors"
            >
              <span class="material-symbols-outlined text-2xl">tune</span>
            </button>
          </div>
        </div>
        <div class="relative group">
          <div
            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"
          >
            <span class="material-symbols-outlined text-text-muted text-[22px]"
              >search</span
            >
          </div>
          <input
            class="block w-full rounded-xl border-none bg-slate-50 py-3.5 pl-11 pr-4 text-text-main placeholder:text-text-muted focus:ring-0 focus:bg-white focus:shadow-flat transition-all text-sm font-medium"
            placeholder="Buscar servicios..."
            type="text"
          />
        </div>
      </header>

      <main class="flex-1 px-6 space-y-5 pb-28 pt-6 overflow-y-auto no-scrollbar">
        <div class="flex justify-between items-center">
          <h3
            class="text-sm font-bold text-text-secondary uppercase tracking-wider"
          >
            Listado
          </h3>
          <span
            class="text-xs font-bold text-primary bg-blue-50 px-2.5 py-1 rounded-md"
            >{{ services.length }} activos</span
          >
        </div>

        <div v-if="isLoading" class="flex justify-center py-10">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <div v-else-if="services.length === 0" class="text-center py-10">
          <span class="material-symbols-outlined text-5xl text-slate-300 mb-2">inventory_2</span>
          <p class="text-slate-500">No hay servicios registrados</p>
        </div>

        <div
          v-for="service in services"
          :key="service.id"
          class="group relative flex flex-col sm:flex-row items-start sm:items-center gap-4 rounded-3xl bg-surface-light p-4 shadow-subtle border border-transparent hover:border-slate-100 transition-all cursor-pointer"
        >
          <div
            class="relative shrink-0 overflow-hidden rounded-2xl size-20 sm:size-16 bg-slate-100"
          >
            <div
              class="absolute inset-0 bg-center bg-no-repeat bg-cover"
              style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCz1YIemTSmTol16__uJRGmxKtpTYlK9krtEHvkQhvP2Qdt7qCq3K5bxHoFzw_9m5r82Ibju_GgXWRGaV6VogbRIHSYwOE_ebZ6XaLzmmfid4QJBpqV3UMGaK8u3np8AyL6f9Yp_0MZrECGxaqKDHfOJOYMTI4q1MbeEqhG4Bk0yx-Ct6v8auQqXhAScLM63cOoedsi3_NmEE-48QIZ6Nsk-sac8nFTxYsQuTM637Hx5lQCaWmGCDxKKg3OgQT0JUJaBxTJCO_iDwg');"
            ></div>
          </div>
          <div class="flex flex-col flex-1 min-w-0 w-full">
            <div class="flex justify-between items-start mb-1.5">
              <p
                class="text-text-main text-[17px] font-bold leading-tight truncate pr-2"
              >
                {{ service.name }}
              </p>
              <span
                class="inline-flex items-center rounded-md bg-accent-success-bg px-2 py-1 text-[10px] font-bold text-accent-success uppercase tracking-wide"
                >Activo</span
              >
            </div>
            <div
              class="flex items-center flex-wrap gap-3 text-sm text-text-secondary"
            >
              <span class="font-bold text-text-main">${{ service.price }}</span>
              <span class="text-slate-300 text-xs">|</span>
              <span class="truncate">{{ service.description }}</span>
            </div>
          </div>
          <button
            class="absolute top-4 right-4 sm:static shrink-0 size-8 flex items-center justify-center rounded-full text-slate-300 hover:text-primary hover:bg-blue-50 transition-colors"
          >
            <span class="material-symbols-outlined text-xl">more_horiz</span>
          </button>
        </div>
      </main>

      <div class="absolute bottom-24 right-6 z-30 md:bottom-28">
        <button
          class="flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white rounded-2xl px-5 py-4 shadow-xl transition-transform active:scale-95 group"
        >
          <span class="material-symbols-outlined text-2xl">add</span>
          <span class="font-bold text-sm tracking-wide">Nuevo</span>
        </button>
      </div>

      <nav
        class="absolute bottom-0 z-20 w-full bg-surface-light border-t border-slate-100 pb-8 pt-4 px-6 flex justify-between items-center text-[10px] font-bold tracking-wide text-text-secondary uppercase md:rounded-b-3xl"
      >
        <button
          @click="router.push('/')"
          class="flex flex-col items-center gap-1.5 hover:text-primary transition-colors"
        >
          <span class="material-symbols-outlined text-[26px]">dashboard</span>
          Inicio
        </button>
        <button
          class="flex flex-col items-center gap-1.5 text-primary transition-colors"
        >
          <span
            class="material-symbols-outlined text-[26px]"
            style="font-variation-settings: 'FILL' 1"
            >inventory_2</span
          >
          Servicios
        </button>
        <button
          class="flex flex-col items-center gap-1.5 hover:text-primary transition-colors"
        >
          <span class="material-symbols-outlined text-[26px]"
            >calendar_month</span
          >
          Eventos
        </button>
        <button
          @click="handleLogout"
          class="flex flex-col items-center gap-1.5 hover:text-primary transition-colors"
        >
          <span class="material-symbols-outlined text-[26px]">logout</span>
          Salir
        </button>
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
