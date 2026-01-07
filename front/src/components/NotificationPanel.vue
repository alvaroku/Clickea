<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '../services/axios'
import { toast } from 'vue3-toastify'

interface Notification {
  id: number
  title: string
  message: string
  additional_data: any
  is_read: boolean
  created_at: string
}

const emit = defineEmits(['close', 'updateCount'])

const notifications = ref<Notification[]>([])
const pagination = ref<any>({})
const isLoading = ref(true)
const searchTerm = ref('')
const filterRead = ref<'all' | 'unread' | 'read'>('all')

const fetchNotifications = async (page = 1) => {
  isLoading.value = true
  try {
    const params: any = { page }
    if (searchTerm.value) params.search = searchTerm.value
    if (filterRead.value !== 'all') {
      params.is_read = filterRead.value === 'read'
    }

    const response = await api.get('/notifications', { params })
    notifications.value = response.data.data.data
    pagination.value = response.data.data
  } catch (error: any) {
    toast.error('Error al cargar notificaciones')
  } finally {
    isLoading.value = false
  }
}

const markAsRead = async (notification: Notification) => {
  if (notification.is_read) return

  try {
    await api.patch(`/notifications/${notification.id}/read`)
    notification.is_read = true
    emit('updateCount')
  } catch (error: any) {
    toast.error('Error al marcar como leída')
  }
}

const markAllAsRead = async () => {
  try {
    const response = await api.patch('/notifications/mark-all-read')
    toast.success(response.data.message)
    fetchNotifications(pagination.value.current_page)
    emit('updateCount')
  } catch (error: any) {
    toast.error('Error al marcar todas como leídas')
  }
}

const deleteNotification = async (notification: Notification) => {
  if (!confirm('¿Eliminar esta notificación?')) return

  try {
    await api.delete(`/notifications/${notification.id}`)
    notifications.value = notifications.value.filter(n => n.id !== notification.id)
    toast.success('Notificación eliminada')
    emit('updateCount')
  } catch (error: any) {
    toast.error('Error al eliminar')
  }
}

const deleteAllRead = async () => {
  if (!confirm('¿Eliminar todas las notificaciones leídas?')) return

  try {
    const response = await api.delete('/notifications/read/all')
    toast.success(response.data.message)
    fetchNotifications(1)
    emit('updateCount')
  } catch (error: any) {
    toast.error('Error al eliminar')
  }
}

const formatDate = (date: string) => {
  const d = new Date(date)
  const now = new Date()
  const diff = now.getTime() - d.getTime()
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 1) return 'Ahora'
  if (minutes < 60) return `Hace ${minutes}m`
  if (hours < 24) return `Hace ${hours}h`
  if (days < 7) return `Hace ${days}d`
  return d.toLocaleDateString()
}

const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.is_read).length
})

onMounted(() => {
  fetchNotifications()
})
</script>

