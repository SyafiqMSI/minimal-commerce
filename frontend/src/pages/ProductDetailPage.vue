<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useProductStore } from '@/stores/product'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { Separator } from '@/components/ui/separator'
import { toast } from 'vue-sonner'
import { Package, ArrowLeft, Tag, LayoutDashboard, LogOut, User, ShoppingCart, Heart, Minus, Plus, Check } from 'lucide-vue-next'
import Image from '@/components/Image.vue'
import { confirmModal } from '@/components/ui/confirmation-dialog'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()
const authStore = useAuthStore()
const cartStore = useCartStore()
const wishlistStore = useWishlistStore()

const product = ref(null)
const isLoading = ref(true)
const relatedProducts = ref([])
const quantity = ref(1)
const isAddingToCart = ref(false)
const isTogglingWishlist = ref(false)

const isInWishlist = computed(() => {
    if (!authStore.isAuthenticated || !product.value) return false
    return wishlistStore.isInWishlist(product.value.id)
})

const isInCart = computed(() => {
    if (!authStore.isAuthenticated || !product.value) return false
    return cartStore.isInCart(product.value.id)
})

onMounted(async () => {
    isLoading.value = true
    product.value = await productStore.fetchProduct(route.params.id)
    
    if (!product.value) {
        router.push('/products')
        return
    }

    await productStore.fetchCategories()
    
    productStore.setFilters({ category_id: product.value.category_id })
    await productStore.fetchProducts()
    relatedProducts.value = productStore.products.filter(p => p.id !== product.value.id).slice(0, 4)
    
    if (authStore.isAuthenticated) {
        await Promise.all([
            cartStore.fetchCart(),
            wishlistStore.fetchWishlist()
        ])
    }
    
    isLoading.value = false
})

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

const decreaseQuantity = () => {
    if (quantity.value > 1) quantity.value--
}

const increaseQuantity = () => {
    quantity.value++
}

const handleAddToCart = async () => {
    if (!authStore.isAuthenticated) {
        router.push('/login')
        return
    }

    isAddingToCart.value = true
    const result = await cartStore.addToCart(product.value.id, quantity.value)
    
    if (result.success) {
        toast.success('Product added to cart!')
    } else {
        toast.error(result.message)
    }
    isAddingToCart.value = false
}

const handleToggleWishlist = async () => {
    if (!authStore.isAuthenticated) {
        router.push('/login')
        return
    }

    isTogglingWishlist.value = true
    
    if (isInWishlist.value) {
        const result = await wishlistStore.removeByProductId(product.value.id)
        if (result.success) {
            toast.success('Product removed from wishlist')
        }
    } else {
        const result = await wishlistStore.addToWishlist(product.value.id)
        if (result.success) {
            toast.success('Product added to wishlist!')
        }
    }
    
    isTogglingWishlist.value = false
}

const handleBuyNow = async () => {
    if (!authStore.isAuthenticated) {
        router.push('/login')
        return
    }

    isAddingToCart.value = true
    const result = await cartStore.addToCart(product.value.id, quantity.value)
    
    if (result.success) {
        router.push('/user/checkout')
    } else {
        toast.error(result.message)
    }
    isAddingToCart.value = false
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
            <RouterLink to="/products" class="inline-flex items-center gap-2 text-muted-foreground hover:text-foreground transition-colors mb-8">
                <ArrowLeft class="w-4 h-4" />
                Back to Products
            </RouterLink>

            <div v-if="isLoading" class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12">
                <Skeleton class="aspect-square rounded-2xl lg:col-span-3" />
                <div class="space-y-6 lg:col-span-2">
                    <Skeleton class="h-8 w-3/4" />
                    <Skeleton class="h-6 w-1/4" />
                    <Skeleton class="h-24 w-full" />
                    <Skeleton class="h-10 w-1/3" />
                </div>
            </div>

            <div v-else-if="product" class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12">
                <div class="relative lg:col-span-3">
                    <div class="aspect-square rounded-2xl bg-gradient-to-br from-muted to-muted/50 overflow-hidden shadow-2xl">
                        <img 
                            v-if="product.image_url"
                            :src="product.image_url"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="flex items-center justify-center h-full">
                            <Package class="w-24 h-24 text-muted-foreground/30" />
                        </div>
                    </div>
                    <Button 
                        variant="outline" 
                        size="icon" 
                        class="absolute top-4 right-4 bg-background/80 backdrop-blur-sm"
                        :class="{ 'text-red-500 hover:text-red-600': isInWishlist }"
                        @click="handleToggleWishlist"
                        :disabled="isTogglingWishlist"
                    >
                        <Heart class="w-5 h-5" :fill="isInWishlist ? 'currentColor' : 'none'" />
                    </Button>
                </div>

                <div class="space-y-6 lg:col-span-2">
                    <div>
                        <Badge variant="secondary" class="mb-4">
                            <Tag class="w-3 h-3 mr-1" />
                            {{ product.category?.name }}
                        </Badge>
                        <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ product.name }}</h1>
                        <p class="text-3xl font-bold text-primary">{{ formatPrice(product.price) }}</p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-lg font-semibold mb-3">Description</h3>
                        <p class="text-muted-foreground leading-relaxed">{{ product.description }}</p>
                    </div>

                    <Separator />

                    <div>
                        <h3 class="text-lg font-semibold mb-3">Quantity</h3>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center border rounded-lg">
                                <Button variant="ghost" size="icon" @click="decreaseQuantity" :disabled="quantity <= 1">
                                    <Minus class="w-4 h-4" />
                                </Button>
                                <span class="w-12 text-center font-medium">{{ quantity }}</span>
                                <Button variant="ghost" size="icon" @click="increaseQuantity">
                                    <Plus class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <Button 
                            size="lg" 
                            class="gap-2 flex-1" 
                            @click="handleAddToCart"
                            :disabled="isAddingToCart"
                        >
                            <Check v-if="isInCart" class="w-5 h-5" />
                            <ShoppingCart v-else class="w-5 h-5" />
                            {{ isInCart ? 'Added to Cart' : 'Add to Cart' }}
                        </Button>
                        <Button 
                            variant="outline" 
                            size="lg" 
                            class="flex-1"
                            @click="handleBuyNow"
                            :disabled="isAddingToCart"
                        >
                            Buy Now
                        </Button>
                    </div>
                </div>
            </div>

            <div v-if="relatedProducts.length > 0" class="mt-20">
                <h2 class="text-2xl font-bold mb-8">Related Products</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <RouterLink 
                        v-for="relatedProduct in relatedProducts" 
                        :key="relatedProduct.id"
                        :to="`/products/${relatedProduct.id}`"
                        class="group"
                    >
                        <Card class="overflow-hidden h-full hover:shadow-xl hover:shadow-primary/5 transition-all duration-300 hover:-translate-y-1 border-border/50">
                            <div class="relative h-48 bg-gradient-to-br from-muted to-muted/50 overflow-hidden">
                                <img 
                                    v-if="relatedProduct.image_url"
                                    :src="relatedProduct.image_url"
                                    :alt="relatedProduct.name"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                />
                                <div v-else class="flex items-center justify-center h-full">
                                    <Package class="w-12 h-12 text-muted-foreground/30" />
                                </div>
                            </div>
                            <CardHeader class="pb-2">
                                <CardTitle class="text-lg line-clamp-1 group-hover:text-primary transition-colors">
                                    {{ relatedProduct.name }}
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="pt-0">
                                <span class="text-lg font-bold text-primary">
                                    {{ formatPrice(relatedProduct.price) }}
                                </span>
                            </CardContent>
                        </Card>
                    </RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>
