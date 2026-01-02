<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useOrderStore } from '@/stores/order'
import { toast } from 'vue-sonner'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Package, ShoppingBag, ArrowRight, Eye } from 'lucide-vue-next'
import { confirmModal } from '@/components/ui/confirmation-dialog'

const orderStore = useOrderStore()
const activeTab = ref('all')

onMounted(async () => {
    await orderStore.fetchOrders()
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
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
        processing: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
        shipped: 'bg-purple-500/10 text-purple-500 border-purple-500/20',
        delivered: 'bg-green-500/10 text-green-500 border-green-500/20',
        cancelled: 'bg-red-500/10 text-red-500 border-red-500/20',
    }
    return colors[status] || ''
}

const getPaymentStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
        paid: 'bg-green-500/10 text-green-500 border-green-500/20',
        failed: 'bg-red-500/10 text-red-500 border-red-500/20',
        refunded: 'bg-gray-500/10 text-gray-500 border-gray-500/20',
    }
    return colors[status] || ''
}

const handleTabChange = async (tab) => {
    activeTab.value = tab
    await orderStore.fetchOrders(tab === 'all' ? '' : tab)
}

const handleCancelOrder = async (order) => {
    const confirmed = await confirmModal('Cancel Order', `Are you sure you want to cancel order ${order.order_number}?`)
    if (confirmed) {
        const result = await orderStore.cancelOrder(order.id)
        if (result.success) {
            toast.success('Order cancelled')
            await orderStore.fetchOrders()
        } else {
            toast.error(result.message)
        }
    }
}

const handlePayOrder = async (order) => {
    const confirmed = await confirmModal('Confirm Payment', `Confirm payment for order ${order.order_number}? (Simulated)`)
    if (confirmed) {
        const result = await orderStore.payOrder(order.id)
        if (result.success) {
            toast.success('Payment successful')
            await orderStore.fetchOrders()
        } else {
            toast.error(result.message)
        }
    }
}
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-3xl font-bold mb-2">My Orders</h1>
            <p class="text-muted-foreground">Track and manage your orders</p>
        </div>

        <Tabs :defaultValue="activeTab" @update:modelValue="handleTabChange">
            <div class="overflow-x-auto -mx-1 px-1 sm:overflow-x-visible sm:mx-0 sm:px-0">
                <TabsList class="inline-flex w-full min-w-[500px] sm:min-w-0 sm:w-full">
                    <TabsTrigger value="all" class="flex-shrink-0 sm:flex-1">All</TabsTrigger>
                    <TabsTrigger value="pending" class="flex-shrink-0 sm:flex-1">Pending</TabsTrigger>
                    <TabsTrigger value="processing" class="flex-shrink-0 sm:flex-1">Processing</TabsTrigger>
                    <TabsTrigger value="shipped" class="flex-shrink-0 sm:flex-1">Shipped</TabsTrigger>
                    <TabsTrigger value="delivered" class="flex-shrink-0 sm:flex-1">Delivered</TabsTrigger>
                </TabsList>
            </div>
        </Tabs>

        <div v-if="orderStore.isLoading" class="space-y-4">
            <Skeleton v-for="i in 3" :key="i" class="h-48 w-full" />
        </div>

        <div v-else-if="orderStore.orders.length === 0" class="text-center py-16">
            <ShoppingBag class="w-16 h-16 mx-auto text-muted-foreground/30 mb-4" />
            <h2 class="text-xl font-semibold mb-2">No orders found</h2>
            <p class="text-muted-foreground mb-6">Start shopping to see your orders here</p>
            <RouterLink to="/products">
                <Button class="gap-2">
                    Browse Products
                    <ArrowRight class="w-4 h-4" />
                </Button>
            </RouterLink>
        </div>

        <div v-else class="space-y-4">
            <Card v-for="order in orderStore.orders" :key="order.id" class="border-border/50">
                <CardHeader class="pb-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <CardTitle class="text-lg">{{ order.order_number }}</CardTitle>
                            <p class="text-sm text-muted-foreground">{{ formatDate(order.created_at) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Badge :class="getStatusColor(order.status)" variant="outline">
                                {{ order.status }}
                            </Badge>
                            <Badge :class="getPaymentStatusColor(order.payment_status)" variant="outline">
                                {{ order.payment_status }}
                            </Badge>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="flex flex-wrap gap-4">
                            <div v-for="item in order.items.slice(0, 3)" :key="item.id" class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-muted overflow-hidden flex-shrink-0">
                                    <img 
                                        v-if="item.product?.image_url"
                                        :src="item.product.image_url"
                                        :alt="item.product_name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="flex items-center justify-center h-full">
                                        <Package class="w-5 h-5 text-muted-foreground/30" />
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium truncate">{{ item.product_name }}</p>
                                    <p class="text-xs text-muted-foreground">x{{ item.quantity }}</p>
                                </div>
                            </div>
                            <div v-if="order.items.length > 3" class="flex items-center text-sm text-muted-foreground">
                                +{{ order.items.length - 3 }} more item(s)
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-4 border-t">
                            <div>
                                <p class="text-sm text-muted-foreground">Total Amount</p>
                                <p class="text-xl font-bold text-primary">{{ formatPrice(order.total_amount) }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <RouterLink :to="`/user/orders/${order.id}`">
                                    <Button variant="outline" size="sm" class="gap-2">
                                        <Eye class="w-4 h-4" />
                                        View Details
                                    </Button>
                                </RouterLink>
                                <Button 
                                    v-if="order.payment_status === 'pending' && order.status !== 'cancelled'"
                                    size="sm" 
                                    @click="handlePayOrder(order)"
                                >
                                    Pay Now
                                </Button>
                                <Button 
                                    v-if="['pending', 'processing'].includes(order.status) && order.status !== 'cancelled'"
                                    variant="outline" 
                                    size="sm" 
                                    class="text-destructive hover:text-destructive"
                                    @click="handleCancelOrder(order)"
                                >
                                    Cancel
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

    </div>
</template>

