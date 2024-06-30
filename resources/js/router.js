import { createWebHistory, createRouter } from 'vue-router'
const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('../js/components/Dashboard.vue'),
  },
  {
    path: '/create',
    name: 'create',
    component: () => import('../js/components/CreateCustomization.vue'),
  },
  {
    path: '/:id',
    name: 'edit',
    component: () => import('../js/components/Edit.vue'),
  },
  {
    path: '/plans',
    name: 'plans',
    component: () => import('../js/components/Plans.vue'),
  },
  {
    path: '/Settings',
    name: 'Settings',
    component: () => import('../js/components/Settings.vue'),
  },
  {
    path: '/Support',
    name: 'Support',
    component: () => import('../js/components/Support.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
