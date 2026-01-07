<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/axios'
import { toast } from 'vue3-toastify'

const router = useRouter()
const showPassword = ref(false)
const isLoading = ref(false)

const form = ref({
  email: '',
  password: '',
})

const errors = ref<Record<string, string[]>>({})

onMounted(() => {
  const token = localStorage.getItem('token')
  const userStr = localStorage.getItem('user')

  if (token && userStr) {
    try {
      const user = JSON.parse(userStr)
      const userRole = user.role?.name
      if (userRole === 'superadmin') {
        router.push({ name: 'users' })
      } else if (userRole === 'admin') {
        router.push({ name: 'admin-services' })
      } else {
        router.push({ name: 'user-services' })
      }
    } catch (e) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }
})

const goBack = () => {
  router.push({ name: 'welcome' })
}

const goToRegister = () => {
  router.push({ name: 'register', params: { role: '0' } })
}

const handleLogin = async () => {
  isLoading.value = true
  errors.value = {}

  try {
    const response = await api.post('/login', form.value)
    const { data, message } = response.data

    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))

    toast.success(message)

    setTimeout(() => {
      const userRole = data.user.role?.name
      if (userRole === 'superadmin') {
        router.push({ name: 'users' })
      } else if (userRole === 'admin') {
        router.push({ name: 'admin-services' })
      } else {
        router.push({ name: 'user-services' })
      }
    }, 1500)
  } catch (error: any) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors
      toast.error(error.response.data.message)
    } else {
      const msg = error.response?.data?.message
      toast.error(msg)
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="bg-slate-50 min-h-screen font-display text-text-main antialiased md:py-12">
    <div
      class="w-full max-w-2xl mx-auto md:h-[calc(100vh-6rem)] min-h-screen md:min-h-0 relative flex flex-col bg-white md:shadow-2xl overflow-x-hidden md:rounded-3xl border border-slate-100"
    >
      <header class="flex-none p-4 sticky top-0 z-20 bg-white/95 backdrop-blur-sm md:rounded-t-3xl">
      <div class="flex items-center justify-between">
        <button
          @click="goBack"
          class="flex size-10 items-center justify-center rounded-full hover:bg-slate-100 transition-colors text-slate-900"
        >
          <span class="material-symbols-outlined text-2xl">arrow_back</span>
        </button>
        <div class="size-10"></div>
      </div>
    </header>

    <main class="flex-1 overflow-y-auto overflow-x-hidden no-scrollbar flex flex-col justify-center pb-10">
      <div class="max-w-md mx-auto w-full px-6">
        <div class="mb-10 text-center flex flex-col items-center">
          <div
            class="size-16 rounded-3xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-primary mb-6"
          >
            <span class="material-symbols-outlined text-3xl">event_available</span>
          </div>
          <h1 class="text-3xl font-extrabold text-slate-900 mb-2 tracking-tight">
            ¡Hola de nuevo!
          </h1>
          <p class="text-slate-500 font-medium">
            Ingresa tus datos para continuar.
          </p>
        </div>

        <form @submit.prevent="handleLogin" class="flex flex-col gap-5">
          <label class="flex flex-col gap-2 group">
            <span class="text-sm font-bold text-slate-900 ml-1">
              Correo Electrónico
            </span>
            <div class="relative transition-all">
              <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
                >mail</span
              >
              <input
                v-model="form.email"
                id="email"
                class="w-full rounded-2xl bg-white border border-slate-200 h-14 pl-12 pr-4 text-base font-medium text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-400 shadow-sm"
                placeholder="ejemplo@correo.com"
                type="email"
                :class="{ 'border-red-500': errors.email }"
              />
            </div>
            <span v-if="errors.email" class="text-xs text-red-500 ml-1">{{ errors.email[0] }}</span>
          </label>

          <label class="flex flex-col gap-2 group">
            <span class="text-sm font-bold text-slate-900 ml-1">
              Contraseña
            </span>
            <div class="relative transition-all">
              <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
                >lock</span
              >
              <input
                v-model="form.password"
                id="password"
                :type="showPassword ? 'text' : 'password'"
                class="w-full rounded-2xl bg-white border border-slate-200 h-14 pl-12 pr-12 text-base font-medium text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-400 shadow-sm"
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

          <div class="flex items-center justify-end px-1">
            <button type="button" class="text-xs font-bold text-primary hover:underline">
              ¿Olvidaste tu contraseña?
            </button>
          </div>

          <button
            type="submit"
            :disabled="isLoading"
            class="w-full bg-primary hover:opacity-90 text-white font-bold text-lg h-14 rounded-2xl flex items-center justify-center gap-2 transition-all active:scale-[0.98] mt-4 shadow-md disabled:opacity-50"
          >
            <span v-if="!isLoading">Iniciar Sesión</span>
            <span v-else class="animate-pulse">Cargando...</span>
            <span v-if="!isLoading" class="material-symbols-outlined text-xl">arrow_forward</span>
          </button>
        </form>

        <div class="mt-8 text-center font-display">
          <p class="text-slate-500 font-medium text-sm">
            ¿No tienes una cuenta?
            <button
              @click="goToRegister"
              class="text-primary font-bold hover:underline ml-1"
            >
              Regístrate aquí
            </button>
          </p>
        </div>
      </div>
    </main>
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
