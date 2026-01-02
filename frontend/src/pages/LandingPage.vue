<script setup>
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useProductStore } from '@/stores/product'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { Search, ShoppingCart, User, LogOut, LayoutDashboard, Package, Star, ArrowRight } from 'lucide-vue-next'
import Image from '@/components/Image.vue'
import WorldMap from '@/components/WorldMap.vue'
import ReviewCard from '@/components/RevieCard.vue'
import { confirmModal } from '@/components/ui/confirmation-dialog'
import { debounce } from 'perfect-debounce'

const reviews = [
    {
        img: 'https://avatar.vercel.sh/jack',
        name: 'Budi Santoso',
        username: '@budi_santoso',
        body: 'Produk berkualitas tinggi dan pengiriman sangat cepat! Sangat puas dengan pelayanannya.',
    },
    {
        img: 'https://avatar.vercel.sh/jill',
        name: 'Siti Iskandar',
        username: '@siti_nur',
        body: 'Customer service yang ramah dan responsif. Produk sesuai dengan deskripsi.',
    },
    {
        img: 'https://avatar.vercel.sh/john',
        name: 'Ahmad Wijaya',
        username: '@ahmad_w',
        body: 'Harga terjangkau dengan kualitas premium. Pasti akan belanja lagi!',
    },
    {
        img: 'https://avatar.vercel.sh/jane',
        name: 'Dewi Lestari',
        username: '@dewi_les',
        body: 'Packaging rapi dan aman. Barang sampai dalam kondisi sempurna.',
    },
    {
        img: 'https://avatar.vercel.sh/james',
        name: 'Rudi Hartono',
        username: '@rudi_h',
        body: 'Proses checkout mudah dan payment method lengkap. Recommended!',
    },
    {
        img: 'https://avatar.vercel.sh/jenny',
        name: 'Maya Putri',
        username: '@maya_p',
        body: 'Sudah langganan dari dulu, tidak pernah mengecewakan. Top!',
    },
]

const productStore = useProductStore()
const authStore = useAuthStore()
const cartStore = useCartStore()

const searchQuery = ref('')
const selectedCategory = ref('')

onMounted(async () => {
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
        category_id: selectedCategory.value
    })
    productStore.fetchProducts()
}, 300)

const handleCategoryChange = (value) => {
    selectedCategory.value = value
    debouncedSearch()
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price)
}

const handleLogout = async () => {
    const confirmed = await confirmModal('Confirm Logout', 'Are you sure you want to logout?')
    if (confirmed) {
        await authStore.logout()
        window.location.href = '/login'
    }
}
</script>

