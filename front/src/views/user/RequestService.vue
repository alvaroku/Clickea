<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../services/axios'
import { toast } from 'vue3-toastify'

const router = useRouter()
const route = useRoute()
const serviceId = route.params.id??''
const service = ref<any>(null)
const isLoading = ref(true)
const isSubmitting = ref(false)
const imagePreview = ref<string | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const form = ref({
  date: '',
  time: '',
  location: '',
  observations: '',
  reference_image: null as File | null
})

const handleFileChange = (event: any) => {
  const file = event.target.files[0]
  if (file) {
    form.value.reference_image = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const fetchServiceDetails = async () => {
  try {
    const response = await api.get(`/services/${serviceId}`)
    service.value = response.data.data
  } catch (error) {
    toast.error('Error al cargar detalles del servicio')
    router.push({ name: 'user-services' })
  } finally {
    isLoading.value = false
  }
}

const handleSubmit = async () => {
  isSubmitting.value = true
  try {
    const formData = new FormData()
    formData.append('service_id', serviceId.toString())
    formData.append('date', form.value.date)
    formData.append('time', form.value.time)
    formData.append('location', form.value.location)
    if (form.value.observations) formData.append('observations', form.value.observations)
    if (form.value.reference_image) {
      formData.append('reference_image', form.value.reference_image)
    }
    
    await api.post('/service-requests', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    toast.success('Solicitud enviada exitosamente')
    router.push({ name: 'user-services' })
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Error al enviar la solicitud')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  fetchServiceDetails()
})
</script>

<template>
  <div class="bg-slate-50 min-h-screen font-display text-text-main antialiased md:py-12">
    <div class="relative flex h-full md:h-[calc(100vh-6rem)] min-h-screen md:min-h-0 w-full flex-col overflow-x-hidden max-w-2xl mx-auto bg-white md:shadow-2xl md:rounded-3xl shadow-subtle border border-slate-100">
      
      <!-- Header -->
      <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-md border-b border-slate-100 md:rounded-t-3xl">
        <div class="flex items-center gap-4 px-6 py-6">
          <button @click="router.back()" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-600 hover:bg-slate-100 transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
          </button>
          <div>
            <h2 class="text-xl font-extrabold text-slate-900">Solicitar Servicio</h2>
            <p v-if="service" class="text-xs font-bold text-primary uppercase tracking-wider">{{ service.name }}</p>
          </div>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto no-scrollbar p-6 pb-24">
        <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 gap-4">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
          <p class="text-slate-400 text-sm font-bold">Cargando detalles...</p>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Info del Servicio -->
          <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 flex gap-4 items-center">
            <div class="h-16 w-16 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
              <span class="material-symbols-outlined" style="font-size: 32px">handshake</span>
            </div>
            <div>
              <p class="text-xs font-bold text-slate-400 uppercase">Estás solicitando</p>
              <p class="text-lg font-black text-slate-900">{{ service.name }}</p>
              <p class="text-sm font-bold text-primary">${{ service.price }}</p>
            </div>
          </div>

          <!-- Fecha y Hora -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="text-sm font-bold text-slate-700 ml-1">Fecha</label>
              <input 
                v-model="form.date"
                type="date" 
                required
                class="w-full h-12 rounded-xl border-slate-200 focus:border-primary focus:ring-primary/20 font-semibold"
              />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-bold text-slate-700 ml-1">Hora</label>
              <input 
                v-model="form.time"
                type="time" 
                required
                class="w-full h-12 rounded-xl border-slate-200 focus:border-primary focus:ring-primary/20 font-semibold"
              />
            </div>
          </div>

          <!-- Lugar -->
          <div class="space-y-2">
            <label class="text-sm font-bold text-slate-700 ml-1">Lugar / Dirección</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" style="font-size: 20px">location_on</span>
              <input 
                v-model="form.location"
                type="text" 
                placeholder="Ej. Calle 50 x 21, Col. Centro"
                required
                class="w-full h-12 pl-12 rounded-xl border-slate-200 focus:border-primary focus:ring-primary/20 font-semibold"
              />
            </div>
          </div>

          <!-- Observaciones -->
          <div class="space-y-2">
            <label class="text-sm font-bold text-slate-700 ml-1">Observaciones</label>
            <textarea 
              v-model="form.observations"
              rows="4"
              placeholder="Cuéntanos más detalles sobre lo que necesitas..."
              class="w-full rounded-2xl border-slate-200 focus:border-primary focus:ring-primary/20 font-semibold p-4"
            ></textarea>
          </div>

          <!-- Imagen de Referencia -->
          <div class="space-y-2">
            <label class="text-sm font-bold text-slate-700 ml-1">Imagen de Referencia (Opcional)</label>
            <div 
              @click="fileInput?.click()"
              class="relative border-2 border-dashed border-slate-200 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 hover:border-primary/50 transition-colors cursor-pointer group min-h-[160px] overflow-hidden"
            >
              <input 
                type="file" 
                ref="fileInput" 
                @change="handleFileChange" 
                class="hidden" 
                accept="image/*"
              />
              
              <template v-if="imagePreview">
                <img :src="imagePreview" class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                  <span class="text-white font-bold text-sm">Cambiar Imagen</span>
                </div>
              </template>
              <template v-else>
                <span class="material-symbols-outlined text-slate-300 group-hover:text-primary transition-colors" style="font-size: 40px">add_a_photo</span>
                <p class="text-xs font-bold text-slate-400 group-hover:text-slate-600">Haz clic para subir una foto</p>
              </template>
            </div>
          </div>
        </form>
      </main>

      <!-- Botón de Acción -->
      <footer class="absolute bottom-0 w-full p-6 bg-white/80 backdrop-blur-md border-t border-slate-100 md:rounded-b-3xl">
        <button 
          @click="handleSubmit"
          :disabled="isSubmitting"
          class="w-full h-14 bg-primary text-white rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-primary/20 active:scale-[0.98] transition-all disabled:opacity-50"
        >
          <span v-if="isSubmitting" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
          <span v-else>Confirmar Solicitud</span>
          <span v-if="!isSubmitting" class="material-symbols-outlined">send</span>
        </button>
      </footer>

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
