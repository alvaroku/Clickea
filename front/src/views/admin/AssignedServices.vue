<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/axios'
import { toast } from 'vue3-toastify'

const router = useRouter()
const requests = ref<any[]>([])
const categories = ref<any[]>([])
const isLoading = ref(true)
const searchTerm = ref('')
const selectedCategoryId = ref<string | number>('all')
const selectedStatus = ref('all')
const selectedValidity = ref('all')
const selectedImage = ref<string | null>(null)
const quoteModalVisible = ref(false)
const selectedRequest = ref<any>(null)
const isSubmitting = ref(false)

const quoteForm = ref({
  price: '',
  provider_observations: ''
})

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

const fetchRequests = async (page = 1) => {
  isLoading.value = true
  try {
    const params: any = { page }
    if (searchTerm.value) params.search = searchTerm.value
    if (selectedCategoryId.value !== 'all') params.category_id = selectedCategoryId.value
    if (selectedStatus.value !== 'all') params.status = selectedStatus.value
    if (selectedValidity.value !== 'all') params.validity_status = selectedValidity.value

    const response = await api.get('/service-requests/provider', { params })
    requests.value = response.data.data.data
    pagination.value = {
      current_page: response.data.data.current_page,
      last_page: response.data.data.last_page,
      total: response.data.data.total
    }
  } catch (error: any) {
    toast.error('Error al cargar las solicitudes asignadas')
  } finally {
    isLoading.value = false
  }
}

const isVigente = (date: string, time: string) => {
  const now = new Date()
  const appt = new Date(`${date} ${time}`)
  return appt > now
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'pendiente': return 'bg-amber-100 text-amber-700 border-amber-200'
    case 'cotizada': return 'bg-blue-100 text-blue-700 border-blue-200'
    case 'contratada': return 'bg-green-100 text-green-700 border-green-200'
    case 'rechazada': return 'bg-red-100 text-red-700 border-red-200'
    case 'cancelada': return 'bg-slate-100 text-slate-500 border-slate-200'
    default: return 'bg-gray-100 text-gray-700 border-gray-200'
  }
}

const getImageUrl = (image: any) => {
  if (!image) return ''
  return `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/storage/${image.path}`
}

const openQuoteModal = (q: any) => {
  selectedRequest.value = q
  quoteForm.value.price = q.service_request?.service?.price || ''
  quoteForm.value.provider_observations = ''
  quoteModalVisible.value = true
}

const submitQuote = async () => {
  if (!selectedRequest.value) return
  isSubmitting.value = true
  try {
    await api.put(`/quotations/${selectedRequest.value.id}`, quoteForm.value)
    toast.success('Cotización enviada correctamente')
    quoteModalVisible.value = false
    fetchRequests(pagination.value.current_page)
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Error al enviar cotización')
  } finally {
    isSubmitting.value = false
  }
}

const rejectRequest = async (id: number) => {
  if (!confirm('¿Estás seguro de que deseas rechazar esta solicitud?')) return
  try {
    // We can use the same update endpoint with status rejected or a specific one
    await api.put(`/quotations/${id}`, { status: 'rechazada' })
    toast.success('Solicitud rechazada')
    fetchRequests(pagination.value.current_page)
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Error al rechazar solicitud')
  }
}

const handleLogout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push({ name: 'login' })
}

onMounted(() => {
  fetchRequests()
  fetchCategories()
})
</script>

