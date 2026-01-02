import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'

export const useWishlistStore = defineStore('wishlist', () => {
    const items = ref([])
    const isLoading = ref(false)

    async function fetchWishlist() {
        isLoading.value = true
        try {
            const response = await api.get('/wishlist')
            items.value = response.data.data
        } catch (error) {
            console.error('Error fetching wishlist:', error)
        } finally {
            isLoading.value = false
        }
    }

    async function addToWishlist(productId) {
        isLoading.value = true
        try {
            const response = await api.post('/wishlist', { product_id: productId })
            await fetchWishlist()
            return { success: true, message: response.data.message }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to add to wishlist'
            }
        } finally {
            isLoading.value = false
        }
    }

    async function removeFromWishlist(wishlistId) {
        isLoading.value = true
        try {
            await api.delete(`/wishlist/${wishlistId}`)
            items.value = items.value.filter(item => item.id !== wishlistId)
            return { success: true }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to remove from wishlist'
            }
        } finally {
            isLoading.value = false
        }
    }

    async function removeByProductId(productId) {
        isLoading.value = true
        try {
            await api.delete(`/wishlist/product/${productId}`)
            items.value = items.value.filter(item => item.product_id !== productId)
            return { success: true }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to remove from wishlist'
            }
        } finally {
            isLoading.value = false
        }
    }

    async function checkInWishlist(productId) {
        try {
            const response = await api.get(`/wishlist/check/${productId}`)
            return response.data.in_wishlist
        } catch (error) {
            return false
        }
    }

    function isInWishlist(productId) {
        return items.value.some(item => item.product_id === productId)
    }

    function resetWishlist() {
        items.value = []
    }

    return {
        items,
        isLoading,
        fetchWishlist,
        addToWishlist,
        removeFromWishlist,
        removeByProductId,
        checkInWishlist,
        isInWishlist,
        resetWishlist
    }
})


