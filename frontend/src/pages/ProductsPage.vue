<script setup>
import { ref, onMounted, watch, onBeforeUnmount, computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useProductStore } from '@/stores/product'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { debounce } from 'perfect-debounce'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { Search, Package, ArrowLeft, LogOut, LayoutDashboard, User, ShoppingCart } from 'lucide-vue-next'
import Image from '@/components/Image.vue'
import { confirmModal } from '@/components/ui/confirmation-dialog'

const productStore = useProductStore()
const authStore = useAuthStore()
const cartStore = useCartStore()
const route = useRoute()

const showHeader = computed(() => {
    // Don't show header if inside user/admin layout
    return !route.path.startsWith('/user') && !route.path.startsWith('/admin')
})

const searchQuery = ref('')
const selectedCategory = ref('')
const minPrice = ref('')
const maxPrice = ref('')

onMounted(async () => {
    productStore.resetFilters()
    searchQuery.value = ''
    selectedCategory.value = ''
    minPrice.value = ''
    maxPrice.value = ''
    
    if (route.query.category) {
        selectedCategory.value = route.query.category
        productStore.setFilters({ category_id: route.query.category })
    }
    if (route.query.search) {
        searchQuery.value = route.query.search
        productStore.setFilters({ search: route.query.search })
    }
    
    const promises = [
        productStore.fetchProducts(),
        productStore.fetchCategories()
    ]
    
    if (authStore.isAuthenticated) {
        promises.push(cartStore.fetchCart())
    }
    
    await Promise.all(promises)
})

const debouncedSearch = debounce(() => {
    productStore.setFilters({
        search: searchQuery.value,
        category_id: selectedCategory.value,
        min_price: minPrice.value,
        max_price: maxPrice.value
    })
    productStore.fetchProducts()
}, 300)

const handleCategoryChange = (value) => {
    selectedCategory.value = value
    debouncedSearch()
}

const handleLogout = async () => {
    const confirmed = await confirmModal('Confirm Logout', 'Are you sure you want to logout?')
    if (confirmed) {
        await authStore.logout()
        window.location.href = '/login'
    }
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price)
}

const clearFilters = () => {
    searchQuery.value = ''
    selectedCategory.value = ''
    minPrice.value = ''
    maxPrice.value = ''
    productStore.resetFilters()
    productStore.fetchProducts()
}

watch(() => route.query, (newQuery) => {
    productStore.resetFilters()
    searchQuery.value = ''
    selectedCategory.value = ''
    minPrice.value = ''
    maxPrice.value = ''
    
    if (newQuery.category) {
        selectedCategory.value = newQuery.category
        productStore.setFilters({ category_id: newQuery.category })
    }
    if (newQuery.search) {
        searchQuery.value = newQuery.search
        productStore.setFilters({ search: newQuery.search })
    }
    
    productStore.fetchProducts()
}, { deep: true })

onBeforeUnmount(() => {
    productStore.resetFilters()
})
</script>

