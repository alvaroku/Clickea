<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';

const router = useRouter();
const users = ref([
  {
    id: 1,
    name: 'Carlos Mendoza',
    email: 'carlos.mendoza@evento.app',
    role: 'Admin',
    status: 'En línea',
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCNFGUyzHnQ6ycyQb5boWQQxVBa-A3VJBCHQ7PEGX479gMdwtFpjXpd0KfmBEM5FsU6pWV6f004EiOCsAw3YYhOv8BQUkcXcEavtcj0U-jZVAbVB49XeD1RETwbRWFoASS1CSxvRSbkEgzmqKGXKttBAJ0U6-s_yyFigHDQC-ziv7cBkAKZ_2L0ondjJwnxIy7XqwDEIj0qMXJgi7wLoXb-ZcF9PU0IbyDwh4NAvaYOyWjayNeSkExIn0IuZSahuKN-dk1KHNApXq0'
  },
  {
    id: 2,
    name: 'Ana Torres',
    email: 'ana.torres92@gmail.com',
    role: 'Cliente',
    status: 'Última vez: 2h',
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCz1YIemTSmTol16__uJRGmxKtpTYlK9krtEHvkQhvP2Qdt7qCq3K5bxHoFzw_9m5r82Ibju_GgXWRGaV6VogbRIHSYwOE_ebZ6XaLzmmfid4QJBpqV3UMGaK8u3np8AyL6f9Yp_0MZrECGxaqKDHfOJOYMTI4q1MbeEqhG4Bk0yx-Ct6v8auQqXhAScLM63cOoedsi3_NmEE-48QIZ6Nsk-sac8nFTxYsQuTM637Hx5lQCaWmGCDxKKg3OgQT0JUJaBxTJCO_iDwg'
  },
  {
    id: 3,
    name: 'Javier Ruiz',
    email: 'javier.ruiz@hotmail.com',
    role: 'Suspendido',
    status: 'Pendiente de pago',
    suspended: true,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDeXLSfo5oe7wZWcwL1CWi33oMHw--au-IWmJOJazbFZ9NBs6Derml0mW0dyNbWrsTB3Syw6m9CV8DR-aTHZzzDCUsr4YtQ-mCjoAd7mCDCqJs22JFZ27BUmtyG7pV7iMjY4EmRJnES20bbywdCj5NFelwyoZ382gUw7wG-donSlx1qlhEKy5WuaSRbWgf70mQhJw5T4XzsbWrgBfzFOVNXC8sNUrgfrud_Zo04gRPviw3qXvLHH_G5ysVmDra5dgVb3OygUDr2Ea4'
  },
  {
    id: 4,
    name: 'Sofia Martinez',
    email: 'sofia.m@evento.app',
    role: 'Admin',
    status: 'En línea',
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBKNE1F2oIuM15POcjQsqwtO9gWfp1ae_N0buYW2AIDqM79b7kPc0_sgbj9-Hdz1dNs-zjUlD7tzj7ifXlyAxNV0KBxDej9aA784mY9zjcmhT9ni-FdYjBOMkLRgJUL06GSZNINmoio04d6b33oXCzHhbkhMWnSxfvZsuv6Uc0QAV7rHRPUxtgFYGtSMzMPcsDEF9t9lq2V4OZCztEL6jPktXn4qhRnZ2tFSznhyJD95wD0PaJUXyxVVMu2nLJgXyT5_kzPwXscRaY'
  }
]);

const logout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  toast.success('Sesión cerrada correctamente');
  router.push('/login');
};

const goBack = () => {
  router.back();
};
</script>

