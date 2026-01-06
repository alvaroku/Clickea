<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import api from '../../services/axios';

const router = useRouter();
const users = ref<any[]>([]);
const pagination = ref<any>({});
const isLoading = ref(true);
const isSaving = ref(false);
const searchTerm = ref('');
const statusFilter = ref('all');
const currentPage = ref(1);

// Modal state
const isModalOpen = ref(false);
const isEditing = ref(false);
const currentUserId = ref<number | null>(null);
const form = ref({
  name: '',
  email: '',
  role: 'user',
  password: '',
  active: true
});

const errors = ref<Record<string, string[]>>({});

// Context menu state
const activeMenuId = ref<number | null>(null);

const fetchUsers = async (page = 1) => {
  isLoading.value = true;
  try {
    const response = await api.get('/users', {
      params: {
        page,
        search: searchTerm.value,
        status: statusFilter.value
      }
    });
    users.value = response.data.data.data;
    pagination.value = response.data.data;
    currentPage.value = response.data.data.current_page;
  } catch (error: any) {
    toast.error('Error al cargar usuarios');
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchUsers();
});

watch(statusFilter, () => {
  fetchUsers(1);
});

const openCreateModal = () => {
  isEditing.value = false;
  currentUserId.value = null;
  form.value = {
    name: '',
    email: '',
    role: 'user',
    password: '',
    active: true
  };
  errors.value = {};
  isModalOpen.value = true;
};

const openEditModal = (user: any) => {
  isEditing.value = true;
  currentUserId.value = user.id;
  form.value = {
    name: user.name,
    email: user.email,
    role: user.role?.name || 'user',
    password: '',
    active: Boolean(user.active)
  };
  errors.value = {};
  isModalOpen.value = true;
  activeMenuId.value = null;
};

const handleSubmit = async () => {
  errors.value = {};
  isSaving.value = true;
  try {
    let response;
    if (isEditing.value && currentUserId.value) {
      response = await api.put(`/users/${currentUserId.value}`, form.value);
    } else {
      response = await api.post('/users', form.value);
    }
    toast.success(response.data.message || 'Operación realizada correctamente');
    isModalOpen.value = false;
    fetchUsers(currentPage.value);
  } catch (error: any) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      toast.error(error.response?.data?.message || 'Error al guardar usuario');
    }
  } finally {
    isSaving.value = false;
  }
};

const toggleUserStatus = async (user: any) => {
  if (!confirm(`¿Estás seguro de que deseas ${user.active ? 'inactivar' : 'activar'} a este usuario?`)) return;
  
  try {
    await api.patch(`/users/${user.id}/status`);
    toast.success(`Usuario ${user.active ? 'inactivado' : 'activado'} correctamente`);
    fetchUsers(currentPage.value);
    activeMenuId.value = null;
  } catch (error: any) {
    toast.error('Error al cambiar el estado');
  }
};

const deleteUser = async (user: any) => {
  if (!confirm(`¿Estás seguro de que deseas eliminar permanentemente a ${user.name}? Esta acción no se puede deshacer.`)) return;

  try {
    await api.delete(`/users/${user.id}`);
    toast.success('Usuario eliminado correctamente');
    fetchUsers(currentPage.value);
    activeMenuId.value = null;
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Error al eliminar usuario');
  }
};

const logout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  toast.success('Sesión cerrada correctamente');
  router.push('/login');
};

const toggleMenu = (id: number) => {
  activeMenuId.value = activeMenuId.value === id ? null : id;
};

const goBack = () => {
  router.push('/');
};
</script>

