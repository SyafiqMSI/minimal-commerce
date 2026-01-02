<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCategoryStore } from '@/stores/category'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { toast } from 'vue-sonner'
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const categoryStore = useCategoryStore()

const isEdit = computed(() => !!route.params.id)

const form = ref({
    name: '',
    description: ''
})

const errors = ref({})
const isSubmitting = ref(false)

onMounted(async () => {
    if (isEdit.value) {
        const category = await categoryStore.fetchCategory(route.params.id)
        if (category) {
            form.value = {
                name: category.name,
                description: category.description || ''
            }
        } else {
            router.push('/admin/categories')
        }
    }
})

const validateForm = () => {
    errors.value = {}
    
    if (!form.value.name.trim()) {
        errors.value.name = 'Category name is required'
    }
    
    return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
    if (!validateForm()) return
    
    isSubmitting.value = true
    
    let result
    if (isEdit.value) {
        result = await categoryStore.updateCategory(route.params.id, form.value)
    } else {
        result = await categoryStore.createCategory(form.value)
    }
    
    isSubmitting.value = false
    
    if (result.success) {
        toast.success(isEdit.value ? 'Category updated successfully' : 'Category created successfully')
        router.push('/admin/categories')
    } else {
        if (result.errors) {
            errors.value = result.errors
        }
        toast.error(result.message)
    }
}
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ isEdit ? 'Edit Category' : 'Create Category' }}</h1>
                <p class="text-muted-foreground mt-2">{{ isEdit ? 'Update category details' : 'Add a new product category' }}</p>
            </div>
            <Button variant="outline" @click="router.back()" class="gap-2">
                <ArrowLeft class="w-4 h-4" />
                Back
            </Button>
        </div>

        <Card class="border-border/50">
            <CardHeader>
                <CardTitle>Category Details</CardTitle>
                <CardDescription>Fill in the information below</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div class="space-y-2">
                        <Label for="name">Category Name</Label>
                        <Input 
                            id="name"
                            v-model="form.name"
                            placeholder="Enter category name"
                            :class="{ 'border-destructive': errors.name }"
                        />
                        <p v-if="errors.name" class="text-sm text-destructive">
                            {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description (Optional)</Label>
                        <Textarea 
                            id="description"
                            v-model="form.description"
                            placeholder="Enter category description"
                            rows="4"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t">
                        <Button type="button" variant="outline" @click="router.back()">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="isSubmitting" class="gap-2">
                            <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                            <Save v-else class="w-4 h-4" />
                            {{ isSubmitting ? 'Saving...' : (isEdit ? 'Update Category' : 'Create Category') }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>

