import { createRouter, createWebHistory } from 'vue-router'
import { pinia } from '@/stores'
import { useAuthStore } from '@/stores/auth'
import { ROUTE_NAMES } from '@/router/routeNames'

import AppLayout from '@/layouts/AppLayout.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'

const routes = [
  {
    path: '/',
    component: AppLayout,
    meta: {
      requiresAuth: true,
    },
    children: [
      {
        path: '',
        redirect: { name: ROUTE_NAMES.feed },
      },
      {
        path: 'feed',
        name: ROUTE_NAMES.feed,
        component: () => import('@/views/app/FeedView.vue'),
        meta: { navItem: 'feed' },
      },
      {
        path: 'posts/:postId',
        name: ROUTE_NAMES.postDetails,
        component: () => import('@/views/app/PostDetailsView.vue'),
        meta: { navItem: 'feed' },
      },
      {
        path: 'create',
        name: ROUTE_NAMES.createPost,
        component: () => import('@/views/app/CreatePostView.vue'),
        meta: { navItem: 'create' },
      },
      {
        path: 'discover',
        name: ROUTE_NAMES.discover,
        component: () => import('@/views/app/DiscoverView.vue'),
        meta: { navItem: 'discover' },
      },
      {
        path: 'profile',
        name: ROUTE_NAMES.profile,
        component: () => import('@/views/app/ProfileView.vue'),
        meta: { navItem: 'profile' },
      },
      {
        path: 'profile/edit',
        name: ROUTE_NAMES.editProfile,
        component: () => import('@/views/app/EditProfileView.vue'),
        meta: { navItem: 'profile' },
      },
      {
        path: 'profile/list/:type',
        name: ROUTE_NAMES.profileConnections,
        component: () => import('@/views/app/ProfileConnectionsView.vue'),
        meta: { navItem: 'profile' },
      },
      {
        path: 'users/:username',
        name: ROUTE_NAMES.userProfile,
        component: () => import('@/views/app/ProfileView.vue'),
        meta: { navItem: 'profile' },
      },
      {
        path: 'users/:username/list/:type',
        name: ROUTE_NAMES.userConnections,
        component: () => import('@/views/app/ProfileConnectionsView.vue'),
        meta: { navItem: 'profile' },
      },
    ],
  },
  {
    path: '/',
    component: AuthLayout,
    meta: {
      requiresGuest: true,
    },
    children: [
      {
        path: 'login',
        name: ROUTE_NAMES.login,
        component: () => import('@/views/auth/LoginView.vue'),
      },
      {
        path: 'register',
        name: ROUTE_NAMES.register,
        component: () => import('@/views/auth/RegisterView.vue'),
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: ROUTE_NAMES.notFound,
    component: () => import('@/views/NotFoundView.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

const authStore = useAuthStore(pinia)

router.beforeEach(async (to) => {
  if (!authStore.hydrated) {
    await authStore.hydrateAuthState()
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return {
      name: ROUTE_NAMES.login,
      query: { redirect: to.fullPath },
    }
  }

  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    return { name: ROUTE_NAMES.feed }
  }

  return true
})

export default router
