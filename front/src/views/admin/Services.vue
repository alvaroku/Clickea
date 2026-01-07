<script setup lang="ts">
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/axios'
import { toast } from 'vue3-toastify'

const router = useRouter()

// Estados de carga y datos
const services = ref<any[]>([])
const categories = ref<any[]>([])
const isLoading = ref(true)
const isSaving = ref(false)

// Paginación y Filtros
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 10
})

const searchQuery = ref('')
const statusFilter = ref('all') // 'all', 'active', 'inactive'
const categoryIdFilter = ref<string | number>('all')

// Estado del Modal
const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingService = ref<any>(null)
const form = ref({
  name: '',
  description: '',
  price: 0,
  active: true,
  category_id: null as number | null,
  gender: 'both' as 'male' | 'female' | 'both' | null
})

// Estado del menú de opciones (como en dashboard)
const activeMenuId = ref<number | null>(null)

const fetchCategories = async () => {
  try {
    const response = await api.get('/categories/all')
    categories.value = response.data.data
  } catch (error) {
    console.error('Error fetching categories:', error)
  }
}

const toggleMenu = (id: number) => {
  activeMenuId.value = activeMenuId.value === id ? null : id
}

const fetchServices = async (page = 1) => {
  isLoading.value = true
  try {
    const params: any = { page }
    if (searchQuery.value) params.search = searchQuery.value
    if (statusFilter.value !== 'all') params.status = statusFilter.value
    if (categoryIdFilter.value !== 'all') params.category_id = categoryIdFilter.value

    const response = await api.get('/services', { params })
    services.value = response.data.data.data
    pagination.value = {
      current_page: response.data.data.current_page,
      last_page: response.data.data.last_page,
      total: response.data.data.total,
      per_page: response.data.data.per_page
    }
  } catch (error: any) {
    toast.error('Error al cargar los servicios')
  } finally {
    isLoading.value = false
  }
}

const handleSearch = () => {
  fetchServices(1)
}

const changeStatusFilter = (status: string) => {
  statusFilter.value = status
  fetchServices(1)
}

const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('.menu-container')) {
    activeMenuId.value = null
  }
}

onMounted(() => {
  fetchServices()
  fetchCategories()
  window.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  window.removeEventListener('click', handleClickOutside)
})

const openCreateModal = () => {
  modalMode.value = 'create'
  form.value = { 
    name: '', 
    description: '', 
    price: 0, 
    active: true,
    category_id: null,
    gender: 'both'
  }
  isModalOpen.value = true
}

const openEditModal = (service: any) => {
  modalMode.value = 'edit'
  editingService.value = service
  form.value = {
    name: service.name,
    description: service.description || '',
    price: service.price || 0,
    active: !!service.active,
    category_id: service.category_id,
    gender: service.gender || 'both'
  }
  isModalOpen.value = true
  activeMenuId.value = null
}

const closeModal = () => {
  isModalOpen.value = false
  editingService.value = null
}

const saveService = async () => {
  if (isSaving.value) return
  isSaving.value = true

  try {
    let response
    if (modalMode.value === 'create') {
      response = await api.post('/services', form.value)
    } else {
      response = await api.put(`/services/${editingService.value.id}`, form.value)
    }

    toast.success(response.data.message || 'Operación exitosa')
    closeModal()
    fetchServices(pagination.value.current_page)
  } catch (error: any) {
    const msg = error.response?.data?.message || 'Error al guardar el servicio'
    toast.error(msg)
  } finally {
    isSaving.value = false
  }
}

const toggleStatus = async (service: any) => {
  activeMenuId.value = null
  try {
    const response = await api.patch(`/services/${service.id}/status`)
    toast.success(response.data.message)
    fetchServices(pagination.value.current_page)
  } catch (error: any) {
    toast.error('Error al cambiar el estado')
  }
}

const deleteService = async (id: number) => {
  if (!confirm('¿Estás seguro de eliminar este servicio?')) return

  try {
    await api.delete(`/services/${id}`)
    toast.success('Servicio eliminado')
    fetchServices(pagination.value.current_page)
  } catch (error: any) {
    toast.error('Error al eliminar')
  }
}

