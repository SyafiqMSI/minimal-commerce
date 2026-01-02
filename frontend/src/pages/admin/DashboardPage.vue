<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useProductStore } from '@/stores/product'
import { useOrderStore } from '@/stores/order'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Package, ShoppingBag, Tag, TrendingUp, ArrowRight, Clock, CheckCircle, Truck, Users } from 'lucide-vue-next'

const productStore = useProductStore()
const orderStore = useOrderStore()

const stats = ref({
    totalProducts: 0,
    totalCategories: 0,
    totalOrders: 0,
    pendingOrders: 0,
    totalRevenue: 0,
    todayRevenue: 0,
    todayOrders: 0,
    totalStock: 0,
    outOfStock: 0,
    lowStock: 0,
    totalUsers: 0
})

onMounted(async () => {
    await Promise.all([
        productStore.fetchProducts(),
        productStore.fetchCategories(),
        orderStore.fetchStats(),
        orderStore.fetchAdminOrders()
    ])
    
    const totalStock = productStore.products.reduce((sum, product) => sum + product.quantity, 0)
    const outOfStock = productStore.products.filter(product => product.quantity === 0).length
    const lowStock = productStore.products.filter(product => product.quantity > 0 && product.quantity <= 10).length

    stats.value = {
        totalProducts: productStore.products.length,
        totalCategories: productStore.categories.length,
        totalOrders: orderStore.stats?.total_orders || 0,
        pendingOrders: orderStore.stats?.pending_orders || 0,
        totalRevenue: orderStore.stats?.total_revenue || 0,
        todayRevenue: orderStore.stats?.today_revenue || 0,
        todayOrders: orderStore.stats?.today_orders || 0,
        totalStock: totalStock,
        outOfStock: outOfStock,
        lowStock: lowStock,
        totalUsers: orderStore.stats?.total_users || 0
    }
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    })
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
            <h1 class="text-3xl font-bold mb-2">Dashboard</h1>
            <p class="text-muted-foreground">Welcome back! Here's an overview of your store.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-6">
            <Card class="border-border/50 hover:border-primary/30 transition-colors">
                <CardHeader class="pb-2">
                    <CardDescription class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <ShoppingBag class="w-4 h-4 text-primary" />
                        </div>
                        Total Products
                    </CardDescription>
                    <CardTitle class="text-3xl">{{ stats.totalProducts }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">Products in your store</p>
                </CardContent>
            </Card>

            <Card class="border-border/50 hover:border-primary/30 transition-colors">
                <CardHeader class="pb-2">
                    <CardDescription class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <Tag class="w-4 h-4 text-primary" />
                        </div>
                        Categories
                    </CardDescription>
                    <CardTitle class="text-3xl">{{ stats.totalCategories }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">Product categories</p>
                </CardContent>
            </Card>

            <Card class="border-border/50 hover:border-primary/30 transition-colors">
                <CardHeader class="pb-2">
                    <CardDescription class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center">
                            <Users class="w-4 h-4 text-purple-500" />
                        </div>
                        Total Users
                    </CardDescription>
                    <CardTitle class="text-3xl">{{ stats.totalUsers }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">Registered users</p>
                </CardContent>
            </Card>

            <Card class="border-border/50 hover:border-primary/30 transition-colors">
                <CardHeader class="pb-2">
                    <CardDescription class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                            <Clock class="w-4 h-4 text-yellow-500" />
                        </div>
                        Pending Orders
                    </CardDescription>
                    <CardTitle class="text-3xl">{{ stats.pendingOrders }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">Orders awaiting processing</p>
                </CardContent>
            </Card>

            <Card class="border-border/50 hover:border-primary/30 transition-colors">
                <CardHeader class="pb-2">
                    <CardDescription class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-green-500/10 flex items-center justify-center">
                            <TrendingUp class="w-4 h-4 text-green-500" />
                        </div>
                        Total Revenue
                    </CardDescription>
                    <CardTitle class="text-2xl">{{ formatPrice(stats.totalRevenue) }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">From {{ stats.totalOrders }} orders</p>
                </CardContent>
            </Card>

            <Card class="border-border/50 hover:border-primary/30 transition-colors">
                <CardHeader class="pb-2">
                    <CardDescription class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                            <Package class="w-4 h-4 text-blue-500" />
                        </div>
                        Total Stock
                    </CardDescription>
                    <CardTitle class="text-3xl">{{ stats.totalStock }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">Items in inventory</p>
                </CardContent>
            </Card>

            <Card class="border-border/50 hover:border-primary/30 transition-colors" :class="{ 'border-red-500': stats.outOfStock > 0 }">
                <CardHeader class="pb-2">
                    <CardDescription class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center">
                            <Package class="w-4 h-4 text-red-500" />
                        </div>
                        Out of Stock
                    </CardDescription>
                    <CardTitle class="text-3xl" :class="{ 'text-red-600': stats.outOfStock > 0 }">{{ stats.outOfStock }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">Products need restocking</p>
                </CardContent>
            </Card>

            <Card class="border-border/50 hover:border-primary/30 transition-colors" :class="{ 'border-orange-500': stats.lowStock > 0 }">
                <CardHeader class="pb-2">
                    <CardDescription class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-orange-500/10 flex items-center justify-center">
                            <Package class="w-4 h-4 text-orange-500" />
                        </div>
                        Low Stock
                    </CardDescription>
                    <CardTitle class="text-3xl" :class="{ 'text-orange-600': stats.lowStock > 0 }">{{ stats.lowStock }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">Products with ≤10 stock</p>
                </CardContent>
            </Card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Recent Orders</CardTitle>
                            <CardDescription>Latest orders from customers</CardDescription>
                        </div>
                        <RouterLink to="/admin/orders">
                            <Button variant="ghost" size="sm" class="gap-2">
                                View All
                                <ArrowRight class="w-4 h-4" />
                            </Button>
                        </RouterLink>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="orderStore.orders.length > 0" class="space-y-4">
                        <div 
                            v-for="order in orderStore.orders.slice(0, 5)" 
                            :key="order.id"
                            class="flex items-center justify-between p-3 rounded-lg hover:bg-muted/50 transition-colors"
                        >
                            <div>
                                <p class="font-medium">{{ order.order_number }}</p>
                                <p class="text-sm text-muted-foreground">{{ order.user?.name }} • {{ order.items?.length || 0 }} items</p>
                            </div>
                            <div class="text-right">
                                <Badge :class="getStatusColor(order.status)" variant="secondary" class="mb-1">
                                    {{ order.status }}
                                </Badge>
                                <p class="text-sm font-semibold text-primary">{{ formatPrice(order.total_amount) }}</p>
                            </div>
                        </div>
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
                            <CardTitle>Recent Products</CardTitle>
                            <CardDescription>Latest products added to your store</CardDescription>
                        </div>
                        <RouterLink to="/admin/products">
                            <Button variant="ghost" size="sm" class="gap-2">
                                View All
                                <ArrowRight class="w-4 h-4" />
                            </Button>
                        </RouterLink>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="productStore.products.length > 0" class="space-y-4">
                        <div 
                            v-for="product in productStore.products.slice(0, 5)" 
                            :key="product.id"
                            class="flex items-center gap-4"
                        >
                            <div class="w-12 h-12 rounded-lg bg-muted overflow-hidden flex-shrink-0">
                                <img 
                                    v-if="product.image_url"
                                    :src="product.image_url"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="flex items-center justify-center h-full">
                                    <Package class="w-5 h-5 text-muted-foreground/30" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium truncate">{{ product.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ product.category?.name }}</p>
                            </div>
                            <p class="font-semibold text-primary">
                                {{ formatPrice(product.price) }}
                            </p>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        No products yet
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Categories</CardTitle>
                <CardDescription>Products by category</CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="productStore.categories.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div 
                        v-for="category in productStore.categories" 
                        :key="category.id"
                        class="flex items-center justify-between p-4 rounded-lg bg-muted/50"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                <Tag class="w-5 h-5 text-primary" />
                            </div>
                            <span class="font-medium">{{ category.name }}</span>
                        </div>
                        <span class="text-muted-foreground">{{ category.products_count || 0 }} products</span>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-muted-foreground">
                    No categories yet
                </div>
            </CardContent>
        </Card>
    </div>
</template>
