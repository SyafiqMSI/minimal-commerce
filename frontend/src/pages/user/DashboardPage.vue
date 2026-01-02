<script setup>
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useProductStore } from '@/stores/product'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'
import { useOrderStore } from '@/stores/order'
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { User, Mail, ShoppingBag, Heart, CreditCard, Package, ArrowRight, ShoppingCart } from 'lucide-vue-next'

const authStore = useAuthStore()
const productStore = useProductStore()
const cartStore = useCartStore()
const wishlistStore = useWishlistStore()
const orderStore = useOrderStore()

const stats = computed(() => {
    if (!authStore.isAuthenticated) {
        return {
            totalOrders: 0,
            wishlistCount: 0,
            totalSpent: 0,
            cartItems: 0
        }
    }

    const totalSpent = orderStore.orders
        .filter(o => o.payment_status === 'paid')
        .reduce((sum, o) => sum + parseFloat(o.total_amount), 0)

    return {
        totalOrders: orderStore.orders.length,
        wishlistCount: wishlistStore.items.length,
        totalSpent: totalSpent,
        cartItems: cartStore.totalItems
    }
})

onMounted(async () => {
    const promises = [
        productStore.fetchProducts(),
        productStore.fetchCategories()
    ]

    if (authStore.isAuthenticated) {
        promises.push(cartStore.fetchCart())
        promises.push(wishlistStore.fetchWishlist())
        promises.push(orderStore.fetchOrders())
    }

    await Promise.all(promises)
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price)
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-500/10 text-yellow-500',
        processing: 'bg-blue-500/10 text-blue-500',
        shipped: 'bg-purple-500/10 text-purple-500',
        delivered: 'bg-green-500/10 text-green-500',
        cancelled: 'bg-red-500/10 text-red-500',
    }
    return colors[status] || ''
}
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-3xl font-bold mb-2">Welcome, {{ authStore.user?.name }}!</h1>
            <p class="text-muted-foreground">Here's what's happening with your account today.</p>
        </div>

        <Card class="border-border/50">
            <CardContent class="p-6">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                    <div class="w-20 h-20 rounded-2xl bg-primary/10 flex items-center justify-center text-primary text-3xl font-bold">
                        {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold mb-2">{{ authStore.user?.name }}</h2>
                        <div class="flex flex-wrap gap-4 text-sm text-muted-foreground">
                            <div class="flex items-center gap-2">
                                <Mail class="w-4 h-4" />
                                {{ authStore.user?.email }}
                            </div>
                            <div class="flex items-center gap-2">
                                <User class="w-4 h-4" />
                                <Badge variant="secondary">{{ authStore.user?.role }}</Badge>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <RouterLink to="/user/orders">
                <Card class="border-border/50 hover:border-primary/30 transition-colors cursor-pointer h-full">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                <ShoppingBag class="w-4 h-4 text-primary" />
                            </div>
                            My Orders
                        </CardDescription>
                        <CardTitle class="text-3xl">{{ stats.totalOrders }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">Total orders placed</p>
                    </CardContent>
                </Card>
            </RouterLink>

            <RouterLink to="/user/wishlist">
                <Card class="border-border/50 hover:border-primary/30 transition-colors cursor-pointer h-full">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                <Heart class="w-4 h-4 text-primary" />
                            </div>
                            Wishlist
                        </CardDescription>
                        <CardTitle class="text-3xl">{{ stats.wishlistCount }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">Items in wishlist</p>
                    </CardContent>
                </Card>
            </RouterLink>

            <RouterLink to="/user/cart">
                <Card class="border-border/50 hover:border-primary/30 transition-colors cursor-pointer h-full">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                <ShoppingCart class="w-4 h-4 text-primary" />
                            </div>
                            Cart
                        </CardDescription>
                        <CardTitle class="text-3xl">{{ stats.cartItems }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">Items in cart</p>
                    </CardContent>
                </Card>
            </RouterLink>

            <Card class="border-border/50 hover:border-primary/30 transition-colors">
                <CardHeader class="pb-2">
                    <CardDescription class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <CreditCard class="w-4 h-4 text-primary" />
                        </div>
                        Total Spent
                    </CardDescription>
                    <CardTitle class="text-2xl">{{ formatPrice(stats.totalSpent) }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">Lifetime purchases</p>
                </CardContent>
            </Card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Recent Orders</CardTitle>
                            <CardDescription>Your latest orders</CardDescription>
                        </div>
                        <RouterLink to="/user/orders">
                            <Button variant="ghost" size="sm" class="gap-2">
                                View All
                                <ArrowRight class="w-4 h-4" />
                            </Button>
                        </RouterLink>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="orderStore.orders.length > 0" class="space-y-4">
                        <RouterLink 
                            v-for="order in orderStore.orders.slice(0, 5)" 
                            :key="order.id"
                            :to="`/user/orders/${order.id}`"
                            class="block"
                        >
                            <div class="flex items-center justify-between p-3 rounded-lg hover:bg-muted/50 transition-colors">
                                <div>
                                    <p class="font-medium">{{ order.order_number }}</p>
                                    <p class="text-sm text-muted-foreground">{{ order.items?.length || 0 }} item(s)</p>
                                </div>
                                <div class="text-right">
                                    <Badge :class="getStatusColor(order.status)" variant="secondary" class="mb-1">
                                        {{ order.status }}
                                    </Badge>
                                    <p class="text-sm font-semibold text-primary">{{ formatPrice(order.total_amount) }}</p>
                                </div>
                            </div>
                        </RouterLink>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        <ShoppingBag class="w-12 h-12 mx-auto mb-3 opacity-30" />
                        <p>No orders yet</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Recommended for You</CardTitle>
                            <CardDescription>Products you might like</CardDescription>
                        </div>
                        <RouterLink to="/products">
                            <Button variant="ghost" size="sm" class="gap-2">
                                View All
                                <ArrowRight class="w-4 h-4" />
                            </Button>
                        </RouterLink>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 gap-4">
                        <RouterLink 
                            v-for="product in productStore.products.slice(0, 4)" 
                            :key="product.id"
                            :to="`/products/${product.id}`"
                            class="group"
                        >
                            <Card class="overflow-hidden h-full hover:shadow-md transition-all hover:-translate-y-1 border-border/50">
                                <div class="relative h-24 bg-muted overflow-hidden">
                                    <img 
                                        v-if="product.image_url"
                                        :src="product.image_url"
                                        :alt="product.name"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    />
                                    <div v-else class="flex items-center justify-center h-full">
                                        <Package class="w-6 h-6 text-muted-foreground/30" />
                                    </div>
                                </div>
                                <CardHeader class="p-2 pb-1">
                                    <CardTitle class="text-xs line-clamp-1 group-hover:text-primary transition-colors">
                                        {{ product.name }}
                                    </CardTitle>
                                </CardHeader>
                                <CardFooter class="p-2 pt-0 flex justify-between items-center">
                                    <span class="text-xs font-bold text-primary">
                                        {{ formatPrice(product.price) }}
                                    </span>
                                    <span class="text-[10px]" :class="product.quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ product.quantity > 0 ? `Stock: ${product.quantity}` : 'Out of stock' }}
                                    </span>
                                </CardFooter>
                            </Card>
                        </RouterLink>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