const goBack = () => {
  router.push('/')
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
            <button @click="handleLogout" class="flex size-10 items-center justify-center rounded-full hover:bg-slate-50 transition-colors text-slate-400">
              <span class="material-symbols-outlined text-2xl">logout</span>
            </button>
          </div>
        </div>

        <div class="flex flex-col gap-3">
          <div class="relative group">
            <div
              class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"
            >
              <span class="material-symbols-outlined text-text-muted text-[22px]"
                >search</span
              >
            </div>
            <input
              v-model="searchQuery"
              @keyup.enter="handleSearch"
              class="block w-full rounded-xl border-none bg-slate-50 py-3.5 pl-11 pr-14 text-text-main placeholder:text-text-muted focus:ring-0 focus:bg-white focus:shadow-flat transition-all text-sm font-medium"
              placeholder="Buscar por nombre... (Presiona Enter)"
              type="text"
            />
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
              <button 
                @click="handleSearch"
                class="size-8 flex items-center justify-center rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all"
              >
                <span class="material-symbols-outlined text-xl">keyboard_return</span>
              </button>
            </div>
          </div>

          <div class="flex gap-2">
            <select 
              v-model="categoryIdFilter"
              @change="handleSearch"
              class="flex-1 rounded-xl border-none bg-slate-50 text-xs font-bold text-slate-700 focus:ring-1 focus:ring-primary px-4 py-3 appearance-none"
            >
              <option value="all">Todas las categorías</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
            
            <div class="flex items-center gap-1 bg-slate-50 p-1 rounded-xl shrink-0">
              <button
                @click="changeStatusFilter('all')"
                :class="statusFilter === 'all' ? 'bg-white shadow-sm text-primary' : 'text-text-muted'"
                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all"
              >
                Todos
              </button>
              <button
                @click="changeStatusFilter('active')"
                :class="statusFilter === 'active' ? 'bg-white shadow-sm text-accent-success' : 'text-text-muted'"
                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all"
              >
                Activos
              </button>
              <button
                @click="changeStatusFilter('inactive')"
                :class="statusFilter === 'inactive' ? 'bg-white shadow-sm text-accent-danger' : 'text-text-muted'"
                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all"
              >
                Inactivos
              </button>
            </div>
          </div>
        </div>
      </header>

      <main class="flex-1 px-6 space-y-5 pb-28 pt-6 overflow-y-auto no-scrollbar">
        <div class="flex justify-between items-center text-xs font-bold text-slate-400 uppercase tracking-widest">
          <h3>Servicios registrados</h3>
          <span v-if="pagination.total" class="text-primary bg-cyan-50 px-2.5 py-1 rounded-md">
            {{ pagination.total }} {{ pagination.total === 1 ? 'Servicio' : 'Servicios' }}
          </span>
        </div>

        <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 gap-4">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
          <p class="text-slate-400 text-sm font-bold">Cargando servicios...</p>
        </div>

        <div v-else-if="services.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
          <div class="size-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-4xl text-slate-200">inventory_2</span>
          </div>
          <p class="text-slate-400 font-medium">No se encontraron servicios</p>
          <button @click="openCreateModal" class="mt-4 text-primary font-bold text-sm hover:underline">Crear el primero</button>
        </div>

        <div
          v-for="service in services"
          :key="service.id"
          @click="openEditModal(service)"
          class="group relative flex items-center gap-4 rounded-3xl bg-surface-light p-4 shadow-xl shadow-slate-200/50 border-2 border-transparent hover:border-primary/20 transition-all duration-300 cursor-pointer"
        >
          <div
            class="relative shrink-0 overflow-hidden rounded-2xl size-16 bg-slate-50 flex items-center justify-center border border-slate-100"
          >
            <span class="material-symbols-outlined text-slate-400 text-3xl">local_mall</span>
          </div>
          <div class="flex flex-col flex-1 min-w-0">
            <div class="flex justify-between items-start mb-1">
              <p
                class="text-text-main text-base font-bold leading-tight truncate pr-2"
              >
                {{ service.name }}
              </p>
              <div class="flex gap-2">
                <span
                  v-if="service.active"
                  class="inline-flex items-center rounded-md bg-accent-success/10 px-2 py-0.5 text-[10px] font-black text-accent-success uppercase tracking-wider"
                >Visible</span>
                <span
                  v-else
                  class="inline-flex items-center rounded-md bg-accent-danger/10 px-2 py-0.5 text-[10px] font-black text-accent-danger uppercase tracking-wider"
                >Oculto</span>
              </div>
            </div>
            <p class="text-xs text-text-secondary line-clamp-1 mb-2">
              {{ service.description || 'Sin descripción' }}
            </p>
            <div class="flex items-center gap-3">
              <span class="text-sm font-bold text-primary">${{ service.price }}</span>
              <span v-if="service.category" class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md uppercase">
                {{ service.category.name }}
              </span>
              <span class="text-[10px] font-bold text-slate-400 uppercase">
                {{ service.gender === 'male' ? 'Hombre' : service.gender === 'female' ? 'Mujer' : 'Ambos' }}
              </span>
            </div>
          </div>
          
          <!-- Menu Options (Dashboard style) -->
          <div class="relative">
            <button 
              @click.stop="toggleMenu(service.id)"
              class="shrink-0 size-10 flex items-center justify-center rounded-full text-slate-300 hover:text-primary hover:bg-slate-50 transition-colors"
            >
              <span class="material-symbols-outlined text-2xl">more_vert</span>
            </button>
            
            <div v-if="activeMenuId === service.id" 
                 class="absolute right-0 top-10 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 py-2 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
              
              <button @click.stop="openEditModal(service)" class="w-full px-4 py-2.5 text-left text-sm font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px] text-blue-500">edit</span>
                Editar Servicio
              </button>
              
              <button @click.stop="toggleStatus(service)" class="w-full px-4 py-2.5 text-left text-sm font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px]" :class="service.active ? 'text-amber-500' : 'text-emerald-500'">
                  {{ service.active ? 'visibility_off' : 'visibility' }}
                </span>
                {{ service.active ? 'Ocultar' : 'Mostrar' }}
              </button>
              
              <div class="border-t border-slate-50 my-1"></div>
              
              <button @click.stop="deleteService(service.id)" class="w-full px-4 py-2.5 text-left text-sm font-bold text-red-500 hover:bg-red-50 flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px]">delete</span>
                Eliminar
              </button>
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

      <div class="absolute bottom-24 right-6 z-30 md:bottom-28">
        <button
          @click="openCreateModal"
          class="flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white rounded-2xl px-5 py-4 shadow-xl shadow-primary/20 transition-transform active:scale-95 group"
        >
          <span class="material-symbols-outlined text-2xl group-hover:rotate-90 transition-transform">add</span>
          <span class="font-bold text-sm tracking-wide">Nuevo Servicio</span>
        </button>
      </div>

      <nav
        class="absolute bottom-0 z-50 w-full border-t border-slate-100 bg-white/95 pb-8 px-6 backdrop-blur-xl md:rounded-b-3xl"
      >
        <div class="flex h-20 items-center justify-between">
          <button @click="router.push('/')" class="group flex flex-col items-center gap-1.5 w-14">
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >dashboard</span
            >
          </button>
          <button class="group flex flex-col items-center gap-1.5 w-14">
            <div
              class="flex h-10 w-12 items-center justify-center rounded-xl bg-primary/10"
            >
              <span
                class="material-symbols-outlined text-primary"
                style="font-size: 26px; font-variation-settings: 'FILL' 1"
                >inventory_2</span
              >
            </div>
          </button>
          <button
            @click="router.push({ name: 'admin-assigned-services' })"
            class="group flex flex-col items-center gap-1.5 w-14"
          >
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >assignment_turned_in</span
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

      <!-- Modal Creation/Edition -->
      <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center bg-text-main/20 backdrop-blur-[2px]">
        <div 
          @click.self="!isSaving && closeModal()"
          class="absolute inset-0"
          :class="isSaving ? 'cursor-not-allowed' : ''"
        ></div>
        
        <div class="relative w-full max-w-lg bg-white rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-2xl p-8 animate-in fade-in slide-in-from-bottom-10 duration-300">
          <div class="flex items-center justify-between mb-8">
            <h3 class="text-2xl font-black text-text-main tracking-tight">
              {{ modalMode === 'create' ? 'Nuevo Servicio' : 'Editar Servicio' }}
            </h3>
            <button 
              @click="closeModal"
              :disabled="isSaving"
              class="size-10 flex items-center justify-center rounded-full bg-slate-50 text-text-muted hover:bg-slate-100 transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <form @submit.prevent="saveService" class="space-y-6">
            <div class="space-y-1.5">
              <label class="text-xs font-black text-text-secondary uppercase tracking-widest pl-1">Nombre del Servicio</label>
              <input 
                v-model="form.name"
                required
                type="text"
                placeholder="Ej. Corte de Cabello"
                class="w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-text-main placeholder:text-text-muted focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-bold"
              />
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-black text-text-secondary uppercase tracking-widest pl-1">Descripción</label>
              <textarea 
                v-model="form.description"
                rows="3"
                placeholder="Breve descripción de lo que incluye..."
                class="w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-text-main placeholder:text-text-muted focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-bold resize-none"
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xs font-black text-text-secondary uppercase tracking-widest pl-1">Categoría</label>
                <select 
                  v-model="form.category_id"
                  class="w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-text-main focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-bold"
                >
                  <option :value="null">Sin Categoría</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-black text-text-secondary uppercase tracking-widest pl-1">Dirigido a</label>
                <select 
                  v-model="form.gender"
                  class="w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-text-main focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-bold"
                >
                  <option value="both">Ambos (Unisex)</option>
                  <option value="male">Hombre</option>
                  <option value="female">Mujer</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xs font-black text-text-secondary uppercase tracking-widest pl-1">Precio ($)</label>
                <input 
                  v-model.number="form.price"
                  type="number"
                  step="0.01"
                  class="w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-text-main placeholder:text-text-muted focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-bold"
                />
              </div>
              <div class="space-y-1.5 flex flex-col justify-end">
                <label class="text-xs font-black text-text-secondary uppercase tracking-widest pl-1 mb-0.5">Visibilidad</label>
                <div 
                  @click="form.active = !form.active"
                  class="relative flex items-center justify-between w-full h-[60px] rounded-2xl bg-slate-50 px-5 cursor-pointer select-none transition-all duration-300 border-2"
                  :class="form.active ? 'bg-white border-accent-success shadow-md shadow-accent-success/10' : 'bg-slate-50 border-transparent'"
                >
                  <div class="flex flex-col">
                    <span 
                      class="text-[13px] font-black uppercase tracking-wider transition-colors"
                      :class="form.active ? 'text-accent-success' : 'text-text-muted'"
                    >
                      {{ form.active ? 'Disponible' : 'Oculto' }}
                    </span>
                    <span class="text-[10px] font-medium text-text-secondary opacity-60">
                      {{ form.active ? 'Visible en catálogo' : 'No se mostrará' }}
                    </span>
                  </div>
                  
                  <div 
                    class="relative w-12 h-6 rounded-full transition-all duration-500 flex items-center px-1"
                    :class="form.active ? 'bg-accent-success' : 'bg-slate-300'"
                  >
                    <div 
                      class="bg-white w-4 h-4 rounded-full shadow-lg transition-transform duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] transform"
                      :class="form.active ? 'translate-x-6' : 'translate-x-0'"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="pt-4 flex gap-3">
              <button 
                type="button" 
                @click="closeModal"
                :disabled="isSaving"
                class="flex-1 py-4.5 rounded-2xl font-black uppercase tracking-widest text-text-muted hover:bg-slate-50 transition-all text-sm disabled:opacity-30 disabled:cursor-not-allowed"
              >
                Cancelar
              </button>
              <button 
                type="submit"
                :disabled="isSaving"
                class="flex-[2] bg-primary hover:bg-primary-hover text-white py-4.5 rounded-2xl font-black uppercase tracking-widest transition-all shadow-lg shadow-primary/25 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm"
              >
                <span v-if="isSaving" class="material-symbols-outlined animate-spin">sync</span>
                <span v-if="!isSaving">{{ modalMode === 'create' ? 'Crear Servicio' : 'Guardar Cambios' }}</span>
                <span v-else>Guardando...</span>
              </button>
            </div>
          </form>
        </div>
      </div>
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
