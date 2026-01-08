<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/axios'
import { toast } from 'vue3-toastify'
import NotificationPanel from '../../components/NotificationPanel.vue'
import NotificationButton from '../../components/NotificationButton.vue'
import { useNotifications } from '../../composables/useNotifications'

const router = useRouter()
const { unreadCount, isNotificationPanelOpen, openNotificationPanel, closeNotificationPanel, updateCount } = useNotifications()
const API_URL =(image:any)=> `${import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || ''}/storage/${image.path}`
const services = ref<any[]>([])
const categories = ref<any[]>([])
const isLoading = ref(true)
const searchTerm = ref('')
const selectedCategoryId = ref<string | number>('all')

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
})

const fetchCategories = async () => {
  try {
    const response = await api.get('/categories/all')
    categories.value = response.data.data
  } catch (error) {
    console.error('Error fetching categories:', error)
  }
}

const fetchServices = async (page = 1) => {
  isLoading.value = true
  try {
    const params: any = { page }
    if (searchTerm.value) params.search = searchTerm.value
    if (selectedCategoryId.value !== 'all') params.category_id = selectedCategoryId.value

    const response = await api.get('/services/catalog', { params })
    services.value = response.data.data.data
    pagination.value = {
      current_page: response.data.data.current_page,
      last_page: response.data.data.last_page,
      total: response.data.data.total
    }
  } catch (error: any) {
    toast.error('Error al cargar los servicios')
  } finally {
    isLoading.value = false
  }
}

const selectCategory = (id: string | number) => {
  selectedCategoryId.value = id
  fetchServices(1)
}

