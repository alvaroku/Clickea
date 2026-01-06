<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const role = ref('client')
const showPassword = ref(false)

const goBack = () => {
  router.push({ name: 'welcome' })
}

const goToRegister = () => {
  router.push({ name: 'register', params: { role: role.value === 'admin' ? '1' : '0' } })
}
</script>

<template>
  <div
    class="w-full max-w-2xl mx-auto min-h-screen md:min-h-[auto] relative flex flex-col bg-background-light md:shadow-2xl overflow-x-hidden md:my-12 md:rounded-3xl"
  >
    <header class="flex-none p-4 sticky top-0 z-20">
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

        <div class="mb-8 font-display">
          <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 ml-1">
            Selecciona tu perfil
          </label>
          <div class="p-1.5 bg-white border border-slate-200 rounded-2xl flex relative shadow-sm">
            <button
              @click="role = 'client'"
              :class="[
                'flex-1 py-3.5 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2',
                role === 'client' ? 'bg-primary text-white shadow-md' : 'text-slate-500 hover:bg-slate-50'
              ]"
            >
              <span class="material-symbols-outlined text-lg">person</span>
              <span>Cliente</span>
            </button>
            <button
              @click="role = 'admin'"
              :class="[
                'flex-1 py-3.5 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2',
                role === 'admin' ? 'bg-primary text-white shadow-md' : 'text-slate-500 hover:bg-slate-50'
              ]"
            >
              <span class="material-symbols-outlined text-lg">storefront</span>
              <span>Administrador</span>
            </button>
          </div>
        </div>

        <form @submit.prevent class="flex flex-col gap-5">
          <div class="space-y-2 group">
            <label class="text-sm font-bold text-slate-900 ml-1">
              Correo Electrónico
            </label>
            <div class="relative transition-all">
              <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
                >mail</span
              >
              <input
                class="w-full rounded-2xl bg-white border border-slate-200 h-14 pl-12 pr-4 text-base font-medium text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-400 shadow-sm"
                placeholder="ejemplo@correo.com"
                type="email"
              />
            </div>
          </div>

          <div class="space-y-2 group">
            <label class="text-sm font-bold text-slate-900 ml-1">
              Contraseña
            </label>
            <div class="relative transition-all">
              <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
                >lock</span
              >
              <input
                :type="showPassword ? 'text' : 'password'"
                class="w-full rounded-2xl bg-white border border-slate-200 h-14 pl-12 pr-12 text-base font-medium text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-400 shadow-sm"
                placeholder="••••••••"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 transition-colors outline-none"
              >
                <span class="material-symbols-outlined text-xl">
                  {{ showPassword ? 'visibility' : 'visibility_off' }}
                </span>
              </button>
            </div>
          </div>

          <div class="flex justify-end pt-1">
            <button
              class="text-sm font-bold text-primary hover:opacity-80 transition-opacity"
              type="button"
            >
              ¿Olvidaste tu contraseña?
            </button>
          </div>

          <button
            class="w-full bg-primary hover:opacity-90 text-white font-bold text-lg h-14 rounded-2xl mt-4 flex items-center justify-center gap-2 transition-all active:scale-[0.98] shadow-sm"
          >
            <span>Iniciar Sesión</span>
            <span class="material-symbols-outlined text-xl">login</span>
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
