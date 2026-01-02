import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/axios'

export const useCartStore = defineStore('cart', () => {
    const items = ref([])
    const total = ref(0)
    const totalItems = ref(0)
    const isLoading = ref(false)

    async function fetchCart() {
        isLoading.value = true
        try {
            const response = await api.get('/cart')
            items.value = response.data.data.items
            total.value = response.data.data.total
            totalItems.value = response.data.data.total_items
        } catch (error) {
            console.error('Error fetching cart:', error)
        } finally {
            isLoading.value = false
        }
    }

    async function addToCart(productId, quantity = 1) {
        isLoading.value = true
        try {
            const response = await api.post('/cart', { product_id: productId, quantity })
            items.value = response.data.data.items
            total.value = response.data.data.total
            totalItems.value = response.data.data.total_items
            return { success: true, message: response.data.message }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to add to cart'
            }
        } finally {
            isLoading.value = false
        }
    }

    async function updateQuantity(cartItemId, quantity) {
        isLoading.value = true
        try {
            const response = await api.put(`/cart/${cartItemId}`, { quantity })
            items.value = response.data.data.items
            total.value = response.data.data.total
            totalItems.value = response.data.data.total_items
            return { success: true }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to update quantity'
            }
        } finally {
            isLoading.value = false
        }
    }

    async function removeFromCart(cartItemId) {
        isLoading.value = true
        try {
            const response = await api.delete(`/cart/${cartItemId}`)
            items.value = response.data.data.items
            total.value = response.data.data.total
            totalItems.value = response.data.data.total_items
            return { success: true }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to remove from cart'
            }
        } finally {
            isLoading.value = false
        }
    }

    async function clearCart() {
        isLoading.value = true
        try {
            await api.delete('/cart')
            items.value = []
            total.value = 0
            totalItems.value = 0
            return { success: true }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to clear cart'
            }
        } finally {
            isLoading.value = false
        }
    }

    function isInCart(productId) {
        return items.value.some(item => item.product_id === productId)
    }

    function resetCart() {
        items.value = []
        total.value = 0
        totalItems.value = 0
    }

    return {
        items,
        total,
        totalItems,
        isLoading,
        fetchCart,
        addToCart,
        updateQuantity,
        removeFromCart,
        clearCart,
        isInCart,
        resetCart
    }
})


