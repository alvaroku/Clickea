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

    const response = await api.get('/service-requests', { params })
    requests.value = response.data.data.data
    pagination.value = {
      current_page: response.data.data.current_page,
      last_page: response.data.data.last_page,
      total: response.data.data.total
    }
  } catch (error: any) {
    toast.error('Error al cargar las solicitudes')
  } finally {
    isLoading.value = false
  }
}

const quotationsModalVisible = ref(false)
const selectedRequestForQuotations = ref<any>(null)
const quotations = ref<any[]>([])
const isLoadingQuotations = ref(false)

const openQuotationsModal = async (req: any) => {
  selectedRequestForQuotations.value = req
  quotationsModalVisible.value = true
  isLoadingQuotations.value = true
  try {
    const response = await api.get(`/service-requests/${req.id}/quotations`)
    quotations.value = response.data.data
  } catch (error) {
    toast.error('Error al cargar las cotizaciones')
  } finally {
    isLoadingQuotations.value = false
  }
}

const acceptQuotation = async (quotationId: number) => {
  try {
    await api.post(`/quotations/${quotationId}/accept`)
    toast.success('Cotización aceptada correctamente')
    quotationsModalVisible.value = false
    fetchRequests(pagination.value.current_page)
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Error al aceptar cotización')
  }
}

const rejectQuotation = async (quotationId: number) => {
  try {
    if (!confirm('¿Estás seguro de que deseas rechazar esta cotización?')) return
    await api.post(`/quotations/${quotationId}/reject`)
    toast.success('Cotización rechazada')
    if (selectedRequestForQuotations.value) {
      openQuotationsModal(selectedRequestForQuotations.value)
    }
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Error al rechazar cotización')
  }
}

const ratingModalVisible = ref(false)
const selectedQuotationForRating = ref<any>(null)
const isSubmittingRating = ref(false)
const ratingForm = ref({
  rating: 5,
  rating_comment: ''
})

const openRatingModal = (quotation: any) => {
  selectedQuotationForRating.value = quotation
  ratingForm.value.rating = 5
  ratingForm.value.rating_comment = ''
  ratingModalVisible.value = true
}