<template>
  <div class="min-h-screen bg-slate-50 md:flex md:items-center md:justify-center md:bg-slate-200 p-0 md:p-4 font-sans text-slate-900">
    <div @click="activeMenuId = null" class="w-full max-w-2xl mx-auto bg-white min-h-screen md:min-h-[850px] md:h-[850px] md:rounded-[3rem] shadow-2xl overflow-hidden flex flex-col relative border border-slate-100">
      
      <!-- Header -->
      <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-sm border-b border-slate-100 px-6 pt-4 pb-4 md:rounded-t-[3rem]">
        <div class="flex items-center justify-between mb-4">
          <button @click="goBack" class="text-slate-900 flex size-10 shrink-0 items-center justify-center rounded-full hover:bg-slate-50 transition-colors">
            <span class="material-symbols-outlined text-2xl">arrow_back</span>
          </button>
          <h2 class="text-slate-900 text-xl font-bold tracking-tight text-center flex-1">
            Gestión de Usuarios
          </h2>
          <div class="flex items-center justify-end">
            <button @click="logout" class="flex size-10 cursor-pointer items-center justify-center rounded-full bg-transparent text-slate-900 hover:bg-slate-50 transition-colors">
              <span class="material-symbols-outlined text-2xl">logout</span>
            </button>
          </div>
        </div>
        
        <div class="flex gap-2">
          <div class="relative flex-1 group">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
              <span class="material-symbols-outlined text-slate-400 text-[22px]">search</span>
            </div>
            <input
              v-model="searchTerm"
              @keyup.enter="fetchUsers(1)"
              class="block w-full rounded-xl border-none bg-slate-50 py-3.5 pl-11 pr-4 text-slate-900 placeholder:text-slate-400 focus:ring-1 focus:ring-primary focus:bg-white transition-all text-sm font-medium"
              placeholder="Buscar por nombre o email... (Enter para buscar)"
              type="text"
            />
          </div>
          <select 
            v-model="statusFilter"
            class="rounded-xl border-none bg-slate-50 text-sm font-bold text-slate-700 focus:ring-1 focus:ring-primary px-4"
          >
            <option value="all">Todos</option>
            <option value="active">Activos</option>
            <option value="inactive">Inactivos</option>
          </select>
        </div>
      </header>

      <!-- Main Content -->
      <main class="flex-1 px-6 space-y-5 pb-28 pt-6 overflow-y-auto custom-scrollbar no-scrollbar">
        <div class="flex justify-between items-center">
          <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">
            Usuarios Registrados
          </h3>
          <span v-if="pagination.total !== undefined" class="text-xs font-bold text-primary bg-cyan-50 px-2.5 py-1 rounded-md">
            {{ pagination.total }} total
          </span>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 gap-4">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
          <p class="text-slate-400 text-sm font-medium">Cargando usuarios...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="users.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
          <span class="material-symbols-outlined text-6xl text-slate-200 mb-2">person_off</span>
          <p class="text-slate-500 font-bold">No se encontraron usuarios</p>
          <p class="text-slate-400 text-sm">Prueba ajustando tus filtros</p>
        </div>

        <!-- Users List -->
        <div v-for="user in users" :key="user.id" 
          class="group relative flex items-center gap-4 rounded-3xl bg-white p-4 shadow-card border border-slate-50 hover:border-slate-200 hover:shadow-md transition-all"
          :class="activeMenuId === user.id ? 'z-40' : 'z-10'">
          
          <!-- Wrapper para opacidad (solo información del usuario) -->
          <div class="flex items-center gap-4 flex-1 min-w-0 transition-opacity" :class="{ 'opacity-60 grayscale-[0.5]': !user.active }">
            <div class="relative shrink-0 flex items-center justify-center rounded-2xl size-14 bg-slate-100 text-slate-400 border border-slate-200 overflow-hidden">
              <span v-if="!user.image" class="material-symbols-outlined text-3xl">account_circle</span>
              <img v-else :src="user.image" class="w-full h-full object-cover" />
            </div>

            <div class="flex flex-col flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-0.5">
                <p class="text-slate-900 text-base font-bold leading-tight truncate">
                  {{ user.name }}
                </p>
                <span v-if="user.role?.name === 'superadmin'" class="material-symbols-outlined text-amber-500 text-xs">verified</span>
              </div>
              <p class="text-sm text-slate-500 truncate mb-1.5">
                {{ user.email }}
              </p>
              <div class="flex items-center gap-3">
                <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide border"
                      :class="[
                        user.role?.name === 'superadmin' ? 'bg-amber-50 text-amber-700 border-amber-100' :
                        user.role?.name === 'admin' ? 'bg-purple-50 text-purple-700 border-purple-100' :
                        'bg-slate-50 text-slate-600 border-slate-100'
                      ]">
                  {{ user.role?.name === 'user' ? 'Cliente' : (user.role?.name || 'User') }}
                </span>
                <div class="flex items-center gap-1.5">
                  <div class="h-1.5 w-1.5 rounded-full" :class="user.active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-slate-300'"></div>
                  <span class="text-[10px] font-bold uppercase text-slate-400">{{ user.active ? 'Activo' : 'Inactivo' }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="relative">
            <button @click.stop="toggleMenu(user.id)" class="shrink-0 size-10 flex items-center justify-center rounded-full text-slate-300 hover:text-primary hover:bg-slate-50 transition-colors">
              <span class="material-symbols-outlined text-2xl">more_vert</span>
            </button>
            
            <!-- Context Menu -->
            <div v-if="activeMenuId === user.id" 
                 class="absolute right-0 top-10 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 z-30 py-2 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
              
              <!-- Opciones para usuarios ACTIVOS -->
              <template v-if="user.active">
                <button @click="openEditModal(user)" class="w-full px-4 py-2.5 text-left text-sm font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                  <span class="material-symbols-outlined text-[20px] text-blue-500">edit</span>
                  Editar Datos
                </button>
                <button @click="toggleUserStatus(user)" class="w-full px-4 py-2.5 text-left text-sm font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                  <span class="material-symbols-outlined text-[20px] text-amber-500">visibility_off</span>
                  Inactivar
                </button>
                <div class="border-t border-slate-50 my-1"></div>
                <button @click="deleteUser(user)" 
                        :disabled="user.role?.name === 'superadmin'"
                        class="w-full px-4 py-2.5 text-left text-sm font-bold text-red-500 hover:bg-red-50 flex items-center gap-3 disabled:opacity-30">
                  <span class="material-symbols-outlined text-[20px]">delete</span>
                  Eliminar usuario
                </button>
              </template>

              <!-- Opciones para usuarios INACTIVOS (Solo activar) -->
              <template v-else>
                <button @click="toggleUserStatus(user)" class="w-full px-4 py-2.5 text-left text-sm font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                  <span class="material-symbols-outlined text-[20px] text-emerald-500">visibility</span>
                  Activar Usuario
                </button>
              </template>

            </div>
          </div>
        </div>

        <!-- Paginación -->
        <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-2 pt-6 pb-10">
          <button 
            @click="fetchUsers(1)"
            :disabled="pagination.current_page === 1"
            class="size-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 disabled:grayscale transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined">first_page</span>
          </button>
          <button 
            @click="fetchUsers(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="size-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined">chevron_left</span>
          </button>
          
          <div class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-900 shadow-sm flex items-center gap-2">
            <span class="text-primary">{{ pagination.current_page }}</span>
            <span class="text-slate-300">/</span>
            <span>{{ pagination.last_page }}</span>
          </div>

          <button 
            @click="fetchUsers(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="size-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
          <button 
            @click="fetchUsers(pagination.last_page)"
            :disabled="pagination.current_page === pagination.last_page"
            class="size-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 disabled:opacity-30 disabled:grayscale transition-all hover:border-primary hover:text-primary"
          >
            <span class="material-symbols-outlined">last_page</span>
          </button>
        </div>
      </main>

      <!-- FAB -->
      <div class="absolute bottom-24 right-6 z-30">
        <button @click="openCreateModal" class="flex items-center justify-center gap-2 bg-primary hover:opacity-90 text-white rounded-2xl px-5 py-4 shadow-xl shadow-primary/20 transition-transform active:scale-95">
          <span class="material-symbols-outlined text-2xl">person_add</span>
          <span class="font-bold text-sm tracking-wide">Nuevo</span>
        </button>
      </div>

      <!-- Navigation -->
      <div class="absolute bottom-0 z-20 w-full bg-white border-t border-slate-100 pb-8 pt-4 px-6 flex justify-between items-center text-[10px] font-bold tracking-wide text-slate-500 uppercase md:rounded-b-[3rem]">
        <button @click="router.push('/')" class="flex flex-col items-center gap-1.5 hover:text-primary transition-colors">
          <span class="material-symbols-outlined text-[26px]">dashboard</span>
          Inicio
        </button>
        <button class="flex flex-col items-center gap-1.5 text-primary transition-colors">
          <span class="material-symbols-outlined text-[26px] fill-icon">group</span>
          Usuarios
        </button>
        <button @click="router.push('/superadmin/categories')" class="flex flex-col items-center gap-1.5 hover:text-primary transition-colors">
          <span class="material-symbols-outlined text-[26px]">category</span>
          Categorías
        </button>
        <button class="flex flex-col items-center gap-1.5 hover:text-primary transition-colors">
          <span class="material-symbols-outlined text-[26px]">settings</span>
          Ajustes
        </button>
      </div>

      <!-- User Modal -->
      <div v-if="isModalOpen" class="absolute inset-0 z-50 flex items-end md:items-center justify-center bg-slate-900/60 backdrop-blur-sm p-0 md:p-6">
        <div @click.stop class="w-full max-w-md bg-white rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-2xl flex flex-col max-h-[90%] animate-in slide-in-from-bottom duration-300">
          <div class="p-6 md:p-8 flex-1 overflow-y-auto no-scrollbar">
            <div class="flex items-center justify-between mb-8">
              <div class="flex flex-col">
                <h2 class="text-2xl font-extrabold text-slate-900">
                  {{ isEditing ? 'Editar Usuario' : 'Nuevo Usuario' }}
                </h2>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">
                  {{ isEditing ? 'Modifica los datos del perfil' : 'Registra un nuevo colaborador o cliente' }}
                </p>
              </div>
              <button @click="isModalOpen = false" :disabled="isSaving" class="size-10 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="material-symbols-outlined">close</span>
              </button>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
              <div class="space-y-4">
                <label class="block">
                  <span class="text-sm font-bold text-slate-900 ml-1">Nombre Completo</span>
                  <input v-model="form.name" type="text" 
                    :disabled="isSaving"
                    class="mt-1 w-full rounded-2xl bg-slate-50 border-none h-14 px-4 text-base font-medium focus:ring-1 focus:ring-primary focus:bg-white transition-all shadow-input disabled:opacity-60"
                    placeholder="Ej. Juan Pérez" />
                  <p v-if="errors.name" class="text-xs text-red-500 mt-1 ml-1 font-medium">{{ errors.name[0] }}</p>
                </label>

                <label class="block">
                  <span class="text-sm font-bold text-slate-900 ml-1">Correo Electrónico</span>
                  <input v-model="form.email" type="email" 
                    :disabled="isSaving"
                    class="mt-1 w-full rounded-2xl bg-slate-50 border-none h-14 px-4 text-base font-medium focus:ring-1 focus:ring-primary focus:bg-white transition-all shadow-input disabled:opacity-60"
                    placeholder="correo@ejemplo.com" />
                  <p v-if="errors.email" class="text-xs text-red-500 mt-1 ml-1 font-medium">{{ errors.email[0] }}</p>
                </label>

                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <span class="text-sm font-bold text-slate-900 ml-1">Rol</span>
                    <select v-model="form.role" :disabled="isSaving" class="w-full rounded-2xl bg-slate-50 border-none h-14 px-4 text-base font-bold focus:ring-1 focus:ring-primary focus:bg-white transition-all appearance-none disabled:opacity-60">
                      <option value="user">Cliente</option>
                      <option value="admin">Administrador</option>
                      <option value="superadmin" v-if="isEditing && form.role === 'superadmin'">Superadmin</option>
                    </select>
                  </div>
                  <div class="space-y-2 flex flex-col justify-end">
                    <label class="flex items-center gap-3 bg-slate-50 h-14 rounded-2xl px-4 cursor-pointer hover:bg-slate-100 transition-colors" :class="{ 'opacity-60 cursor-not-allowed': isSaving }">
                      <input type="checkbox" v-model="form.active" :disabled="isSaving" class="rounded text-primary focus:ring-primary w-5 h-5" />
                      <span class="text-sm font-bold text-slate-900">Activo</span>
                    </label>
                  </div>
                </div>

                <label class="block">
                  <span class="text-sm font-bold text-slate-900 ml-1">
                    {{ isEditing ? 'Nueva Contraseña (opcional)' : 'Contraseña' }}
                  </span>
                  <input v-model="form.password" type="password" 
                    :disabled="isSaving"
                    class="mt-1 w-full rounded-2xl bg-slate-50 border-none h-14 px-4 text-base font-medium focus:ring-1 focus:ring-primary focus:bg-white transition-all shadow-input disabled:opacity-60"
                    placeholder="••••••••" />
                  <p v-if="!isEditing" class="text-[10px] text-slate-400 mt-1 ml-1 font-bold italic">
                    * Si se deja vacío, el sistema generará una y la enviará por correo.
                  </p>
                  <p v-if="errors.password" class="text-xs text-red-500 mt-1 ml-1 font-medium">{{ errors.password[0] }}</p>
                </label>
              </div>

              <div class="pt-4 flex flex-col gap-3">
                <button type="submit" 
                        :disabled="isSaving"
                        class="w-full bg-primary text-white font-bold text-lg h-16 rounded-2xl transition-all active:scale-[0.98] shadow-lg shadow-cyan-500/25 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-wait">
                  <span v-if="isSaving" class="animate-spin material-symbols-outlined">sync</span>
                  <span v-else class="material-symbols-outlined">save</span>
                  {{ isSaving ? 'Guardando...' : (isEditing ? 'Guardar Cambios' : 'Crear Usuario') }}
                </button>
                <button type="button" @click="isModalOpen = false" 
                        :disabled="isSaving"
                        class="w-full bg-slate-100 text-slate-600 font-bold text-lg h-16 rounded-2xl transition-all hover:bg-slate-200 disabled:opacity-50">
                  Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
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

.shadow-card {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
}

.shadow-input {
  box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);
}

.animate-in {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.slide-in-from-bottom {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}

@media (min-width: 768px) {
  .slide-in-from-bottom {
    animation: fadeIn 0.3s ease-out;
  }
}
</style>
