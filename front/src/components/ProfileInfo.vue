<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/axios'
import { toast } from 'vue3-toastify'

const router = useRouter()
const user = ref<any>({})
const isLoading = ref(true)
const isEditing = ref(false)
const isSaving = ref(false)
const isUploadingPicture = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

const form = ref({
  name: '',
  email: '',
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const profilePictureUrl = computed(() => {
  if (user.value.profile_picture) {
    return `http://localhost:8000/storage/${user.value.profile_picture.path}`
  }
  return null
})

const fetchProfile = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/profile')
    user.value = response.data.data
    form.value.name = user.value.name
    form.value.email = user.value.email
  } catch (error: any) {
    toast.error('Error al cargar el perfil')
  } finally {
    isLoading.value = false
  }
}

const updateProfile = async () => {
  if (isSaving.value) return
  isSaving.value = true

  try {
    const payload: any = {
      name: form.value.name,
      email: form.value.email
    }

    if (form.value.new_password) {
      if (form.value.new_password !== form.value.new_password_confirmation) {
        toast.error('Las contraseñas no coinciden')
        isSaving.value = false
        return
      }
      payload.current_password = form.value.current_password
      payload.new_password = form.value.new_password
      payload.new_password_confirmation = form.value.new_password_confirmation
    }

    const response = await api.put('/profile', payload)
    toast.success(response.data.message || 'Perfil actualizado')
    
    // Actualizar localStorage
    const storedUser = JSON.parse(localStorage.getItem('user') || '{}')
    storedUser.name = form.value.name
    storedUser.email = form.value.email
    localStorage.setItem('user', JSON.stringify(storedUser))
    
    isEditing.value = false
    form.value.current_password = ''
    form.value.new_password = ''
    form.value.new_password_confirmation = ''
    fetchProfile()
  } catch (error: any) {
    const msg = error.response?.data?.message || 'Error al actualizar el perfil'
    toast.error(msg)
  } finally {
    isSaving.value = false
  }
}

const cancelEdit = () => {
  isEditing.value = false
  form.value.name = user.value.name
  form.value.email = user.value.email
  form.value.current_password = ''
  form.value.new_password = ''
  form.value.new_password_confirmation = ''
}

const handleLogout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push({ name: 'login' })
}

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileChange = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (!file) return

  // Validate file type
  if (!file.type.startsWith('image/')) {
    toast.error('Por favor selecciona una imagen válida')
    return
  }

  // Validate file size (2MB)
  if (file.size > 2 * 1024 * 1024) {
    toast.error('La imagen no debe superar 2MB')
    return
  }

  isUploadingPicture.value = true
  
  try {
    const formData = new FormData()
    formData.append('profile_picture', file)

    const response = await api.post('/profile/picture', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    user.value = response.data.data
    toast.success(response.data.message || 'Foto de perfil actualizada')
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Error al subir la imagen')
  } finally {
    isUploadingPicture.value = false
    if (target) target.value = ''
  }
}

const deletePicture = async () => {
  if (!confirm('¿Estás seguro de eliminar tu foto de perfil?')) return

  isUploadingPicture.value = true
  
  try {
    const response = await api.delete('/profile/picture')
    user.value = response.data.data
    toast.success(response.data.message || 'Foto de perfil eliminada')
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Error al eliminar la imagen')
  } finally {
    isUploadingPicture.value = false
  }
}

onMounted(() => {
  fetchProfile()
})
</script>

