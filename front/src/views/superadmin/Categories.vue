<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/axios'
import { toast } from 'vue3-toastify'
import NotificationPanel from '../../components/NotificationPanel.vue'
import NotificationButton from '../../components/NotificationButton.vue'
import { useNotifications } from '../../composables/useNotifications'

const router = useRouter()
const { unreadCount, isNotificationPanelOpen, openNotificationPanel, closeNotificationPanel, updateCount } = useNotifications()

// Estados
const categories = ref<any[]>([])
const isLoading = ref(true)
const isSaving = ref(false)
const activeMenuId = ref<number | null>(null)

// Paginación y Filtros
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
})
const searchTerm = ref('')
const statusFilter = ref('all')

// Guardar/Editar
const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingCategory = ref<any>(null)
const form = ref({
  name: '',
  description: '',
  active: true
})

const fetchCategories = async (page = 1) => {
  isLoading.value = true
  try {
    const params: any = { page }
    if (searchTerm.value) params.search = searchTerm.value
    if (statusFilter.value !== 'all') params.status = statusFilter.value

    const response = await api.get('/categories', { params })
    categories.value = response.data.data.data
    pagination.value = {
      current_page: response.data.data.current_page,
      last_page: response.data.data.last_page,
      total: response.data.data.total
    }
  } catch (error: any) {
    toast.error('Error al cargar las categorías')
  } finally {
    isLoading.value = false
  }
}

const openCreateModal = () => {
  modalMode.value = 'create'
  form.value = { name: '', description: '', active: true }
  isModalOpen.value = true
}

const openEditModal = (category: any) => {
  modalMode.value = 'edit'
  editingCategory.value = category
  form.value = {
    name: category.name,
    description: category.description || '',
    active: !!category.active
  }
  isModalOpen.value = true
  activeMenuId.value = null
}

const saveCategory = async () => {
  if (isSaving.value) return
  isSaving.value = true

  try {
    let response
    if (modalMode.value === 'create') {
      response = await api.post('/categories', form.value)
    } else {
      response = await api.put(`/categories/${editingCategory.value.id}`, form.value)
    }

    toast.success(response.data.message)
    isModalOpen.value = false
    fetchCategories(pagination.value.current_page)
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Error al guardar')
  } finally {
    isSaving.value = false
  }
}

const toggleStatus = async (category: any) => {
  activeMenuId.value = null
  try {
    const response = await api.patch(`/categories/${category.id}/status`)
    toast.success(response.data.message)
    fetchCategories(pagination.value.current_page)
  } catch (error: any) {
    toast.error('Error al cambiar el estado')
  }
}

const deleteCategory = async (id: number) => {
  activeMenuId.value = null
  if (!confirm('¿Estás seguro de eliminar esta categoría?')) return

  try {
    await api.delete(`/categories/${id}`)
    toast.success('Categoría eliminada')
    fetchCategories(1)
  } catch (error: any) {
    toast.error('Error al eliminar')
  }
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}

