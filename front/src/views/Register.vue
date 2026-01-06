<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps<{
  role?: string
}>()

const router = useRouter()
const internalRole = ref('client')
const showPassword = ref(false)

onMounted(() => {
  if (props.role === '1') {
    internalRole.value = 'admin'
  } else {
    internalRole.value = 'client'
  }
})

const goBack = () => {
  router.push({ name: 'welcome' })
}

const goToLogin = () => {
  router.push({ name: 'login' })
}
</script>

<template>
  <div
    class="w-full max-w-2xl mx-auto min-h-screen md:min-h-[auto] relative flex flex-col bg-background-light md:shadow-2xl overflow-x-hidden md:my-12 md:rounded-3xl"
  >
    <header
      class="flex-none bg-background-light/90 backdrop-blur-md p-4 sticky top-0 z-20 border-b border-slate-200"
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
            Bienvenido
          </h1>
          <p class="text-slate-500 font-medium">
            Completa tus datos para comenzar a gestionar tus eventos.
          </p>
        </div>

        <div class="flex flex-col gap-3 mb-8">
          <label class="text-sm font-bold text-slate-500 ml-1">
            ¿Cómo deseas registrarte?
          </label>
          <div class="grid grid-cols-2 gap-4">
            <label class="cursor-pointer group relative">
              <input
                v-model="internalRole"
                class="peer sr-only"
                name="role"
                type="radio"
                value="client"
              />
              <div
                class="flex flex-col items-center gap-3 p-5 rounded-2xl border border-slate-200 bg-background-light text-slate-500 transition-all peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary hover:bg-slate-50"
              >
                <span class="material-symbols-outlined text-4xl">sentiment_satisfied</span>
                <span class="font-bold">Soy Cliente</span>
                <div
                  class="absolute top-3 right-3 hidden peer-checked:block text-primary"
                >
                  <span class="material-symbols-outlined text-xl">check_circle</span>
                </div>
              </div>
            </label>
            <label class="cursor-pointer group relative">
              <input
                v-model="internalRole"
                class="peer sr-only"
                name="role"
                type="radio"
                value="admin"
              />
              <div
                class="flex flex-col items-center gap-3 p-5 rounded-2xl border border-slate-200 bg-background-light text-slate-500 transition-all peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary hover:bg-slate-50"
              >
                <span class="material-symbols-outlined text-4xl">storefront</span>
                <span class="font-bold">Administrador</span>
                <div
                  class="absolute top-3 right-3 hidden peer-checked:block text-primary"
                >
                  <span class="material-symbols-outlined text-xl">check_circle</span>
                </div>
              </div>
            </label>
          </div>
        </div>

        <div class="flex flex-col gap-5">
          <label class="flex flex-col gap-2 group">
            <span class="text-sm font-bold text-slate-500 ml-1">Nombre Completo</span>
            <div class="relative">
              <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
              >
                person
              </span>
              <input
                class="w-full rounded-2xl bg-background-light border border-slate-200 h-14 pl-12 pr-4 text-base font-medium text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-400 shadow-sm"
                placeholder="Ej. Juan Pérez"
                type="text"
              />
            </div>
          </label>
          <label class="flex flex-col gap-2 group">
            <span class="text-sm font-bold text-slate-500 ml-1">Correo Electrónico</span>
            <div class="relative">
              <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
              >
                mail
              </span>
              <input
                class="w-full rounded-2xl bg-background-light border border-slate-200 h-14 pl-12 pr-4 text-base font-medium text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-400 shadow-sm"
                placeholder="nombre@ejemplo.com"
                type="email"
              />
            </div>
          </label>
          <label class="flex flex-col gap-2 group">
            <span class="text-sm font-bold text-slate-500 ml-1">Contraseña</span>
            <div class="relative">
              <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
              >
                lock
              </span>
              <input
                :type="showPassword ? 'text' : 'password'"
                class="w-full rounded-2xl bg-background-light border border-slate-200 h-14 pl-12 pr-12 text-base font-medium text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-400 shadow-sm"
                placeholder="••••••••"
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
          </label>
        </div>
        <div class="h-10"></div>
      </div>
    </main>

    <footer
      class="flex-none bg-background-light/90 backdrop-blur-xl border-t border-slate-200 p-5 pb-8 z-30 md:rounded-b-3xl"
    >
      <div class="max-w-xl mx-auto flex flex-col gap-4">
        <button
          class="w-full bg-primary hover:opacity-90 text-white font-bold text-lg h-14 rounded-2xl flex items-center justify-center gap-2 transition-all active:scale-[0.98] shadow-sm"
        >
          <span>Registrarse</span>
          <span class="material-symbols-outlined text-xl">arrow_forward</span>
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
