<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/axios'
import { toast } from 'vue3-toastify'

const props = defineProps<{
  role?: string
}>()

const router = useRouter()
const internalRole = ref('user')
const showPassword = ref(false)
const isLoading = ref(false)

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = ref<Record<string, string[]>>({})

onMounted(() => {
  if (props.role === '1') {
    internalRole.value = 'admin'
  } else {
    internalRole.value = 'user'
  }
})

const handleRegister = async () => {
  isLoading.value = true
  errors.value = {}

  try {
    const response = await api.post('/register', {
      ...form.value,
      role: internalRole.value
    })

    const { data, message } = response.data
    
    // Guardar token y usuario
    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))

    toast.success(message || 'Registro exitoso')

    const userRole = data.user.role?.name
    // Redirigir después de un momento
    setTimeout(() => {
      if (userRole === 'superadmin') {
        router.push({ name: 'superadmin-dashboard' })
      } else if (userRole === 'admin') {
        router.push({ name: 'admin-services' })
      } else {
        router.push({ name: 'user-services' })
      }
    }, 1500)
  } catch (error: any) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors
      toast.error('Por favor verifica los errores en el formulario')
    } else {
      const msg = error.response?.data?.message || 'Hubo un error al registrarte'
      toast.error(msg)
    }
  } finally {
    isLoading.value = false
  }
}

const goBack = () => {
  router.push({ name: 'welcome' })
}

const goToLogin = () => {
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="bg-slate-50 min-h-screen font-display text-text-main antialiased md:py-12">
    <div
      class="w-full max-w-2xl mx-auto md:h-[calc(100vh-6rem)] min-h-screen md:min-h-0 relative flex flex-col bg-white md:shadow-2xl overflow-x-hidden md:rounded-3xl border border-slate-100"
    >
      <header
        class="flex-none bg-white/95 backdrop-blur-sm p-4 sticky top-0 z-20 border-b border-slate-200 md:rounded-t-3xl"
      >
      <div class="flex items-center justify-between">
        <button
          @click="goBack"
          class="flex size-10 items-center justify-center rounded-full hover:bg-slate-100 transition-colors text-slate-900"
        >
          <span class="material-symbols-outlined text-2xl">arrow_back</span>
        </button>
        <h2
          class="text-lg font-bold leading-tight tracking-tight text-slate-900"
        >
          Crear Cuenta
        </h2>
        <div class="size-10"></div>
      </div>
    </header>

    <main class="flex-1 overflow-y-auto overflow-x-hidden no-scrollbar">
      <div class="w-full p-6 pt-8 md:px-12">
        <div class="mb-8">
          <h1 class="text-3xl font-extrabold text-slate-900 mb-2">
            ¡Hagámoslo realidad!
          </h1>
          <p class="text-slate-500 font-medium">
            Completa tus datos para comenzar.
          </p>
        </div>

        <div class="flex flex-col gap-5">
          <label class="flex flex-col gap-2 group">
            <span class="text-sm font-bold text-slate-900 ml-1">Nombre Completo</span>
            <div class="relative">
              <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
              >
                person
              </span>
              <input
                v-model="form.name"
                id="name"
                class="w-full rounded-2xl bg-white border border-slate-200 h-14 pl-12 pr-4 text-base font-medium text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-400 shadow-sm"
                placeholder="Ej. Juan Pérez"
                type="text"
                :class="{ 'border-red-500': errors.name }"
              />
            </div>
            <span v-if="errors.name" class="text-xs text-red-500 ml-1">{{ errors.name[0] }}</span>
          </label>
          <label class="flex flex-col gap-2 group">
            <span class="text-sm font-bold text-slate-900 ml-1">Correo Electrónico</span>
            <div class="relative">
              <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
              >
                mail
              </span>
              <input
                v-model="form.email"
                id="email"
                class="w-full rounded-2xl bg-white border border-slate-200 h-14 pl-12 pr-4 text-base font-medium text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-400 shadow-sm"
                placeholder="nombre@ejemplo.com"
                type="email"
                :class="{ 'border-red-500': errors.email }"
              />
            </div>
            <span v-if="errors.email" class="text-xs text-red-500 ml-1">{{ errors.email[0] }}</span>
          </label>
          <label class="flex flex-col gap-2 group">
            <span class="text-sm font-bold text-slate-900 ml-1">Contraseña</span>
            <div class="relative">
              <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
              >
                lock
              </span>
              <input
                v-model="form.password"
                id="password"
                :type="showPassword ? 'text' : 'password'"
                class="w-full rounded-2xl bg-white border border-slate-200 h-14 pl-12 pr-12 text-base font-medium text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-400 shadow-sm"
                placeholder="••••••••"
                :class="{ 'border-red-500': errors.password }"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 transition-colors focus:outline-none"
              >
                <span class="material-symbols-outlined text-xl">
                  {{ showPassword ? 'visibility' : 'visibility_off' }}
                </span>
              </button>
            </div>
            <span v-if="errors.password" class="text-xs text-red-500 ml-1">{{ errors.password[0] }}</span>
          </label>
          <label class="flex flex-col gap-2 group">
            <span class="text-sm font-bold text-slate-900 ml-1">Confirmar Contraseña</span>
            <div class="relative">
              <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
              >
                lock_reset
              </span>
              <input
                v-model="form.password_confirmation"
                id="password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                class="w-full rounded-2xl bg-white border border-slate-200 h-14 pl-12 pr-12 text-base font-medium text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-400 shadow-sm"
                placeholder="••••••••"
              />
            </div>
          </label>
        </div>
        <div class="h-10"></div>
      </div>
    </main>

    <footer
      class="flex-none bg-white/95 backdrop-blur-xl border-t border-slate-200 p-5 pb-8 z-30 md:rounded-b-3xl"
    >
      <div class="max-w-xl mx-auto flex flex-col gap-4">
        <button
          @click="handleRegister"
          :disabled="isLoading"
          class="w-full bg-primary hover:opacity-90 text-white font-bold text-lg h-14 rounded-2xl flex items-center justify-center gap-2 transition-all active:scale-[0.98] shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="!isLoading">Registrarse</span>
          <span v-else class="animate-pulse">Cargando...</span>
          <span v-if="!isLoading" class="material-symbols-outlined text-xl">arrow_forward</span>
        </button>
        <div class="flex justify-center gap-1 text-sm font-medium">
          <span class="text-slate-500">¿Ya tienes una cuenta?</span>
          <button
            @click="goToLogin"
            class="text-primary font-bold hover:underline transition-colors"
          >
            Inicia sesión
          </button>
        </div>
      </div>
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
