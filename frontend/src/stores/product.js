import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/axios'

export const useProductStore = defineStore('product', () => {
    const products = ref([])
    const currentProduct = ref(null)
    const categories = ref([])
    const isLoading = ref(false)
    const filters = ref({
        search: '',
        category_id: '',
        min_price: '',
        max_price: ''
    })

    async function fetchProducts() {
        isLoading.value = true
        try {
            const params = new URLSearchParams()
            if (filters.value.search) params.append('search', filters.value.search)
            if (filters.value.category_id) params.append('category_id', filters.value.category_id)
            if (filters.value.min_price) params.append('min_price', filters.value.min_price)
            if (filters.value.max_price) params.append('max_price', filters.value.max_price)

            const response = await api.get(`/products?${params.toString()}`)
            products.value = response.data.data
        } catch (error) {
            console.error('Error fetching products:', error)
        } finally {
            isLoading.value = false
        }
    }

    async function fetchProduct(id) {
        isLoading.value = true
        try {
            const response = await api.get(`/products/${id}`)
            currentProduct.value = response.data.data
            return response.data.data
        } catch (error) {
            console.error('Error fetching product:', error)
            return null
        } finally {
            isLoading.value = false
        }
    }

    async function createProduct(formData) {
        isLoading.value = true
        try {
            const response = await api.post('/products', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            return { success: true, data: response.data.data }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to create product',
                errors: error.response?.data?.errors || {}
            }
        } finally {
            isLoading.value = false
        }
    }

    async function updateProduct(id, formData) {
        isLoading.value = true
        try {
            const response = await api.post(`/products/${id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            return { success: true, data: response.data.data }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to update product',
                errors: error.response?.data?.errors || {}
            }
        } finally {
            isLoading.value = false
        }
    }

    async function deleteProduct(id) {
        isLoading.value = true
        try {
            await api.delete(`/products/${id}`)
            products.value = products.value.filter(p => p.id !== id)
            return { success: true }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to delete product'
            }
        } finally {
            isLoading.value = false
        }
    }

    async function fetchCategories() {
        try {
            const response = await api.get('/categories')
            categories.value = response.data.data
        } catch (error) {
            console.error('Error fetching categories:', error)
        }
    }

    function setFilters(newFilters) {
        filters.value = { ...filters.value, ...newFilters }
    }

    function resetFilters() {
        filters.value = {
            search: '',
            category_id: '',
            min_price: '',
            max_price: ''
        }
    }

    return {
        products,
        currentProduct,
        categories,
        isLoading,
        filters,
        fetchProducts,
        fetchProduct,
        createProduct,
        updateProduct,
        deleteProduct,
        fetchCategories,
        setFilters,
        resetFilters
    }
})
