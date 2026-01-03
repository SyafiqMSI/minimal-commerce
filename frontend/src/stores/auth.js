import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/axios'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const token = ref(localStorage.getItem('access_token') || null)
    const isLoading = ref(false)

    const isAuthenticated = computed(() => !!token.value && !!user.value)
    const isAdmin = computed(() => user.value?.role === 'admin')
    const isUser = computed(() => user.value?.role === 'user')

    async function login(email, password) {
        isLoading.value = true
        try {
            const response = await api.post('/login', { email, password })
            token.value = response.data.token
            user.value = response.data.user
            localStorage.setItem('access_token', response.data.token)
            localStorage.setItem('auth_user', JSON.stringify(response.data.user))
            return { success: true }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Login failed',
                errors: error.response?.data?.errors || {}
            }
        } finally {
            isLoading.value = false
        }
    }

    async function register(name, email, password, password_confirmation) {
        isLoading.value = true
        try {
            const response = await api.post('/register', {
                name,
                email,
                password,
                password_confirmation
            })
            token.value = response.data.token
            user.value = response.data.user
            localStorage.setItem('access_token', response.data.token)
            localStorage.setItem('auth_user', JSON.stringify(response.data.user))
            return { success: true }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Registration failed',
                errors: error.response?.data?.errors || {}
            }
        } finally {
            isLoading.value = false
        }
    }

    async function logout() {
        try {
            await api.post('/logout')
        } catch (error) {
            console.error('Logout error:', error)
        } finally {
            token.value = null
            user.value = null
            localStorage.removeItem('access_token')
            localStorage.removeItem('auth_user')
        }
    }

    async function checkAuth() {
        const storedToken = localStorage.getItem('access_token')
        const storedUser = localStorage.getItem('auth_user')

        if (storedToken && storedUser) {
            token.value = storedToken
            try {
                user.value = JSON.parse(storedUser)
                const response = await api.get('/user')
                user.value = response.data.user
                localStorage.setItem('auth_user', JSON.stringify(response.data.user))
            } catch (error) {
                token.value = null
                user.value = null
                localStorage.removeItem('access_token')
                localStorage.removeItem('auth_user')
            }
        }
    }

    return {
        user,
        token,
        isLoading,
        isAuthenticated,
        isAdmin,
        isUser,
        login,
        register,
        logout,
        checkAuth
    }
})