onMounted(() => {
  fetchServices()
  fetchCategories()
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
          <NotificationButton :unreadCount="unreadCount" @click="openNotificationPanel" />
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
              v-model="searchTerm"
              @keyup.enter="fetchServices(1)"
              class="h-14 w-full border-none bg-transparent px-2 text-base font-semibold placeholder:text-slate-400 focus:ring-0 text-slate-900"
              placeholder="¿Qué servicio buscas?"
              type="text"
            />
            <button
              @click="fetchServices(1)"
              class="flex h-14 w-14 items-center justify-center text-primary hover:bg-slate-50 rounded-r-2xl transition-colors"
            >
              <span class="material-symbols-outlined" style="font-size: 26px">keyboard_return</span>
            </button>
          </div>
        </div>

        <div class="mt-6 w-full">
          <div class="flex w-full gap-3 overflow-x-auto px-6 py-2 no-scrollbar">
            <button
              @click="selectCategory('all')"
              :class="selectedCategoryId === 'all' ? 'bg-primary text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
              class="flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl px-6 transition-transform active:scale-95"
            >
              <span class="material-symbols-outlined" :class="selectedCategoryId === 'all' ?'text-white' : 'text-slate-400'" style="font-size: 20px">grid_view</span>
              <span class="text-sm font-bold">Todos</span>
            </button>
            <button
              v-for="cat in categories"
              :key="cat.id"
              @click="selectCategory(cat.id)"
              :class="selectedCategoryId === cat.id ? 'bg-primary text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
              class="flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl px-6 transition-transform active:scale-95"
            >
              <span class="material-symbols-outlined" :class="selectedCategoryId === cat.id ?'text-white' : 'text-slate-400'" style="font-size: 20px">category</span>
              <span class="text-sm font-bold capitalize">{{ cat.name }}</span>
            </button>
          </div>
        </div>

        <div class="px-6 pt-10 pb-4 flex items-end justify-between">
          <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">
            {{ selectedCategoryId === 'all' ? 'Todos los servicios' : 'En esta categoría' }}
          </h3>
          <span v-if="pagination.total" class="text-xs font-bold text-primary bg-cyan-50 px-2.5 py-1 rounded-md mb-1">
            {{ pagination.total }} {{ pagination.total === 1 ? 'resultado' : 'resultados' }}
          </span>
        </div>

        <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 gap-4">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
          <p class="text-slate-400 text-sm font-bold">Descubriendo servicios...</p>
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
                <img 
                  v-if="service.images && service.images.length > 0"
                  :src="API_URL(service.images[0])"
                  :alt="service.name"
                  class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div
                  v-else
                  class="absolute inset-0 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center"
                >
                  <span class="material-symbols-outlined text-slate-300" style="font-size: 64px">local_mall</span>
                </div>
                <!-- <div class="absolute top-4 right-4 rounded-lg bg-white px-2 py-1 shadow-sm border border-slate-100">
                  <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-yellow-400" style="font-size: 16px">star</span>
                    <span class="text-xs font-bold text-slate-900">4.8</span>
                  </div>
                </div> -->
                <div class="absolute bottom-4 left-4">
                  <span
                    class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-bold text-slate-900 shadow-sm border border-slate-100"
                  >
                    <span class="material-symbols-outlined mr-1.5 text-primary" style="font-size: 14px">category</span>
                    {{ service.category?.name || 'Varios' }}
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
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mt-1">
                      Por: {{ service.owner?.name || 'Staff' }}
                    </p>
                    <p class="text-sm font-semibold text-slate-500 mt-1.5 line-clamp-1">
                      {{ service.description }}
                    </p>
                  </div>
                  <!-- <div
                    class="h-10 w-10 flex items-center justify-center rounded-full bg-slate-50 text-slate-300 hover:text-primary hover:bg-primary/10 transition-colors cursor-pointer"
                  >
                    <span class="material-symbols-outlined" style="font-size: 22px">favorite</span>
                  </div> -->
                </div>
                <div class="flex items-center justify-between border-t border-slate-100 pt-5">
                  <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Precio</span>
                    <span class="text-2xl font-black text-primary">
                      ${{ service.price }}
                      <span class="text-sm font-bold text-slate-400"></span>
                    </span>
                  </div>
                  <button
                    @click="router.push({ name: 'user-request-service', params: { id: service.id } })"
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900 hover:bg-slate-800 text-white transition-transform active:scale-95"
                  >
                    <span class="material-symbols-outlined" style="font-size: 24px">arrow_forward</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Paginación -->
        <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-2 pt-6 pb-10">
          <button 
            @click="fetchServices(1)"
            :disabled="pagination.current_page === 1"
            class="size-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 disabled:grayscale transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined">first_page</span>
          </button>
          <button 
            @click="fetchServices(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="size-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined">chevron_left</span>
          </button>
          
          <div class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-900 shadow-sm flex items-center gap-2">
            <span class="text-primary">{{ pagination.current_page }}</span>
            <span class="text-slate-300">/</span>
            <span>{{ pagination.last_page }}</span>
          </div>

          <button 
            @click="fetchServices(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="size-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
          <button 
            @click="fetchServices(pagination.last_page)"
            :disabled="pagination.current_page === pagination.last_page"
            class="size-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 disabled:grayscale transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined">last_page</span>
          </button>
        </div>
      </main>

      <nav
        class="absolute bottom-0 z-50 w-full border-t border-slate-100 bg-white/95 pb-8 px-6 backdrop-blur-xl md:rounded-b-3xl"
      >
        <div class="flex h-20 items-center justify-between">
          <button class="group flex flex-col items-center gap-1.5 w-14">
            <div class="flex h-10 w-12 items-center justify-center rounded-xl bg-primary/10">
              <span
                class="material-symbols-outlined text-primary"
                style="font-size: 26px; font-variation-settings: 'FILL' 1"
                >manage_search</span
              >
            </div>
          </button>
          <button @click="router.push({ name: 'user-requests' })" class="group flex flex-col items-center gap-1.5 w-14">
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >receipt_long</span
            >
          </button>

          <button @click="router.push({ name: 'user-profile' })" class="group flex flex-col items-center gap-1.5 w-14">
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >account_circle</span
            >
          </button>
        </div>
      </nav>
    </div>

    <!-- Notification Panel -->
    <NotificationPanel 
      v-if="isNotificationPanelOpen"
      @close="closeNotificationPanel"
      @update-count="updateCount"
    />
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