<template>
    <div class="min-h-screen bg-background">
        <header class="sticky top-0 z-50 backdrop-blur-xl bg-background/80 border-b border-border/50">
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
                        <RouterLink to="/products" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
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

        <section class="relative py-20 md:py-32 overflow-hidden">
            <!-- WorldMap Background -->
            <div class="blur-[2px] absolute inset-0 flex items-center justify-center opacity-50 dark:opacity-40 pointer-events-none overflow-hidden">
                <div class="w-[150%] max-w-none">
                    <WorldMap 
                        :dots="[
                            { start: { lat: -6.2, lng: 106.8 }, end: { lat: 35.6, lng: 139.6 } },
                            { start: { lat: -6.2, lng: 106.8 }, end: { lat: 1.3, lng: 103.8 } },
                            { start: { lat: -6.2, lng: 106.8 }, end: { lat: 22.3, lng: 114.1 } },
                            { start: { lat: -6.2, lng: 106.8 }, end: { lat: 37.5, lng: 127.0 } },
                            { start: { lat: -6.2, lng: 106.8 }, end: { lat: 13.7, lng: 100.5 } },
                            { start: { lat: -6.2, lng: 106.8 }, end: { lat: 25.0, lng: 121.5 } },
                            { start: { lat: 1.3, lng: 103.8 }, end: { lat: 51.5, lng: -0.1 } },
                            { start: { lat: 35.6, lng: 139.6 }, end: { lat: 40.7, lng: -74.0 } },
                            { start: { lat: 22.3, lng: 114.1 }, end: { lat: 48.8, lng: 2.3 } },
                            { start: { lat: 37.5, lng: 127.0 }, end: { lat: -33.8, lng: 151.2 } },
                            { start: { lat: 51.5, lng: -0.1 }, end: { lat: 40.7, lng: -74.0 } },
                            { start: { lat: 48.8, lng: 2.3 }, end: { lat: 55.7, lng: 37.6 } },
                        ]"
                        line-color="#3b82f6"
                        map-color="#94a3b8"
                        map-bg-color="transparent"
                    />
                </div>
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-primary/5 via-transparent to-transparent" />
            
            <div class="container mx-auto px-4 relative z-10">
                <div class="max-w-3xl mx-auto text-center">
                    <Badge variant="secondary" class="mb-6 px-4 py-1.5 text-sm">
                        Welcome to E-Commerce
                    </Badge>
                    <h1 class="text-4xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-foreground via-foreground to-muted-foreground bg-clip-text text-transparent leading-tight">
                        Discover Amazing Products
                    </h1>
                    <p class="text-lg md:text-xl text-muted-foreground mb-10 max-w-2xl mx-auto">
                        Shop the latest products from the best brands. Quality guaranteed, fast shipping, and excellent customer service.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <RouterLink to="/products">
                            <Button size="lg" class="gap-2 px-8">
                                Browse Products
                                <ArrowRight class="w-4 h-4" />
                            </Button>
                        </RouterLink>
                        <RouterLink to="/register" v-if="!authStore.isAuthenticated">
                            <Button variant="outline" size="lg" class="px-8">
                                Create Account
                            </Button>
                        </RouterLink>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-12">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row gap-4 max-w-3xl mx-auto">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search products..."
                            class="pl-10"
                            @input="debouncedSearch"
                        />
                    </div>
                    <Select :model-value="selectedCategory" @update:model-value="handleCategoryChange">
                        <SelectTrigger class="w-full md:w-[200px]">
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
            </div>
        </section>

        <section class="py-16">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-10">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold mb-2">Featured Products</h2>
                        <p class="text-muted-foreground">Explore our latest collection</p>
                    </div>
                    <RouterLink to="/products">
                        <Button variant="ghost" class="gap-2">
                            View All
                            <ArrowRight class="w-4 h-4" />
                        </Button>
                    </RouterLink>
                </div>

                <div v-if="productStore.isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Card v-for="i in 8" :key="i" class="overflow-hidden">
                        <Skeleton class="h-48 w-full" />
                        <CardHeader>
                            <Skeleton class="h-4 w-3/4" />
                            <Skeleton class="h-3 w-1/2 mt-2" />
                        </CardHeader>
                        <CardContent>
                            <Skeleton class="h-3 w-full" />
                            <Skeleton class="h-3 w-2/3 mt-2" />
                        </CardContent>
                    </Card>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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
                            <CardFooter class="pt-0">
                                <span class="text-lg font-bold text-primary">
                                    {{ formatPrice(product.price) }}
                                </span>
                            </CardFooter>
                        </Card>
                    </RouterLink>
                </div>

                <div v-if="!productStore.isLoading && productStore.products.length === 0" class="text-center py-20">
                    <Package class="w-16 h-16 text-muted-foreground/30 mx-auto mb-4" />
                    <h3 class="text-xl font-semibold mb-2">No Products Found</h3>
                    <p class="text-muted-foreground">Try adjusting your search or filters</p>
                </div>
            </div>
        </section>

        <section class="py-16 border-y border-border/50">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                            <Package class="w-6 h-6 text-primary" />
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Free Shipping</h3>
                        <p class="text-muted-foreground text-sm">Free shipping on all orders</p>
                    </div>
                    <div class="text-center">
                        <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                            <Star class="w-6 h-6 text-primary" />
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Quality Guarantee</h3>
                        <p class="text-muted-foreground text-sm">100% original products guaranteed</p>
                    </div>
                    <div class="text-center">
                        <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                            <ShoppingCart class="w-6 h-6 text-primary" />
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Easy Shopping</h3>
                        <p class="text-muted-foreground text-sm">Easy shopping experience</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Reviews Section -->
        <section class="py-16">
            <div class="container mx-auto px-4">
                <div class="text-center mb-10">
                    <h2 class="text-2xl md:text-3xl font-bold mb-2">What Our Customers Say</h2>
                    <p class="text-muted-foreground">Trusted by thousands of happy customers</p>
                </div>
                
                <div class="relative overflow-hidden">
                    <!-- Gradient overlays for smooth edges -->
                    <div class="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r from-background to-transparent z-10 pointer-events-none" />
                    <div class="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l from-background to-transparent z-10 pointer-events-none" />
                    
                    <!-- Scrolling reviews - Row 1 -->
                    <div class="flex gap-4 mb-4 animate-marquee">
                        <ReviewCard
                            v-for="(review, index) in reviews"
                            :key="`row1-${index}`"
                            :img="review.img"
                            :name="review.name"
                            :username="review.username"
                            :body="review.body"
                        />
                        <ReviewCard
                            v-for="(review, index) in reviews"
                            :key="`row1-dup-${index}`"
                            :img="review.img"
                            :name="review.name"
                            :username="review.username"
                            :body="review.body"
                        />
                    </div>
                    
                    <!-- Scrolling reviews - Row 2 (reverse) -->
                    <div class="flex gap-4 animate-marquee-reverse">
                        <ReviewCard
                            v-for="(review, index) in [...reviews].reverse()"
                            :key="`row2-${index}`"
                            :img="review.img"
                            :name="review.name"
                            :username="review.username"
                            :body="review.body"
                        />
                        <ReviewCard
                            v-for="(review, index) in [...reviews].reverse()"
                            :key="`row2-dup-${index}`"
                            :img="review.img"
                            :name="review.name"
                            :username="review.username"
                            :body="review.body"
                        />
                    </div>
                </div>
            </div>
        </section>

        <footer class="py-8 md:py-12 border-t border-border/50">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <Image src="/logo.png" alt="Logo" class="h-6 md:h-8 w-auto" />
                        <span class="font-semibold text-sm md:text-base">E-Commerce</span>
                    </div>
                    <p class="text-xs md:text-sm text-muted-foreground text-center">
                        2026 E-Commerce. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
