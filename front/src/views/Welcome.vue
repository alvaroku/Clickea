<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const navigateToRegister = (role: number) => {
  router.push({ name: 'register', params: { role: role.toString() } })
}

const navigateToLogin = () => {
  router.push({ name: 'login' })
}
</script>

<template>
  <div
    class="w-full max-w-6xl mx-auto min-h-screen relative flex flex-col bg-background-light md:shadow-2xl overflow-x-hidden"
  >
    <header class="relative w-full">
      <div
        class="w-full h-72 md:h-96 bg-center bg-no-repeat bg-cover relative"
        data-alt="Concert crowd with vibrant stage lights and confetti"
        style="
          background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDMVQdEDkQ3lHG3SRcQbn-HZ5wyBDsN3nOo00QtPeSZpdj2CpNLUQYUl6kyEDpZb10TlOnTH5tEwByJyeXghcXzVJZdsXuMG8SMEMXhUxTZhSnDQKFBKtvqLeLzuEVBJyaNkWMC7vaToQK4wPnGgSHToy7rel6N_s1lCa53BoKPLXys-g58_BudVlm7ZhTwX16F6MxJzkzWIrjjS7iXIoUjcr3eSVa3-XLFN1NaQpaxkPNb16gURGmPbhKvNntkSXm95uVqG3gCneM');
        "
      >
        <div
          class="absolute inset-0 bg-gradient-to-t from-white via-white/70 to-transparent"
        ></div>
      </div>
      <div class="absolute bottom-0 left-0 w-full px-6 pb-6 md:px-12 md:pb-12">
        <div class="max-w-5xl mx-auto w-full">
          <div
            class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/80 backdrop-blur-md border border-slate-200 rounded-full mb-5 shadow-sm"
          >
            <span class="material-symbols-outlined text-primary text-[20px]"
              >event_available</span
            >
            <span
              class="text-primary font-bold tracking-wider text-xs uppercase"
              >EventFlow</span
            >
          </div>
          <h1
            class="text-slate-900 tracking-tight text-[40px] md:text-6xl font-extrabold leading-tight mb-2"
          >
            Bienvenido
          </h1>
          <p
            class="text-slate-500 text-lg md:text-xl font-medium leading-relaxed max-w-[90%] md:max-w-md"
          >
            Selecciona tu perfil para comenzar la experiencia.
          </p>
        </div>
      </div>
    </header>
    <div class="h-2"></div>
    <main class="flex-1 px-5 grid grid-cols-1 md:grid-cols-2 gap-8 pb-8 items-start max-w-5xl mx-auto w-full">
      <div class="@container w-full group cursor-pointer">
        <div
          class="flex flex-col rounded-3xl overflow-hidden bg-white border border-slate-200 hover:border-primary/40 transition-all duration-300"
        >
          <div class="relative w-full h-36 overflow-hidden bg-slate-50">
            <div
              class="w-full h-full bg-center bg-no-repeat bg-cover transition-transform duration-500 group-hover:scale-105"
              data-alt="Group of friends toasting drinks at a party"
              style="
                background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDH7XgNpgGoMXsC4bZdTEudBozTBFF5qt2_gbhRXm1Ti0Mba7ZoJZmWsS084mwVjoovwy8WN719uMbxnyQc1swJ8Xfi8ZJN0Z3N31m4jRCitQ11JxBbGo_Sp4krmVw57zyDKijy-AtIgaY7-8NUJHXJXMTdbFx5RIwcQi0eyBjnM-J2jVpgFKGLrL7kFod7SqcH-HEOG393HykwSDexRp0spedbzE8n7xnuNyAICfEEgugqYbBefE6Zlo6RJ56yJbtj09rf4RNGs68');
              "
            ></div>
            <div
              class="absolute top-4 right-4 bg-white p-2 rounded-full border border-slate-100 z-10"
            >
              <span class="material-symbols-outlined text-primary"
                >person</span
              >
            </div>
          </div>
          <div class="flex flex-col p-6 gap-5">
            <div class="flex flex-col gap-1.5">
              <p class="text-slate-900 text-xl font-bold leading-tight">
                Soy Cliente
              </p>
              <p
                class="text-slate-500 text-[15px] font-medium leading-relaxed"
              >
                Organiza tu evento perfecto con los mejores servicios.
              </p>
            </div>
            <button
              @click="navigateToRegister(0)"
              class="flex w-full items-center justify-center overflow-hidden rounded-2xl h-12 px-6 bg-primary text-white text-[15px] font-bold leading-normal hover:bg-primary/90 transition-colors"
            >
              <span class="truncate">Continuar como Cliente</span>
              <span class="material-symbols-outlined text-lg ml-2"
                >arrow_forward</span
              >
            </button>
          </div>
        </div>
      </div>
      <div class="@container w-full group cursor-pointer">
        <div
          class="flex flex-col rounded-3xl overflow-hidden bg-white border border-slate-200 hover:border-primary/40 transition-all duration-300"
        >
          <div class="relative w-full h-36 overflow-hidden bg-slate-50">
            <div
              class="w-full h-full bg-center bg-no-repeat bg-cover transition-transform duration-500 group-hover:scale-105"
              data-alt="Professional event planner working on laptop"
              style="
                background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDopsNb8sc7jZeI4xpJZ9BUKYrZVvgwYJvBPsQQZTrDN6l-85gFpDaqkeQewxSWh0NWA2IPzo8yX5ZGQe9S3JppFhQV4BLjp_F4rs8pCzvidwS0HmeM7SEcS4njbxH6vmeQ7tej0KVUg5owFNjWfC4KsP-ioUySTJX4jsSpyoZ1mSyAlmEmSGwOB23XYcjCrLf-hD6QAGQAzejc6Kl-jApqWjvtAIUDEStk3_d-egqNlzTZdoVFS6bWge8xuW8AbVXIi_nMAb8JmIg');
              "
            ></div>
            <div
              class="absolute top-4 right-4 bg-white p-2 rounded-full border border-slate-100 z-10"
            >
              <span class="material-symbols-outlined text-primary"
                >business_center</span
              >
            </div>
          </div>
          <div class="flex flex-col p-6 gap-5">
            <div class="flex flex-col gap-1.5">
              <p class="text-slate-900 text-xl font-bold leading-tight">
                Soy Administrador
              </p>
              <p
                class="text-slate-500 text-[15px] font-medium leading-relaxed"
              >
                Ofrece tus servicios y gestiona eventos de forma eficiente.
              </p>
            </div>
            <button
              @click="navigateToRegister(1)"
              class="flex w-full items-center justify-center overflow-hidden rounded-2xl h-12 px-6 bg-slate-100 text-slate-700 text-[15px] font-bold leading-normal hover:bg-slate-200 transition-colors"
            >
              <span class="truncate">Continuar como Admin</span>
            </button>
          </div>
        </div>
      </div>
    </main>
    <footer class="pb-10 px-4">
      <div class="flex justify-center items-center gap-2">
        <p class="text-slate-400 text-sm font-medium">¿Ya tienes cuenta?</p>
        <button
          @click="navigateToLogin"
          class="text-primary text-sm font-bold hover:underline decoration-2 underline-offset-4"
        >
          Iniciar Sesión
        </button>
      </div>
    </footer>
  </div>
</template>

<style scoped>
</style>
