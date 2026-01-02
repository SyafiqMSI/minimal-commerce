<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore } from '@/stores/product'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { toast } from 'vue-sonner'
import { ArrowLeft, Save, Loader2, Upload, X, Package } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()

const isEdit = computed(() => !!route.params.id)

const form = ref({
    name: '',
    description: '',
    price: '',
    quantity: '',
    category_id: '',
    image: null
})

const imagePreview = ref(null)
const errors = ref({})
const isSubmitting = ref(false)

onMounted(async () => {
    await productStore.fetchCategories()
    
    if (isEdit.value) {
        const product = await productStore.fetchProduct(route.params.id)
        if (product) {
            form.value = {
                name: product.name,
                description: product.description,
                price: product.price,
                quantity: product.quantity,
                category_id: String(product.category_id),
                image: null
            }
            if (product.image_url) {
                imagePreview.value = product.image_url
            }
        } else {
            router.push('/admin/products')
        }
    }
})

const handleImageChange = (event) => {
    const file = event.target.files?.[0]
    if (file) {
        if (file.size > 10 * 1024 * 1024) {
            toast.error('File size must be less than 10MB')
            event.target.value = ''
            return
        }
        form.value.image = file
        imagePreview.value = URL.createObjectURL(file)
    }
}

const removeImage = () => {
    form.value.image = null
    imagePreview.value = null
}

const validateForm = () => {
    errors.value = {}
    
    if (!form.value.name) {
        errors.value.name = 'Name is required'
    }
    if (!form.value.description) {
        errors.value.description = 'Description is required'
    }
    if (!form.value.price) {
        errors.value.price = 'Price is required'
    } else if (isNaN(form.value.price) || Number(form.value.price) < 0) {
        errors.value.price = 'Price must be a valid positive number'
    }
    if (!form.value.quantity) {
        errors.value.quantity = 'Quantity is required'
    } else if (isNaN(form.value.quantity) || Number(form.value.quantity) < 0) {
        errors.value.quantity = 'Quantity must be a valid non-negative number'
    }
    if (!form.value.category_id) {
        errors.value.category_id = 'Category is required'
    }
    
    return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
    if (!validateForm()) return
    
    isSubmitting.value = true
    
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('description', form.value.description)
    formData.append('price', form.value.price)
    formData.append('quantity', form.value.quantity)
    formData.append('category_id', form.value.category_id)
    if (form.value.image) {
        formData.append('image', form.value.image)
    }
    
    let result
    if (isEdit.value) {
        result = await productStore.updateProduct(route.params.id, formData)
    } else {
        result = await productStore.createProduct(formData)
    }
    
    isSubmitting.value = false
    
    if (result.success) {
        toast.success(isEdit.value ? 'Product updated successfully' : 'Product created successfully')
        router.push('/admin/products')
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
                <h1 class="text-2xl font-bold text-foreground">{{ isEdit ? 'Edit Product' : 'Create Product' }}</h1>
                <p class="text-muted-foreground mt-2">{{ isEdit ? 'Update product details' : 'Add a new product to your store' }}</p>
            </div>
            <Button variant="outline" @click="router.back()" class="gap-2">
                <ArrowLeft class="w-4 h-4" />
                Back
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Product Details</CardTitle>
                <CardDescription>Fill in the information below</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <Label for="name">Product Name</Label>
                                <Input 
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Enter product name"
                                    :class="{ 'border-destructive': errors.name }"
                                />
                                <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Textarea 
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Enter product description"
                                    rows="4"
                                    :class="{ 'border-destructive': errors.description }"
                                />
                                <p v-if="errors.description" class="text-sm text-destructive">{{ errors.description }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <Label for="price">Price (IDR)</Label>
                                    <Input 
                                        id="price"
                                        v-model="form.price"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        :class="{ 'border-destructive': errors.price }"
                                    />
                                    <p v-if="errors.price" class="text-sm text-destructive">{{ errors.price }}</p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="quantity">Stock Quantity</Label>
                                    <Input
                                        id="quantity"
                                        v-model="form.quantity"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        :class="{ 'border-destructive': errors.quantity }"
                                    />
                                    <p v-if="errors.quantity" class="text-sm text-destructive">{{ errors.quantity }}</p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="category">Category</Label>
                                    <Select v-model="form.category_id">
                                        <SelectTrigger class="w-full" :class="{ 'border-destructive': errors.category_id }">
                                            <SelectValue placeholder="Select category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="category in productStore.categories" :key="category.id" :value="String(category.id)">
                                                {{ category.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="errors.category_id" class="text-sm text-destructive">{{ errors.category_id }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>Product Image</Label>
                            <div v-if="imagePreview" class="relative aspect-square max-w-xs rounded-lg overflow-hidden border">
                                <img :src="imagePreview" alt="Preview" class="w-full h-full object-cover" />
                                <Button 
                                    type="button"
                                    variant="destructive" 
                                    size="icon" 
                                    class="absolute top-2 right-2"
                                    @click="removeImage"
                                >
                                    <X class="w-4 h-4" />
                                </Button>
                            </div>
                            <div v-else class="border-2 border-dashed rounded-lg p-8 text-center w-full min-h-[225px] flex items-center justify-center">
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    class="hidden"
                                    id="image-upload"
                                    @change="handleImageChange"
                                />
                                <label for="image-upload" class="cursor-pointer">
                                    <div class="w-16 h-16 rounded-full bg-primary/10 mx-auto mb-4 flex items-center justify-center">
                                        <Upload class="w-6 h-6 text-primary" />
                                    </div>
                                    <p class="text-sm font-medium">Click to upload image</p>
                                    <p class="text-xs text-muted-foreground mt-1">PNG, JPG, GIF up to 10MB</p>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t">
                        <Button type="button" variant="outline" @click="router.back()">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="isSubmitting" class="gap-2">
                            <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                            <Save v-else class="w-4 h-4" />
                            {{ isSubmitting ? 'Saving...' : (isEdit ? 'Update Product' : 'Create Product') }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