<template>
  <div class="bg-slate-50 min-h-screen font-display text-text-main antialiased md:py-12">
    <div
      class="relative flex h-full md:h-[calc(100vh-6rem)] min-h-screen md:min-h-0 w-full flex-col overflow-x-hidden max-w-2xl mx-auto bg-white md:shadow-2xl md:rounded-3xl shadow-subtle border border-slate-100"
    >
      <header
        class="sticky top-0 z-20 bg-white/95 backdrop-blur-md border-b border-slate-100 md:rounded-t-3xl"
      >
        <div class="flex items-center justify-between px-6 py-4">
          <button @click="router.back()" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400">
            <span class="material-symbols-outlined">arrow_back</span>
          </button>
          <h2 class="text-xl font-extrabold text-slate-900">Solicitudes Asignadas</h2>
          <div class="w-10"></div>
        </div>

        <!-- Filters -->
        <div class="px-6 pb-4">
          <div class="flex gap-2 overflow-x-auto no-scrollbar pb-2">
            <div class="relative min-w-[140px]">
              <select v-model="selectedStatus" @change="fetchRequests(1)" class="w-full h-10 pl-3 pr-8 text-xs font-bold bg-slate-50 border-none rounded-xl focus:ring-primary/20 appearance-none">
                <option value="all">Todos los estados</option>
                <option value="pendiente">Pendientes</option>
                <option value="cotizada">Cotizadas</option>
                <option value="contratada">Contratadas</option>
                <option value="cancelada">Canceladas</option>
                <option value="rechazada">Rechazadas</option>
              </select>
              <span class="material-symbols-outlined absolute right-2 top-2.5 text-slate-400 pointer-events-none" style="font-size: 18px">expand_more</span>
            </div>

            <div class="relative min-w-[140px]">
              <select v-model="selectedValidity" @change="fetchRequests(1)" class="w-full h-10 pl-3 pr-8 text-xs font-bold bg-slate-50 border-none rounded-xl focus:ring-primary/20 appearance-none">
                <option value="all">Toda vigencia</option>
                <option value="vigente">Vigentes</option>
                <option value="finalizada">Finalizadas</option>
              </select>
              <span class="material-symbols-outlined absolute right-2 top-2.5 text-slate-400 pointer-events-none" style="font-size: 18px">schedule</span>
            </div>
          </div>
          
          <div class="mt-2 flex w-full items-center rounded-xl bg-slate-50 border border-slate-100 focus-within:ring-1 focus-within:ring-primary/20 transition-all">
             <span class="material-symbols-outlined ml-3 text-slate-400" style="font-size: 20px">search</span>
             <input v-model="searchTerm" @keyup.enter="fetchRequests(1)" placeholder="Buscar por servicio o descripción..." class="h-10 w-full border-none bg-transparent px-3 text-sm font-medium focus:ring-0" />
          </div>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto no-scrollbar pb-32">
        <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 gap-4">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
          <p class="text-slate-400 text-sm font-bold">Cargando solicitudes...</p>
        </div>

        <div v-else-if="requests.length === 0" class="flex flex-col items-center justify-center py-20 px-10 text-center">
          <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-4xl text-slate-300">fact_check</span>
          </div>
          <p class="text-slate-500 font-bold">No tienes solicitudes asignadas en este momento</p>
        </div>

        <div v-else class="flex flex-col gap-4 p-6">
          <div v-for="q in requests" :key="q.id" class="bg-white border border-slate-100 rounded-[1.5rem] shadow-sm overflow-hidden transition-all hover:shadow-md">
            <div class="p-5">
              <div class="flex justify-between items-start mb-4">
                <div class="flex gap-3">
                  <div class="h-12 w-12 rounded-xl bg-primary/5 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">person</span>
                  </div>
                  <div>
                    <h3 class="font-black text-slate-900 leading-tight">{{ q.service_request?.service?.name }}</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cliente: {{ q.service_request?.client?.name }}</p>
                  </div>
                </div>
                <div class="flex flex-col items-end gap-1">
                  <span :class="getStatusColor(q.status)" class="px-3 py-1 rounded-lg text-[10px] font-black uppercase border tracking-tight">
                    {{ q.status }}
                  </span>
                  <span :class="isVigente(q.service_request?.date, q.service_request?.time) ? 'bg-cyan-50 text-cyan-600' : 'bg-slate-100 text-slate-400'" class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase">
                    {{ isVigente(q.service_request?.date, q.service_request?.time) ? 'Vigente' : 'Finalizada' }}
                  </span>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4 py-3 border-y border-slate-50">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-slate-300" style="font-size: 18px">calendar_today</span>
                  <span class="text-xs font-bold text-slate-600">{{ q.service_request?.date }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-slate-300" style="font-size: 18px">schedule</span>
                  <span class="text-xs font-bold text-slate-600">{{ q.service_request?.time }}</span>
                </div>
              </div>

              <div class="mt-3 space-y-2">
                <div class="flex items-start gap-2">
                  <span class="material-symbols-outlined text-slate-300" style="font-size: 18px">location_on</span>
                  <p class="text-xs text-slate-600 font-semibold">{{ q.service_request?.location }}</p>
                </div>
                <div v-if="q.service_request?.observations" class="bg-slate-50 p-3 rounded-xl border border-dashed border-slate-200">
                  <p class="text-xs text-slate-500 italic">"{{ q.service_request?.observations }}"</p>
                </div>
              </div>

              <!-- Calificación del Cliente -->
              <div v-if="q.rating" class="mt-4 p-4 bg-amber-50 rounded-2xl border border-amber-100">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-[10px] font-black text-amber-600 uppercase">Calificación del Cliente</p>
                  <div class="flex gap-0.5">
                    <span v-for="i in 5" :key="i" class="material-symbols-outlined text-[16px]" :class="i <= q.rating ? 'text-amber-400' : 'text-slate-200'" style="font-variation-settings: 'FILL' 1">star</span>
                  </div>
                </div>
                <p v-if="q.rating_comment" class="text-xs text-slate-600 italic">"{{ q.rating_comment }}"</p>
              </div>

              <!-- Imagen de referencia -->
              <div v-if="q.service_request?.image" class="mt-4">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-2">Imagen de referencia:</p>
                <div 
                  @click="selectedImage = getImageUrl(q.service_request?.image)"
                  class="relative h-24 w-40 rounded-xl overflow-hidden cursor-pointer group"
                >
                  <img alt="service" :src="getImageUrl(q.service_request?.image)" class="h-full w-full object-cover transition-transform group-hover:scale-110" />
                  <div class="absolute inset-0 bg-black/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="material-symbols-outlined text-white">zoom_in</span>
                  </div>
                </div>
              </div>

              <!-- Provider Actions -->
              <div class="mt-5 space-y-2">
                <div v-if="q.status === 'cotizada' || q.status === 'aceptada'" class="w-full bg-blue-50 p-3 rounded-xl border border-blue-100">
                  <div class="flex justify-between items-center mb-1">
                    <p class="text-[10px] font-black text-blue-400 uppercase">{{ q.status === 'aceptada' ? 'Cotización Aceptada' : 'Cotización Enviada' }}</p>
                    <span class="text-sm font-black text-blue-700">${{ q.price }}</span>
                  </div>
                  <p v-if="q.provider_observations" class="text-[11px] text-blue-600 leading-tight">"{{ q.provider_observations }}"</p>
                </div>
                
                <div v-if="isVigente(q.service_request?.date, q.service_request?.time) && q.status === 'pendiente'" class="flex gap-2">
                  <button 
                    @click="openQuoteModal(q)"
                    class="flex-1 h-10 bg-primary text-white rounded-xl text-xs font-black uppercase shadow-lg shadow-primary/20 transition-transform active:scale-95"
                  >
                    Enviar Cotización
                  </button>
                  <button 
                    @click="rejectRequest(q.id)"
                    class="flex-1 h-10 bg-red-50 text-red-500 border border-red-100 rounded-xl text-xs font-black uppercase transition-transform active:scale-95"
                  >
                    Rechazar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      <!-- Modal de Imagen -->
      <div v-if="selectedImage" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-sm" @click="selectedImage = null">
        <button class="absolute top-6 right-6 text-white text-3xl">
          <span class="material-symbols-outlined">close</span>
        </button>
        <img :src="selectedImage" class="max-w-full max-h-full rounded-2xl shadow-2xl animate-in zoom-in duration-300" @click.stop />
      </div>

      <!-- Modal de Cotización -->
      <div v-if="quoteModalVisible" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
        <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl p-6 animate-in slide-in-from-bottom-10 duration-300">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-slate-900">Enviar Cotización</h3>
            <button @click="quoteModalVisible = false" class="text-slate-400 hover:text-slate-600">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <form @submit.prevent="submitQuote" class="space-y-4">
            <div>
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1">Costo del servicio</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                <input 
                  v-model="quoteForm.price"
                  type="number" 
                  step="0.01" 
                  required
                  class="w-full h-12 pl-8 pr-4 rounded-xl border-slate-100 bg-slate-50 focus:border-primary focus:ring-primary/20 font-bold"
                  placeholder="0.00"
                />
              </div>
            </div>

            <div>
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1">Observaciones (Opcional)</label>
              <textarea 
                v-model="quoteForm.provider_observations"
                rows="3"
                class="w-full rounded-xl border-slate-100 bg-slate-50 focus:border-primary focus:ring-primary/20 font-medium text-sm p-4"
                placeholder="Detalles sobre el costo o el servicio..."
              ></textarea>
            </div>

            <button 
              type="submit"
              :disabled="isSubmitting"
              class="w-full h-14 bg-primary text-white rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-primary/20 active:scale-95 transition-all disabled:opacity-50"
            >
              <span v-if="isSubmitting" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
              <span v-else>Enviar al Cliente</span>
              <span v-if="!isSubmitting" class="material-symbols-outlined">send</span>
            </button>
          </form>
        </div>
      </div>

      <nav
        class="absolute bottom-0 z-50 w-full border-t border-slate-100 bg-white/95 pb-8 px-6 backdrop-blur-xl md:rounded-b-3xl"
      >
        <div class="flex h-20 items-center justify-between">
          <button
            @click="router.push({ name: 'admin-services' })"
            class="group flex flex-col items-center gap-1.5 w-14"
          >
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >inventory_2</span
            >
          </button>
          <button class="group flex flex-col items-center gap-1.5 w-14">
            <div
              class="flex h-10 w-12 items-center justify-center rounded-xl bg-primary/10"
            >
              <span
                class="material-symbols-outlined text-primary"
                style="font-size: 26px; font-variation-settings: 'FILL' 1"
                >assignment_turned_in</span
              >
            </div>
          </button>
         <button @click="router.push({ name: 'admin-profile' })" class="group flex flex-col items-center gap-1.5 w-14">
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >account_circle</span
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
