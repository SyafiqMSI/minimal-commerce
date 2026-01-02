import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'

export const useOrderStore = defineStore('order', () => {
    const orders = ref([])
    const currentOrder = ref(null)
    const isLoading = ref(false)
    const stats = ref(null)

    async function fetchOrders(status = '') {
        isLoading.value = true
        try {
            const params = new URLSearchParams()
            if (status) params.append('status', status)
            
            const response = await api.get(`/orders?${params.toString()}`)
            orders.value = response.data.data
        } catch (error) {
            console.error('Error fetching orders:', error)
        } finally {
            isLoading.value = false
        }
    }

    async function fetchOrder(orderId) {
        isLoading.value = true
        try {
            const response = await api.get(`/orders/${orderId}`)
            currentOrder.value = response.data.data
            return response.data.data
        } catch (error) {
            console.error('Error fetching order:', error)
            return null
        } finally {
            isLoading.value = false
        }
    }

    async function createOrder(orderData) {
        isLoading.value = true
        try {
            const response = await api.post('/orders', orderData)
            return { success: true, data: response.data.data, message: response.data.message }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to create order',
                errors: error.response?.data?.errors || {}
            }
        } finally {
            isLoading.value = false
        }
    }

    async function payOrder(orderId) {
        isLoading.value = true
        try {
            const response = await api.post(`/orders/${orderId}/pay`)
            if (currentOrder.value && currentOrder.value.id === orderId) {
                currentOrder.value = response.data.data
            }
            return { success: true, data: response.data.data, message: response.data.message }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Payment failed'
            }
        } finally {
            isLoading.value = false
        }
    }

    async function cancelOrder(orderId) {
        isLoading.value = true
        try {
            const response = await api.post(`/orders/${orderId}/cancel`)
            if (currentOrder.value && currentOrder.value.id === orderId) {
                currentOrder.value = response.data.data
            }
            return { success: true, data: response.data.data, message: response.data.message }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to cancel order'
            }
        } finally {
            isLoading.value = false
        }
    }

    async function fetchAdminOrders(filters = {}) {
        isLoading.value = true
        try {
            const params = new URLSearchParams()
            if (filters.status) params.append('status', filters.status)
            if (filters.payment_status) params.append('payment_status', filters.payment_status)
            if (filters.search) params.append('search', filters.search)
            
            const response = await api.get(`/admin/orders?${params.toString()}`)
            orders.value = response.data.data
        } catch (error) {
            console.error('Error fetching admin orders:', error)
        } finally {
            isLoading.value = false
        }
    }

    async function updateOrderStatus(orderId, status) {
        isLoading.value = true
        try {
            const response = await api.put(`/admin/orders/${orderId}/status`, { status })
            return { success: true, data: response.data.data, message: response.data.message }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to update order status'
            }
        } finally {
            isLoading.value = false
        }
    }

    async function fetchStats() {
        try {
            const response = await api.get('/admin/orders/stats')
            stats.value = response.data.data
            return response.data.data
        } catch (error) {
            console.error('Error fetching stats:', error)
            return null
        }
    }

    function resetOrders() {
        orders.value = []
        currentOrder.value = null
        stats.value = null
    }

    return {
        orders,
        currentOrder,
        isLoading,
        stats,
        fetchOrders,
        fetchOrder,
        createOrder,
        payOrder,
        cancelOrder,
        fetchAdminOrders,
        updateOrderStatus,
        fetchStats,
        resetOrders
    }
})