const submitRating = async () => {
  if (!selectedQuotationForRating.value) return
  isSubmittingRating.value = true
  try {
    await api.post(`/quotations/${selectedQuotationForRating.value.id}/rate`, ratingForm.value)
    toast.success('Servicio calificado correctamente')
    ratingModalVisible.value = false
    // Refresh quotations list if modal is open, or just the main list
    if (selectedRequestForQuotations.value) {
      openQuotationsModal(selectedRequestForQuotations.value)
    }
    fetchRequests(pagination.value.current_page)
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Error al calificar')
  } finally {
    isSubmittingRating.value = false
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

const getImageUrl = (image: any): string => {
  if (!image || !image.path) return ''
  return `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/storage/${image.path}`
}

const handleAction = async (action: string, id: number) => {
  try {
    await api.post(`/service-requests/${id}/${action}`)
    
    let statusText = 'procesada'
    if (action === 'cancel') statusText = 'cancelada'
    if (action === 'accept') statusText = 'aceptada'
    
    toast.success(`Solicitud ${statusText} correctamente`)
    fetchRequests(pagination.value.current_page)
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Error al procesar la acción')
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
          <h2 class="text-xl font-extrabold text-slate-900">Mis Solicitudes</h2>
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
            
             <div class="relative min-w-[140px]">
              <select v-model="selectedCategoryId" @change="fetchRequests(1)" class="w-full h-10 pl-3 pr-8 text-xs font-bold bg-slate-50 border-none rounded-xl focus:ring-primary/20 appearance-none">
                <option value="all">Todas categorías</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
              <span class="material-symbols-outlined absolute right-2 top-2.5 text-slate-400 pointer-events-none" style="font-size: 18px">category</span>
            </div>
          </div>
          
          <div class="mt-2 flex w-full items-center rounded-xl bg-slate-50 border border-slate-100 focus-within:ring-1 focus-within:ring-primary/20 transition-all">
             <span class="material-symbols-outlined ml-3 text-slate-400" style="font-size: 20px">search</span>
             <input v-model="searchTerm" @keyup.enter="fetchRequests(1)" placeholder="Buscar solicitud..." class="h-10 w-full border-none bg-transparent px-3 text-sm font-medium focus:ring-0" />
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
            <span class="material-symbols-outlined text-4xl text-slate-300">receipt_long</span>
          </div>
          <p class="text-slate-500 font-bold">No has realizado ninguna solicitud todavía</p>
          <button @click="router.push({ name: 'user-services' })" class="mt-4 px-6 py-2 bg-primary text-white rounded-xl text-sm font-bold">Explorar servicios</button>
        </div>

        <div v-else class="flex flex-col gap-4 p-6">
          <div v-for="req in requests" :key="req.id" class="bg-white border border-slate-100 rounded-[1.5rem] shadow-sm overflow-hidden transition-all hover:shadow-md">
            <div class="p-5">
              <div class="flex justify-between items-start mb-4">
                <div class="flex gap-3">
                  <div class="h-12 w-12 rounded-xl bg-primary/5 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">engineering</span>
                  </div>
                  <div>
                    <h3 class="font-black text-slate-900 leading-tight">{{ req.service?.name }}</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                      ID: #{{ req.id }}
                    </p>
                  </div>
                </div>
                <div class="flex flex-col items-end gap-1">
                  <span :class="getStatusColor(req.status)" class="px-3 py-1 rounded-lg text-[10px] font-black uppercase border tracking-tight">
                    {{ req.status }}
                  </span>
                  <span :class="isVigente(req.date, req.time) ? 'bg-cyan-50 text-cyan-600' : 'bg-slate-100 text-slate-400'" class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase">
                    {{ isVigente(req.date, req.time) ? 'Vigente' : 'Finalizada' }}
                  </span>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4 py-3 border-y border-slate-50">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-slate-300" style="font-size: 18px">calendar_today</span>
                  <span class="text-xs font-bold text-slate-600">{{ req.date }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-slate-300" style="font-size: 18px">schedule</span>
                  <span class="text-xs font-bold text-slate-600">{{ req.time }}</span>
                </div>
              </div>

              <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="flex -space-x-2">
                    <div v-for="i in Math.min(req.quotations_count, 3)" :key="i" class="h-6 w-6 rounded-full bg-slate-200 border-2 border-white flex items-center justify-center">
                      <span class="material-symbols-outlined text-[12px] text-slate-500">person</span>
                    </div>
                  </div>
                  <span class="text-[10px] font-black text-slate-400 uppercase">
                    {{ req.quotations_count }} cotizaciones recibidas
                  </span>
                </div>
                
                <button 
                  @click="openQuotationsModal(req)"
                  class="flex items-center gap-1 text-primary text-xs font-black uppercase hover:underline"
                >
                  Ver Cotizaciones
                  <span class="material-symbols-outlined" style="font-size: 16px">chevron_right</span>
                </button>
              </div>

              <div v-if="req.observations" class="mt-3">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Mis observaciones:</p>
                <p class="text-xs text-slate-500 italic">"{{ req.observations }}"</p>
              </div>

              <!-- Imagen de referencia -->
              <div v-if="req.image" class="mt-4">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-2">Mi imagen de referencia:</p>
                <div 
                  @click="selectedImage = getImageUrl(req.image)"
                  class="relative h-24 w-40 rounded-xl overflow-hidden cursor-pointer group"
                >
                  <img :src="getImageUrl(req.image)" alt="Referencia" class="h-full w-full object-cover transition-transform group-hover:scale-110" />
                  <div class="absolute inset-0 bg-black/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="material-symbols-outlined text-white">zoom_in</span>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="mt-5 flex gap-2">
                <!-- VIGENTES -->
                <template v-if="isVigente(req.date, req.time)">
                  <!-- Pendiente: Cancel -->
                  <button 
                    v-if="req.status === 'pendiente'"
                    @click="handleAction('cancel', req.id)"
                    class="flex-1 h-10 bg-slate-100 text-slate-600 rounded-xl text-xs font-black uppercase transition-transform active:scale-95"
                  >
                    Cancelar Solicitud
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="flex justify-center items-center gap-4 py-8 mb-40">
          <button @click="fetchRequests(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-100 disabled:opacity-50">
            <span class="material-symbols-outlined">chevron_left</span>
          </button>
          <span class="text-sm font-black text-slate-600">Pág. {{ pagination.current_page }} de {{ pagination.last_page }}</span>
          <button @click="fetchRequests(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-100 disabled:opacity-50">
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
        </div>
      </main>

      <!-- Quotations Modal -->
      <div v-if="quotationsModalVisible" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center p-0 md:p-4">
        <div @click="quotationsModalVisible = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-2xl bg-white rounded-t-3xl md:rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden animate-slide-up">
           <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white sticky top-0 z-10">
            <div>
              <h3 class="text-lg font-black text-slate-900">Cotizaciones Recibidas</h3>
              <p class="text-xs text-slate-500 font-bold">Solicitud #{{ selectedRequestForQuotations?.id }}</p>
            </div>
            <button @click="quotationsModalVisible = false" class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center">
              <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-6 no-scrollbar">
            <div v-if="isLoadingQuotations" class="flex flex-col items-center justify-center py-20 gap-4">
               <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
            <div v-else-if="quotations.length === 0" class="text-center py-20">
               <p class="text-slate-400 font-bold">No se han recibido cotizaciones todavía.</p>
            </div>
            <div v-else class="flex flex-col gap-4">
              <div v-for="q in quotations" :key="q.id" class="p-5 border border-slate-100 rounded-[1.5rem] bg-slate-50/50">
                <div class="flex justify-between items-start mb-4">
                  <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                      {{ q.provider?.name?.charAt(0) }}
                    </div>
                    <div>
                      <p class="font-black text-slate-900 text-sm">{{ q.provider?.name }}</p>
                      <div class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-amber-400 text-[14px]" style="font-variation-settings: 'FILL' 1">star</span>
                        <span class="text-[10px] font-bold text-slate-500">Nuevo Proveedor</span>
                      </div>
                    </div>
                  </div>
                  <span :class="getStatusColor(q.status)" class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border tracking-tight">
                    {{ q.status }}
                  </span>
                </div>

                <div v-if="q.price" class="mb-4">
                   <p class="text-[10px] font-black text-primary uppercase mb-1">Costo Estimado</p>
                   <p class="text-2xl font-black text-slate-900">${{ q.price }}</p>
                   <p v-if="q.provider_observations" class="mt-2 text-xs text-slate-600 italic">"{{ q.provider_observations }}"</p>
                </div>
                <div v-else class="mb-4">
                   <p class="text-xs text-slate-400 font-bold italic">Esperando cotización del proveedor...</p>
                </div>

                <!-- Rating -->
                <div v-if="q.rating" class="mt-4 p-3 bg-amber-50 rounded-xl border border-amber-100">
                  <div class="flex items-center justify-between">
                    <p class="text-[10px] font-black text-amber-600 uppercase">Mi Calificación</p>
                    <div class="flex gap-0.5">
                      <span v-for="i in 5" :key="i" class="material-symbols-outlined text-[14px]" :class="i <= q.rating ? 'text-amber-400' : 'text-slate-200'" style="font-variation-settings: 'FILL' 1">star</span>
                    </div>
                  </div>
                  <p v-if="q.rating_comment" class="mt-1 text-xs text-slate-600 leading-relaxed italic">"{{ q.rating_comment }}"</p>
                </div>

                <div class="mt-4 flex gap-2">
                  <button 
                    v-if="(q.status === 'cotizada' || q.status === 'pendiente') && selectedRequestForQuotations?.status === 'pendiente'"
                    @click="acceptQuotation(q.id)"
                    class="flex-1 h-10 bg-primary text-white rounded-xl text-xs font-black uppercase shadow-sm shadow-primary/20"
                  >
                    Aceptar
                  </button>
                  <button 
                    v-if="(q.status === 'cotizada' || q.status === 'pendiente') && selectedRequestForQuotations?.status === 'pendiente'"
                    @click="rejectQuotation(q.id)"
                    class="flex-1 h-10 bg-slate-100 text-slate-600 rounded-xl text-xs font-black uppercase transition-transform active:scale-95"
                  >
                    Rechazar
                  </button>
                  <button 
                    v-if="q.status === 'aceptada' && !isVigente(selectedRequestForQuotations?.date, selectedRequestForQuotations?.time) && !q.rating"
                    @click="openRatingModal(q)"
                    class="flex-1 h-10 bg-amber-400 text-white rounded-xl text-xs font-black uppercase shadow-sm shadow-amber-200"
                  >
                    Calificar Servicio
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Rating Modal -->
      <div v-if="ratingModalVisible" class="fixed inset-0 z-[110] flex items-center justify-center p-4">
        <div @click="ratingModalVisible = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-sm bg-white rounded-[2rem] shadow-2xl p-8 animate-scale-up">
          <div class="text-center mb-6">
            <h3 class="text-xl font-black text-slate-900 mb-2">¿Qué te pareció el servicio?</h3>
            <p class="text-sm text-slate-500">Tu opinión nos ayuda a mejorar la plataforma</p>
          </div>

          <div class="flex justify-center gap-2 mb-8">
            <button 
              v-for="i in 5" 
              :key="i"
              @click="ratingForm.rating = i"
              class="h-12 w-12 rounded-2xl flex items-center justify-center transition-all"
              :class="i <= ratingForm.rating ? 'bg-amber-100 text-amber-400 border-2 border-amber-200' : 'bg-slate-50 text-slate-300 border-2 border-transparent'"
            >
              <span class="material-symbols-outlined text-3xl" :class="i <= ratingForm.rating ? '' : 'text-slate-300'" style="font-variation-settings: 'FILL' 1">star</span>
            </button>
          </div>

          <div class="mb-8">
            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Comentarios (Opcional)</label>
            <textarea 
              v-model="ratingForm.rating_comment"
              placeholder="Escribe aquí tu experiencia..."
              class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-medium focus:ring-2 focus:ring-primary/10 h-32 resize-none"
            ></textarea>
          </div>

          <div class="flex gap-3">
            <button @click="ratingModalVisible = false" class="flex-1 h-12 bg-slate-100 text-slate-600 rounded-2xl text-xs font-black uppercase">Cancelar</button>
            <button 
              @click="submitRating"
              :disabled="isSubmittingRating"
              class="flex-1 h-12 bg-primary text-white rounded-2xl text-xs font-black uppercase shadow-lg shadow-primary/20 disabled:opacity-50"
            >
              {{ isSubmittingRating ? 'Enviando...' : 'Enviar Calificación' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Image Modal -->
      <div v-if="selectedImage" class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-sm" @click="selectedImage = null">
        <button class="absolute top-6 right-6 text-white text-3xl font-black">
          <span class="material-symbols-outlined">close</span>
        </button>
        <img :src="selectedImage" alt="Vista ampliada" class="max-w-full max-h-full rounded-2xl shadow-2xl animate-scale-up" @click.stop />
      </div>

      <nav
        class="absolute bottom-0 z-50 w-full border-t border-slate-100 bg-white/95 pb-8 px-6 backdrop-blur-xl md:rounded-b-3xl"
      >
        <div class="flex h-20 items-center justify-between">
          <button @click="router.push({ name: 'user-services' })" class="group flex flex-col items-center gap-1.5 w-14">
            <span
              class="material-symbols-outlined text-slate-400 transition-colors group-hover:text-primary"
              style="font-size: 26px"
              >manage_search</span
            >
          </button>
          <button class="group flex flex-col items-center gap-1.5 w-14">
            <div class="flex h-10 w-12 items-center justify-center rounded-xl bg-primary/10">
              <span
                class="material-symbols-outlined text-primary"
                style="font-size: 26px; font-variation-settings: 'FILL' 1"
                >receipt_long</span
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
