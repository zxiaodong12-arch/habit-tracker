import { createRouter, createWebHashHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('@/views/Home.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/Login.vue'),
        meta: { requiresAuth: false }
    }
]

// 获取base路径，默认为'/'
const base = import.meta.env.BASE_URL || '/'

const router = createRouter({
    // 始终使用Hash模式，兼容所有静态托管服务
    history: createWebHashHistory(base),
    routes
})

// 路由守卫
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login')
    } else if (to.path === '/login' && authStore.isAuthenticated) {
        next('/')
    } else {
        next()
    }
})

export default router