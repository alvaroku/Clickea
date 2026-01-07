import { createRouter, createWebHistory } from 'vue-router'
import Welcome from '../views/Welcome.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import AdminServices from '../views/admin/Services.vue'
import AssignedServices from '../views/admin/AssignedServices.vue'
import UserServices from '../views/user/Services.vue'
import RequestService from '../views/user/RequestService.vue'
import RequestedServices from '../views/user/RequestedServices.vue'
import SuperAdminDashboard from '../views/superadmin/Dashboard.vue'
import SuperAdminCategories from '../views/superadmin/Categories.vue'

const routes = [
  {
    path: '/',
    name: 'welcome',
    component: Welcome
  },
  {
    path: '/login',
    name: 'login',
    component: Login
  },
  {
    path: '/register/:role',
    name: 'register',
    component: Register,
    props: true
  },
  {
    path: '/admin/services',
    name: 'admin-services',
    component: AdminServices,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/assigned-services',
    name: 'admin-assigned-services',
    component: AssignedServices,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/user/services',
    name: 'user-services',
    component: UserServices,
    meta: { requiresAuth: true, role: 'user' }
  },
  {
    path: '/user/services/:id/request',
    name: 'user-request-service',
    component: RequestService,
    meta: { requiresAuth: true, role: 'user' },
    props: true
  },
  {
    path: '/user/requests',
    name: 'user-requests',
    component: RequestedServices,
    meta: { requiresAuth: true, role: 'user' }
  },
  {
    path: '/superadmin/dashboard',
    name: 'superadmin-dashboard',
    component: SuperAdminDashboard,
    meta: { requiresAuth: true, role: 'superadmin' }
  },
  {
    path: '/superadmin/categories',
    name: 'superadmin-categories',
    component: SuperAdminCategories,
    meta: { requiresAuth: true, role: 'superadmin' }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  const userRole = user.role?.name

  if (to.meta.requiresAuth && !token) {
    next({ name: 'login' })
  } else if (to.meta.role) {
    const allowedRoles = Array.isArray(to.meta.role) ? to.meta.role : [to.meta.role]
    // O permitir superadmin en cualquier ruta de admin
    if (allowedRoles.includes(userRole) || userRole === 'superadmin') {
      next()
    } else {
      next({ name: 'welcome' })
    }
  } else {
    next()
  }
})

export default router