<template>
    <div class="min-h-screen bg-background">
        <header v-if="showHeader" class="sticky top-0 z-50 backdrop-blur-xl bg-background/80 border-b border-border/50">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <RouterLink to="/" class="flex items-center gap-2 group">
                        <Image src="/logo.png" alt="Logo" class="h-8 md:h-10 w-auto" />
                        <span class="text-lg md:text-xl font-bold hidden sm:inline">E-Commerce</span>
                    </RouterLink>

                    <nav class="hidden md:flex items-center gap-6">
                        <RouterLink to="/" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                            Home
                        </RouterLink>
                        <RouterLink to="/products" class="text-sm font-medium text-foreground">
                            Products
                        </RouterLink>
                    </nav>

                    <div class="flex items-center gap-2 md:gap-3">
                        <template v-if="authStore.isAuthenticated">
                            <RouterLink to="/user/cart">
                                <Button variant="ghost" size="icon" class="relative">
                                    <ShoppingCart class="w-5 h-5" />
                                    <span v-if="cartStore.totalItems > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-primary text-primary-foreground text-xs rounded-full flex items-center justify-center">
                                        {{ cartStore.totalItems > 99 ? '99+' : cartStore.totalItems }}
                                    </span>
                                </Button>
                            </RouterLink>
                            <RouterLink :to="authStore.isAdmin ? '/admin' : '/user'">
                                <Button variant="ghost" size="sm" class="gap-2">
                                    <LayoutDashboard class="w-4 h-4" />
                                    <span class="hidden sm:inline">Dashboard</span>
                                </Button>
                            </RouterLink>
                            <Button variant="ghost" size="icon" @click="handleLogout">
                                <LogOut class="w-4 h-4" />
                            </Button>
                        </template>
                        <template v-else>
                            <RouterLink to="/login">
                                <Button variant="ghost" size="sm">Login</Button>
                            </RouterLink>
                            <RouterLink to="/register">
                                <Button size="sm" class="gap-2">
                                    <User class="w-4 h-4" />
                                    <span class="hidden sm:inline">Register</span>
                                </Button>
                            </RouterLink>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <div class="container mx-auto px-4 py-8">
            <div class="flex items-center gap-4 mb-8" v-if="showHeader">
                <RouterLink to="/">
                    <Button variant="ghost" size="icon">
                        <ArrowLeft class="w-4 h-4" />
                    </Button>
                </RouterLink>
                <div>
                    <h1 class="text-3xl font-bold">All Products</h1>
                    <p class="text-muted-foreground">
                        {{ productStore.products.length }} products found
                    </p>
                </div>
            </div>
            <div v-else class="mb-8">
                <h1 class="text-3xl font-bold">All Products</h1>
                <p class="text-muted-foreground">
                    {{ productStore.products.length }} products found
                </p>
            </div>

            <Card class="mb-8 border-border/50">
                <CardContent class="p-4 md:p-6">
                    <div class="flex flex-col gap-4">
                        <!-- Search Row -->
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search products..."
                                class="pl-10 h-11"
                                @input="debouncedSearch"
                            />
                        </div>
                        
                        <!-- Category Full Width -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-muted-foreground">Category</label>
                            <Select :model-value="selectedCategory" @update:model-value="handleCategoryChange">
                                <SelectTrigger class="h-10 w-full">
                                    <SelectValue placeholder="All Categories" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Categories</SelectItem>
                                    <SelectItem v-for="category in productStore.categories" :key="category.id" :value="String(category.id)">
                                        {{ category.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        
                        <!-- Price Filters and Buttons Row -->
                        <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
                            <div class="grid grid-cols-2 gap-3 flex-1">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-medium text-muted-foreground">Min Price</label>
                                    <Input
                                        v-model="minPrice"
                                        type="number"
                                        placeholder="Rp 0"
                                        class="h-10"
                                        @input="debouncedSearch"
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-medium text-muted-foreground">Max Price</label>
                                    <Input
                                        v-model="maxPrice"
                                        type="number"
                                        placeholder="Rp 999.999.999"
                                        class="h-10"
                                        @input="debouncedSearch"
                                    />
                                </div>
                            </div>
                            <div class="flex gap-2 sm:flex-shrink-0">
                                <Button variant="outline" @click="clearFilters" class="w-full sm:w-auto">
                                    Clear
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div v-if="productStore.isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <Card v-for="i in 12" :key="i" class="overflow-hidden">
                    <Skeleton class="h-48 w-full" />
                    <CardHeader>
                        <Skeleton class="h-4 w-3/4" />
                        <Skeleton class="h-3 w-1/2 mt-2" />
                    </CardHeader>
                    <CardContent>
                        <Skeleton class="h-3 w-full" />
                    </CardContent>
                </Card>
            </div>

            <div v-else-if="productStore.products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <RouterLink 
                    v-for="product in productStore.products" 
                    :key="product.id"
                    :to="`/products/${product.id}`"
                    class="group"
                >
                    <Card class="overflow-hidden h-full hover:shadow-xl hover:shadow-primary/5 transition-all duration-300 hover:-translate-y-1 border-border/50">
                        <div class="relative h-48 bg-gradient-to-br from-muted to-muted/50 overflow-hidden">
                            <img 
                                v-if="product.image_url"
                                :src="product.image_url"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            />
                            <div v-else class="flex items-center justify-center h-full">
                                <Package class="w-12 h-12 text-muted-foreground/30" />
                            </div>
                            <Badge class="absolute top-3 left-3" variant="secondary">
                                {{ product.category?.name }}
                            </Badge>
                        </div>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-lg line-clamp-1 group-hover:text-primary transition-colors">
                                {{ product.name }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="pb-2">
                            <p class="text-sm text-muted-foreground line-clamp-2">
                                {{ product.description }}
                            </p>
                        </CardContent>
                        <CardFooter class="pt-0 flex justify-between items-center">
                            <span class="text-lg font-bold text-primary">
                                {{ formatPrice(product.price) }}
                            </span>
                            <span class="text-xs" :class="product.quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                {{ product.quantity > 0 ? `Stock: ${product.quantity}` : 'Out of stock' }}
                            </span>
                        </CardFooter>
                    </Card>
                </RouterLink>
            </div>

            <div v-else class="text-center py-20">
                <Package class="w-16 h-16 text-muted-foreground/30 mx-auto mb-4" />
                <h3 class="text-xl font-semibold mb-2">No Products Found</h3>
                <p class="text-muted-foreground mb-4">Try adjusting your search or filters</p>
                <Button @click="clearFilters">Clear Filters</Button>
            </div>

        </div>
    </div>
</template>