onMounted(() => {
  fetchCategories()
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 md:flex md:items-center md:justify-center md:bg-slate-200 p-0 md:p-4 font-sans text-slate-900">
    <div class="w-full max-w-2xl mx-auto bg-white min-h-screen md:min-h-[850px] md:h-[850px] md:rounded-[3rem] shadow-2xl overflow-hidden flex flex-col relative border border-slate-100">
      
      <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-sm border-b border-slate-100 px-6 pt-4 pb-4 md:rounded-t-[3rem]">
        <div class="flex items-center justify-between mb-4">
          <button @click="router.push('/')" class="text-slate-900 flex size-10 shrink-0 items-center justify-center rounded-full hover:bg-slate-50 transition-colors">
            <span class="material-symbols-outlined text-2xl">arrow_back</span>
          </button>
          <h2 class="text-slate-900 text-xl font-bold tracking-tight">
            Gestión de Categorías
          </h2>
          <NotificationButton :unreadCount="unreadCount" @click="openNotificationPanel" />
        </div>
        
        <div class="flex gap-2">
          <div class="relative flex-1 group">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
              <span class="material-symbols-outlined text-slate-400 text-[22px]">search</span>
            </div>
            <input
              v-model="searchTerm"
              @keyup.enter="fetchCategories(1)"
              class="block w-full rounded-xl border-none bg-slate-50 py-3.5 pl-11 pr-4 text-slate-900 placeholder:text-slate-400 focus:ring-1 focus:ring-primary focus:bg-white transition-all text-sm font-medium"
              placeholder="Buscar categorías... (Enter)"
              type="text"
            />
          </div>
          <select 
            v-model="statusFilter"
            @change="fetchCategories(1)"
            class="rounded-xl border-none bg-slate-50 text-sm font-bold text-slate-700 focus:ring-1 focus:ring-primary px-4"
          >
            <option value="all">Todas</option>
            <option value="active">Activas</option>
            <option value="inactive">Inactivas</option>
          </select>
        </div>
      </header>

      <main class="flex-1 px-6 space-y-5 pb-28 pt-6 overflow-y-auto no-scrollbar">
        <div class="flex justify-between items-center text-xs font-bold text-slate-400 uppercase tracking-widest">
          <h3>Categorías registradas</h3>
          <span v-if="pagination.total" class="text-primary bg-cyan-50 px-2.5 py-1 rounded-md">
            {{ pagination.total }} total
          </span>
        </div>

        <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 gap-4">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
          <p class="text-slate-400 text-sm">Cargando categorías...</p>
        </div>

        <div v-else-if="categories.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
          <span class="material-symbols-outlined text-6xl text-slate-200 mb-2">category</span>
          <p class="text-slate-500 font-bold">No hay categorías</p>
        </div>

        <div v-for="category in categories" :key="category.id" 
          class="group relative flex items-center gap-4 rounded-3xl bg-white p-4 shadow-sm border border-slate-50 hover:border-slate-200 transition-all cursor-pointer"
          @click="openEditModal(category)">
          
          <div class="shrink-0 flex items-center justify-center rounded-2xl size-14 bg-slate-100 text-slate-400 border border-slate-200">
            <span class="material-symbols-outlined text-3xl">category</span>
          </div>

          <div class="flex flex-col flex-1 min-w-0">
            <p class="text-slate-900 text-base font-bold truncate">
              {{ category.name }}
            </p>
            <p class="text-xs text-slate-500 truncate pb-1">
              {{ category.description || 'Sin descripción' }}
            </p>
            <span class="inline-flex w-fit items-center rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-wider"
                  :class="category.active ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'">
              {{ category.active ? 'Activa' : 'Inactiva' }}
            </span>
          </div>

          <div class="relative menu-container">
            <button @click.stop="activeMenuId = activeMenuId === category.id ? null : category.id"
                    class="size-10 flex items-center justify-center rounded-full text-slate-300 hover:text-primary transition-colors">
              <span class="material-symbols-outlined">more_vert</span>
            </button>
            
            <div v-if="activeMenuId === category.id" class="absolute right-0 top-10 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 py-2">
              <button @click.stop="openEditModal(category)" class="w-full px-4 py-2.5 text-left text-sm font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px] text-blue-500">edit</span>
                Editar
              </button>
              <button @click.stop="toggleStatus(category)" class="w-full px-4 py-2.5 text-left text-sm font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px]" :class="category.active ? 'text-amber-500' : 'text-emerald-500'">
                  {{ category.active ? 'visibility_off' : 'visibility' }}
                </span>
                {{ category.active ? 'Desactivar' : 'Activar' }}
              </button>
              <div class="border-t border-slate-50 my-1"></div>
              <button @click.stop="deleteCategory(category.id)" class="w-full px-4 py-2.5 text-left text-sm font-bold text-rose-500 hover:bg-rose-50 flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px]">delete</span>
                Eliminar
              </button>
            </div>
          </div>
        </div>

        <!-- Paginación -->
        <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-2 pt-6 pb-10">
          <button 
            @click="fetchCategories(1)"
            :disabled="pagination.current_page === 1"
            class="size-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 disabled:grayscale transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined">first_page</span>
          </button>
          <button 
            @click="fetchCategories(pagination.current_page - 1)"
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
            @click="fetchCategories(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="size-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
          <button 
            @click="fetchCategories(pagination.last_page)"
            :disabled="pagination.current_page === pagination.last_page"
            class="size-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 disabled:grayscale transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined">last_page</span>
          </button>
        </div>
      </main>

      <div class="absolute bottom-32 right-6 z-30 md:bottom-36">
        <button @click="openCreateModal" class="flex items-center justify-center gap-3 bg-primary hover:bg-primary/90 text-white rounded-2xl h-14 px-6 shadow-xl shadow-primary/20 transition-transform active:scale-95 group">
          <span class="material-symbols-outlined text-2xl group-hover:rotate-90 transition-transform">add</span>
          <span class="font-bold text-sm tracking-wide">Nueva Categoría</span>
        </button>
      </div>

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
          <button class="group flex flex-col items-center gap-1.5 w-14">
            <div
              class="flex h-10 w-12 items-center justify-center rounded-xl bg-primary/10"
            >
              <span
                class="material-symbols-outlined text-primary"
                style="font-size: 26px; font-variation-settings: 'FILL' 1"
                >category</span
              >
            </div>
          </button>
          <button @click="router.push({ name: 'superadmin-profile' })" class="group flex flex-col items-center gap-1.5 w-14">
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >account_circle</span
            >
          </button>
        </div>
      </nav>

      <!-- Modal -->
      <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center bg-slate-900/40 backdrop-blur-sm p-0 md:p-4">
        <div 
          @click.self="!isSaving && (isModalOpen = false)"
          class="absolute inset-0"
          :class="isSaving ? 'cursor-not-allowed' : ''"
        ></div>
        
        <div class="relative w-full max-w-lg bg-white rounded-t-[2.5rem] md:rounded-[3rem] p-8 pb-12 animate-in slide-in-from-bottom">
          <div class="flex items-center justify-between mb-8">
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">
              {{ modalMode === 'create' ? 'Nueva Categoría' : 'Editar Categoría' }}
            </h3>
            <button 
              @click="isModalOpen = false" 
              :disabled="isSaving"
              class="size-10 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <form @submit.prevent="saveCategory" class="space-y-6">
            <div class="space-y-1">
              <label class="text-xs font-black text-slate-400 uppercase tracking-widest pl-1">Nombre</label>
              <input v-model="form.name" required type="text" class="w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-slate-900 font-bold focus:ring-2 focus:ring-primary/20" />
            </div>
            <div class="space-y-1">
              <label class="text-xs font-black text-slate-400 uppercase tracking-widest pl-1">Descripción</label>
              <textarea v-model="form.description" rows="3" class="w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-slate-900 font-bold focus:ring-2 focus:ring-primary/20 resize-none"></textarea>
            </div>
            
            <div class="flex gap-3 pt-4">
              <button 
                type="button" 
                @click="isModalOpen = false" 
                :disabled="isSaving"
                class="flex-1 h-14 rounded-2xl font-bold text-slate-500 hover:bg-slate-50 transition-all uppercase tracking-widest text-xs disabled:opacity-30 disabled:cursor-not-allowed"
              >
                Cancelar
              </button>
              <button type="submit" :disabled="isSaving" class="flex-[2] h-14 rounded-2xl bg-primary text-white font-black uppercase tracking-widest text-xs shadow-lg shadow-primary/20 disabled:opacity-50">
                {{ isSaving ? 'Guardando...' : (modalMode === 'create' ? 'Crear' : 'Guardar') }}
              </button>
            </div>
          </form>
        </div>
      </div>
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
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.animate-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.slide-in-from-bottom { animation: slideIn 0.3s ease-out; }
@keyframes slideIn { from { transform: translateY(100%); } to { transform: translateY(0); } }
</style>