<template>
  <div class="fixed inset-0 z-[200] flex items-end md:items-center justify-center bg-slate-900/60 backdrop-blur-sm">
    <div @click.self="emit('close')" class="absolute inset-0"></div>
    
    <div class="relative w-full max-w-2xl bg-white rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-2xl flex flex-col max-h-[90vh] animate-in slide-in-from-bottom duration-300">
      <!-- Header -->
      <div class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-slate-100 px-6 pt-6 pb-4 md:rounded-t-[2.5rem]">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-black text-slate-900 tracking-tight">
            Notificaciones
            <span v-if="unreadCount > 0" class="ml-2 text-sm font-bold text-primary bg-primary/10 px-2.5 py-1 rounded-lg">
              {{ unreadCount }}
            </span>
          </h2>
          <button 
            @click="emit('close')"
            class="size-10 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 transition-colors"
          >
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <!-- Search and Filters -->
        <div class="space-y-3">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
              <span class="material-symbols-outlined text-slate-400 text-[22px]">search</span>
            </div>
            <input
              v-model="searchTerm"
              @keyup.enter="fetchNotifications(1)"
              class="block w-full rounded-xl border-none bg-slate-50 py-3 pl-11 pr-4 text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm font-medium"
              placeholder="Buscar notificaciones..."
              type="text"
            />
          </div>

          <div class="flex gap-2 items-center justify-between">
            <div class="flex gap-1 bg-slate-50 p-1 rounded-xl">
              <button
                @click="filterRead = 'all'; fetchNotifications(1)"
                :class="filterRead === 'all' ? 'bg-white shadow-sm text-primary' : 'text-slate-400'"
                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all"
              >
                Todas
              </button>
              <button
                @click="filterRead = 'unread'; fetchNotifications(1)"
                :class="filterRead === 'unread' ? 'bg-white shadow-sm text-primary' : 'text-slate-400'"
                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all"
              >
                No leídas
              </button>
              <button
                @click="filterRead = 'read'; fetchNotifications(1)"
                :class="filterRead === 'read' ? 'bg-white shadow-sm text-slate-400' : 'text-slate-400'"
                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase transition-all"
              >
                Leídas
              </button>
            </div>

            <div class="flex gap-2">
              <button
                v-if="unreadCount > 0"
                @click="markAllAsRead"
                class="text-xs font-bold text-primary hover:text-primary/80 transition-colors px-2"
              >
                Marcar todas leídas
              </button>
              <button
                @click="deleteAllRead"
                class="text-xs font-bold text-red-500 hover:text-red-600 transition-colors px-2"
              >
                Eliminar leídas
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="flex-1 overflow-y-auto px-6 py-4 space-y-3">
        <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 gap-4">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
          <p class="text-slate-400 text-sm font-bold">Cargando...</p>
        </div>

        <div v-else-if="notifications.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
          <span class="material-symbols-outlined text-6xl text-slate-200 mb-2">notifications_off</span>
          <p class="text-slate-500 font-bold">No hay notificaciones</p>
        </div>

        <div
          v-for="notification in notifications"
          :key="notification.id"
          @click="markAsRead(notification)"
          class="group relative flex gap-3 rounded-2xl p-4 border-2 transition-all cursor-pointer"
          :class="notification.is_read 
            ? 'bg-slate-50 border-slate-100 hover:border-slate-200' 
            : 'bg-primary/5 border-primary/20 hover:border-primary/30'"
        >
          <div class="shrink-0">
            <div class="size-10 rounded-full flex items-center justify-center" 
              :class="notification.is_read ? 'bg-slate-200' : 'bg-primary/20'">
              <span class="material-symbols-outlined text-xl" 
                :class="notification.is_read ? 'text-slate-500' : 'text-primary'">
                {{ notification.is_read ? 'notifications' : 'notifications_active' }}
              </span>
            </div>
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2 mb-1">
              <h3 class="font-black text-slate-900 text-sm leading-tight">
                {{ notification.title }}
              </h3>
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0">
                {{ formatDate(notification.created_at) }}
              </span>
            </div>
            <p class="text-sm text-slate-600 leading-snug mb-2">
              {{ notification.message }}
            </p>
            
            <div v-if="notification.additional_data" class="flex flex-wrap gap-1 text-[10px]">
              <span v-if="notification.additional_data.service_name" 
                class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md font-bold">
                {{ notification.additional_data.service_name }}
              </span>
              <span v-if="notification.additional_data.date" 
                class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md font-bold">
                {{ notification.additional_data.date }}
              </span>
              <span v-if="notification.additional_data.time" 
                class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md font-bold">
                {{ notification.additional_data.time }}
              </span>
            </div>
          </div>

          <button
            @click.stop="deleteNotification(notification)"
            class="shrink-0 size-8 flex items-center justify-center rounded-full text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all opacity-0 group-hover:opacity-100"
          >
            <span class="material-symbols-outlined text-xl">delete</span>
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-2 pt-4 pb-2">
          <button 
            @click="fetchNotifications(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="size-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined text-xl">chevron_left</span>
          </button>
          
          <div class="bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold text-slate-900">
            <span class="text-primary">{{ pagination.current_page }}</span>
            <span class="text-slate-300 mx-1">/</span>
            <span>{{ pagination.last_page }}</span>
          </div>

          <button 
            @click="fetchNotifications(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="size-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined text-xl">chevron_right</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.slide-in-from-bottom {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}

@media (min-width: 768px) {
  .slide-in-from-bottom {
    animation: fadeIn 0.3s ease-out;
  }
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