<template>
  <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 gap-4">
    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
    <p class="text-slate-400 text-sm font-bold">Cargando perfil...</p>
  </div>

  <div v-else class="space-y-6">
    <!-- Avatar Section -->
    <div class="flex flex-col items-center py-8">
      <div class="relative mb-4">
        <div class="size-24 rounded-full flex items-center justify-center shadow-xl overflow-hidden" :class="profilePictureUrl ? 'bg-slate-100' : 'bg-primary'">
          <img v-if="profilePictureUrl" :src="profilePictureUrl" alt="Foto de perfil" class="w-full h-full object-cover" />
          <span v-else class="material-symbols-outlined text-white text-5xl">account_circle</span>
        </div>
        <input 
          ref="fileInput" 
          type="file" 
          accept="image/*" 
          class="hidden" 
          @change="handleFileChange"
        />
        <div class="absolute bottom-0 right-0 flex gap-1">
          <button 
            @click="triggerFileInput"
            :disabled="isUploadingPicture"
            class="size-8 bg-white rounded-full shadow-lg border-2 border-slate-50 flex items-center justify-center hover:bg-slate-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            :title="profilePictureUrl ? 'Cambiar foto' : 'Subir foto'"
          >
            <span v-if="!isUploadingPicture" class="material-symbols-outlined text-slate-600" style="font-size: 18px">photo_camera</span>
            <span v-else class="material-symbols-outlined text-slate-600 animate-spin" style="font-size: 18px">sync</span>
          </button>
          <button 
            v-if="profilePictureUrl"
            @click="deletePicture"
            :disabled="isUploadingPicture"
            class="size-8 bg-red-50 rounded-full shadow-lg border-2 border-red-100 flex items-center justify-center hover:bg-red-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            title="Eliminar foto"
          >
            <span class="material-symbols-outlined text-red-500" style="font-size: 18px">delete</span>
          </button>
        </div>
      </div>
      <h3 class="text-2xl font-black text-slate-900">{{ user.name }}</h3>
      <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mt-1">{{ user.role?.name || 'Usuario' }}</p>
    </div>

    <!-- Info Cards -->
    <div v-if="!isEditing" class="space-y-4">
      <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <label class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 block">Nombre completo</label>
            <p class="text-base font-bold text-slate-900">{{ user.name }}</p>
          </div>
          <span class="material-symbols-outlined text-slate-300">person</span>
        </div>
      </div>

      <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <label class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 block">Correo electrónico</label>
            <p class="text-base font-bold text-slate-900">{{ user.email }}</p>
          </div>
          <span class="material-symbols-outlined text-slate-300">mail</span>
        </div>
      </div>

      <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <label class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 block">Fecha de registro</label>
            <p class="text-base font-bold text-slate-900">{{ new Date(user.created_at).toLocaleDateString() }}</p>
          </div>
          <span class="material-symbols-outlined text-slate-300">calendar_today</span>
        </div>
      </div>

      <button 
        @click="isEditing = true"
        class="w-full bg-primary hover:bg-primary-hover text-white py-4 rounded-2xl font-black uppercase tracking-widest transition-all shadow-lg shadow-primary/25 active:scale-[0.98] flex items-center justify-center gap-2 text-sm mt-6"
      >
        <span class="material-symbols-outlined">edit</span>
        Editar Perfil
      </button>
    </div>

    <!-- Edit Form -->
    <form v-else @submit.prevent="updateProfile" class="space-y-6">
      <div class="space-y-1.5">
        <label class="text-xs font-black text-text-secondary uppercase tracking-widest pl-1">Nombre completo</label>
        <input 
          v-model="form.name"
          required
          type="text"
          class="w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-text-main placeholder:text-text-muted focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-bold"
        />
      </div>

      <div class="space-y-1.5">
        <label class="text-xs font-black text-text-secondary uppercase tracking-widest pl-1">Correo electrónico</label>
        <input 
          v-model="form.email"
          required
          type="email"
          class="w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-text-main placeholder:text-text-muted focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-bold"
        />
      </div>

      <div class="border-t border-slate-200 pt-6">
        <h4 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-4">Cambiar contraseña (opcional)</h4>
        
        <div class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-xs font-black text-text-secondary uppercase tracking-widest pl-1">Contraseña actual</label>
            <input 
              v-model="form.current_password"
              type="password"
              class="w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-text-main placeholder:text-text-muted focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-bold"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-black text-text-secondary uppercase tracking-widest pl-1">Nueva contraseña</label>
            <input 
              v-model="form.new_password"
              type="password"
              class="w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-text-main placeholder:text-text-muted focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-bold"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-black text-text-secondary uppercase tracking-widest pl-1">Confirmar nueva contraseña</label>
            <input 
              v-model="form.new_password_confirmation"
              type="password"
              class="w-full rounded-2xl border-none bg-slate-50 px-5 py-4 text-text-main placeholder:text-text-muted focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-bold"
            />
          </div>
        </div>
      </div>

      <div class="pt-4 flex gap-3">
        <button 
          type="button" 
          @click="cancelEdit"
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
          <span v-if="!isSaving">Guardar Cambios</span>
          <span v-else>Guardando...</span>
        </button>
      </div>
    </form>

    <!-- Logout Button -->
    <button 
      @click="handleLogout"
      class="w-full bg-red-50 hover:bg-red-100 text-red-500 py-4 rounded-2xl font-black uppercase tracking-widest transition-all active:scale-[0.98] flex items-center justify-center gap-2 text-sm mt-8"
    >
      <span class="material-symbols-outlined">logout</span>
      Cerrar Sesión
    </button>
  </div>
</template>