<template>
  <div class="min-h-screen bg-slate-50 md:flex md:items-center md:justify-center md:bg-slate-200 p-0 md:p-4">
    <div class="w-full max-w-md mx-auto bg-white min-h-screen md:min-h-[850px] md:h-[850px] md:rounded-[3rem] shadow-2xl overflow-hidden flex flex-col relative">
      <header class="sticky top-0 z-20 bg-white border-b border-slate-100 px-6 pt-4 pb-4">
        <div class="flex items-center justify-between mb-4">
          <button @click="goBack" class="text-slate-900 flex size-10 shrink-0 items-center justify-center rounded-full hover:bg-slate-50 transition-colors">
            <span class="material-symbols-outlined text-2xl">arrow_back</span>
          </button>
          <h2 class="text-slate-900 text-xl font-bold tracking-tight">
            Gestión de Usuarios
          </h2>
          <div class="flex items-center justify-end">
            <button @click="logout" class="flex size-10 cursor-pointer items-center justify-center rounded-full bg-transparent text-slate-900 hover:bg-slate-50 transition-colors">
              <span class="material-symbols-outlined text-2xl">logout</span>
            </button>
          </div>
        </div>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <span class="material-symbols-outlined text-slate-400 text-[22px]">search</span>
          </div>
          <input
            class="block w-full rounded-xl border-none bg-slate-50 py-3.5 pl-11 pr-4 text-slate-900 placeholder:text-slate-400 focus:ring-0 focus:bg-white focus:shadow-sm transition-all text-sm font-medium"
            placeholder="Buscar usuarios por nombre o email..."
            type="text"
          />
        </div>
      </header>

      <main class="flex-1 px-6 space-y-5 pb-28 pt-6 overflow-y-auto custom-scrollbar">
        <div class="flex justify-between items-center">
          <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider">
            Todos los usuarios
          </h3>
          <span class="text-xs font-bold text-primary bg-blue-50 px-2.5 py-1 rounded-md">
            {{ users.length }} total
          </span>
        </div>

        <div v-for="user in users" :key="user.id" 
          class="group relative flex flex-col sm:flex-row items-start sm:items-center gap-4 rounded-3xl bg-white p-4 shadow-sm border border-transparent hover:border-slate-100 transition-all cursor-pointer"
          :class="{ 'opacity-80': user.suspended }">
          
          <div class="relative shrink-0 overflow-hidden rounded-full size-16 bg-slate-100 border-2 border-white shadow-sm"
               :class="{ 'grayscale': user.suspended }">
            <div class="absolute inset-0 bg-center bg-no-repeat bg-cover"
                 :style="{ backgroundImage: `url('${user.image}')` }">
            </div>
          </div>

          <div class="flex flex-col flex-1 min-w-0 w-full">
            <div class="flex justify-between items-start mb-1">
              <p class="text-slate-900 text-[16px] font-bold leading-tight truncate pr-2">
                {{ user.name }}
              </p>
              <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                    :class="[
                      user.role === 'Admin' ? 'bg-purple-100 text-purple-600' :
                      user.role === 'Suspendido' ? 'bg-slate-100 text-slate-400' :
                      'bg-slate-100 text-slate-600'
                    ]">
                {{ user.role }}
              </span>
            </div>
            <p class="text-sm text-slate-500 truncate mb-2">
              {{ user.email }}
            </p>
            <div class="flex items-center gap-2">
              <template v-if="user.suspended">
                <span class="material-symbols-outlined text-[14px] text-amber-500">warning</span>
                <span class="text-xs font-medium text-slate-400">{{ user.status }}</span>
              </template>
              <template v-else>
                <div class="h-2 w-2 rounded-full" :class="user.status === 'En línea' ? 'bg-emerald-500' : 'bg-slate-300'"></div>
                <span class="text-xs font-medium text-slate-400">{{ user.status }}</span>
              </template>
            </div>
          </div>

          <button class="absolute top-4 right-4 sm:static shrink-0 size-8 flex items-center justify-center rounded-full text-slate-300 hover:text-primary hover:bg-blue-50 transition-colors">
            <span class="material-symbols-outlined text-xl">more_vert</span>
          </button>
        </div>
      </main>

      <div class="absolute bottom-24 right-6 z-30">
        <button class="flex items-center justify-center gap-2 bg-primary hover:opacity-90 text-white rounded-2xl px-5 py-4 shadow-lg transition-transform active:scale-95">
          <span class="material-symbols-outlined text-2xl">person_add</span>
          <span class="font-bold text-sm tracking-wide">Nuevo</span>
        </button>
      </div>

      <div class="absolute bottom-0 z-20 w-full bg-white border-t border-slate-100 pb-8 pt-4 px-6 flex justify-between items-center text-[10px] font-bold tracking-wide text-slate-500 uppercase">
        <button class="flex flex-col items-center gap-1.5 hover:text-primary transition-colors">
          <span class="material-symbols-outlined text-[26px]">dashboard</span>
          Inicio
        </button>
        <button class="flex flex-col items-center gap-1.5 text-primary transition-colors">
          <span class="material-symbols-outlined text-[26px] fill-icon">group</span>
          Usuarios
        </button>
        <button class="flex flex-col items-center gap-1.5 hover:text-primary transition-colors">
          <span class="material-symbols-outlined text-[26px]">calendar_month</span>
          Eventos
        </button>
        <button class="flex flex-col items-center gap-1.5 hover:text-primary transition-colors">
          <span class="material-symbols-outlined text-[26px]">settings</span>
          Ajustes
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
.fill-icon {
  font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
