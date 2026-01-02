import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'

export const useCategoryStore = defineStore('category', () => {
    const categories = ref([])
    const currentCategory = ref(null)
    const isLoading = ref(false)

    async function fetchCategories() {
        isLoading.value = true
        try {
            const response = await api.get('/categories')
            categories.value = response.data.data
            return categories.value
        } catch (error) {
            console.error('Error fetching categories:', error)
            return []
        } finally {
            isLoading.value = false
        }
    }

    async function fetchCategory(id) {
        isLoading.value = true
        try {
            const response = await api.get(`/categories/${id}`)
            currentCategory.value = response.data.data
            return response.data.data
        } catch (error) {
            console.error('Error fetching category:', error)
            return null
        } finally {
            isLoading.value = false
        }
    }

    async function createCategory(data) {
        isLoading.value = true
        try {
            const response = await api.post('/categories', data)
            categories.value.push(response.data.data)
            return { success: true, data: response.data.data }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to create category',
                errors: error.response?.data?.errors || {}
            }
        } finally {
            isLoading.value = false
        }
    }

    async function updateCategory(id, data) {
        isLoading.value = true
        try {
            const response = await api.put(`/categories/${id}`, data)
            const index = categories.value.findIndex(c => c.id === id)
            if (index !== -1) {
                categories.value[index] = response.data.data
            }
            return { success: true, data: response.data.data }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to update category',
                errors: error.response?.data?.errors || {}
            }
        } finally {
            isLoading.value = false
        }
    }

    async function deleteCategory(id) {
        isLoading.value = true
        try {
            await api.delete(`/categories/${id}`)
            categories.value = categories.value.filter(c => c.id !== id)
            return { success: true }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to delete category'
            }
        } finally {
            isLoading.value = false
        }
    }

    return {
        categories,
        currentCategory,
        isLoading,
        fetchCategories,
        fetchCategory,
        createCategory,
        updateCategory,
        deleteCategory
    }
})


