import { createRouter, createWebHistory } from 'vue-router'
import NProgress from 'nprogress'
import { useAuthStore } from '@/stores/auth'

const routes = [
    {
        path: '/',
        name: 'Landing',
        component: () => import('@/pages/LandingPage.vue'),
        meta: { requiresAuth: false }
    },
    {
        path: '/login',
        name: 'Login',
        component: () => import('@/pages/auth/LoginPage.vue'),
        meta: { requiresAuth: false, guest: true }
    },
    {
        path: '/register',
        name: 'Register',
        component: () => import('@/pages/auth/RegisterPage.vue'),
        meta: { requiresAuth: false, guest: true }
    },
    {
        path: '/products',
        name: 'Products',
        component: () => import('@/pages/ProductsPage.vue'),
        meta: { requiresAuth: false }
    },
    {
        path: '/products/:id',
        name: 'ProductDetail',
        component: () => import('@/pages/ProductDetailPage.vue'),
        meta: { requiresAuth: false }
    },
    {
        path: '/admin',
        name: 'Admin',
        component: () => import('@/layouts/index.vue'),
        meta: { requiresAuth: true, requiresAdmin: true },
        children: [
            {
                path: '',
                name: 'AdminDashboard',
                component: () => import('@/pages/admin/DashboardPage.vue')
            },
            {
                path: 'orders',
                name: 'AdminOrders',
                component: () => import('@/pages/admin/OrdersPage.vue')
            },
            {
                path: 'products',
                name: 'AdminProducts',
                component: () => import('@/pages/admin/ProductsPage.vue')
            },
            {
                path: 'products/create',
                name: 'AdminProductCreate',
                component: () => import('@/pages/admin/ProductFormPage.vue')
            },
            {
                path: 'products/:id/edit',
                name: 'AdminProductEdit',
                component: () => import('@/pages/admin/ProductFormPage.vue')
            },
            {
                path: 'categories',
                name: 'AdminCategories',
                component: () => import('@/pages/admin/CategoriesPage.vue')
            },
            {
                path: 'categories/create',
                name: 'AdminCategoryCreate',
                component: () => import('@/pages/admin/CategoryFormPage.vue')
            },
            {
                path: 'categories/:id/edit',
                name: 'AdminCategoryEdit',
                component: () => import('@/pages/admin/CategoryFormPage.vue')
            }
        ]
    },
    {
        path: '/user',
        name: 'User',
        component: () => import('@/layouts/index.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'UserDashboard',
                component: () => import('@/pages/user/DashboardPage.vue')
            },
            {
                path: 'cart',
                name: 'UserCart',
                component: () => import('@/pages/user/CartPage.vue')
            },
            {
                path: 'wishlist',
                name: 'UserWishlist',
                component: () => import('@/pages/user/WishlistPage.vue')
            },
            {
                path: 'orders',
                name: 'UserOrders',
                component: () => import('@/pages/user/OrdersPage.vue')
            },
            {
                path: 'orders/:id',
                name: 'UserOrderDetail',
                component: () => import('@/pages/user/OrderDetailPage.vue')
            },
            {
                path: 'checkout',
                name: 'UserCheckout',
                component: () => import('@/pages/user/CheckoutPage.vue')
            }
        ]
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: () => import('@/pages/NotFoundPage.vue')
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach(async (to, from, next) => {
    NProgress.start()

    const authStore = useAuthStore()
    const isAuthenticated = authStore.isAuthenticated
    const isAdmin = authStore.isAdmin

    if (to.meta.guest && isAuthenticated) {
        NProgress.done()
        return next(isAdmin ? '/admin' : '/user')
    }

    if (to.meta.requiresAuth && !isAuthenticated) {
        NProgress.done()
        return next('/login')
    }

    if (to.meta.requiresAdmin && !isAdmin) {
        NProgress.done()
        return next('/user')
    }

    next()
})

router.afterEach(() => {
    NProgress.done()
})

export default router
