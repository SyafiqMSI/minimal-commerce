<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useOrderStore } from '@/stores/order'
import { toast } from 'vue-sonner'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { Skeleton } from '@/components/ui/skeleton'
import { Package, ArrowLeft, MapPin, Phone, User, FileText, CreditCard, Calendar, Clock } from 'lucide-vue-next'
import { confirmModal } from '@/components/ui/confirmation-dialog'

const route = useRoute()
const router = useRouter()
const orderStore = useOrderStore()

const order = ref(null)
const isLoading = ref(true)

onMounted(async () => {
    isLoading.value = true
    order.value = await orderStore.fetchOrder(route.params.id)
    
    if (!order.value) {
        router.push('/user/orders')
    }
    
    isLoading.value = false
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

const paymentMethodLabels = {
    bank_transfer: 'Bank Transfer',
    e_wallet: 'E-Wallet',
    cod: 'Cash on Delivery',
}

const handlePayOrder = async () => {
    const confirmed = await confirmModal('Confirm Payment', `Confirm payment for this order? (Simulated)`)
    if (confirmed) {
        const result = await orderStore.payOrder(order.value.id)
        if (result.success) {
            order.value = result.data
            toast.success('Payment successful')
        } else {
            toast.error(result.message)
        }
    }
}

const handleCancelOrder = async () => {
    const confirmed = await confirmModal('Cancel Order', `Are you sure you want to cancel this order?`)
    if (confirmed) {
        const result = await orderStore.cancelOrder(order.value.id)
        if (result.success) {
            order.value = result.data
            toast.success('Order cancelled')
        } else {
            toast.error(result.message)
        }
    }
}
</script>

<template>
    <div class="space-y-8">
        <div class="flex items-center gap-4">
            <Button variant="ghost" size="icon" @click="router.push('/user/orders')">
                <ArrowLeft class="w-5 h-5" />
            </Button>
            <div>
                <h1 class="text-3xl font-bold mb-1">Order Details</h1>
                <p v-if="order" class="text-muted-foreground">{{ order.order_number }}</p>
            </div>
        </div>

        <div v-if="isLoading" class="space-y-4">
            <Skeleton class="h-48 w-full" />
            <Skeleton class="h-64 w-full" />
        </div>

        <div v-else-if="order" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <Card class="border-border/50">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>Order Status</CardTitle>
                            <div class="flex gap-2">
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
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div class="flex items-start gap-2">
                                <Calendar class="w-4 h-4 text-muted-foreground mt-0.5" />
                                <div>
                                    <p class="text-muted-foreground">Order Date</p>
                                    <p class="font-medium">{{ formatDate(order.created_at) }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <CreditCard class="w-4 h-4 text-muted-foreground mt-0.5" />
                                <div>
                                    <p class="text-muted-foreground">Payment Method</p>
                                    <p class="font-medium">{{ paymentMethodLabels[order.payment_method] || order.payment_method }}</p>
                                </div>
                            </div>
                            <div v-if="order.paid_at" class="flex items-start gap-2">
                                <Clock class="w-4 h-4 text-muted-foreground mt-0.5" />
                                <div>
                                    <p class="text-muted-foreground">Paid At</p>
                                    <p class="font-medium">{{ formatDate(order.paid_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border/50">
                    <CardHeader>
                        <CardTitle>Order Items</CardTitle>
                        <CardDescription>{{ order.items.length }} item(s)</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="item in order.items" :key="item.id" class="flex gap-4">
                                <div class="w-16 h-16 rounded-lg bg-muted overflow-hidden flex-shrink-0">
                                    <img 
                                        v-if="item.product?.image_url"
                                        :src="item.product.image_url"
                                        :alt="item.product_name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="flex items-center justify-center h-full">
                                        <Package class="w-6 h-6 text-muted-foreground/30" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium">{{ item.product_name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ formatPrice(item.price) }} x {{ item.quantity }}
                                    </p>
                                </div>
                                <p class="font-bold">{{ formatPrice(item.subtotal) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border/50">
                    <CardHeader>
                        <CardTitle>Shipping Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-start gap-3">
                            <User class="w-4 h-4 text-muted-foreground mt-0.5" />
                            <div>
                                <p class="text-sm text-muted-foreground">Recipient Name</p>
                                <p class="font-medium">{{ order.shipping_name }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Phone class="w-4 h-4 text-muted-foreground mt-0.5" />
                            <div>
                                <p class="text-sm text-muted-foreground">Phone Number</p>
                                <p class="font-medium">{{ order.shipping_phone }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <MapPin class="w-4 h-4 text-muted-foreground mt-0.5" />
                            <div>
                                <p class="text-sm text-muted-foreground">Shipping Address</p>
                                <p class="font-medium">{{ order.shipping_address }}</p>
                            </div>
                        </div>
                        <div v-if="order.notes" class="flex items-start gap-3">
                            <FileText class="w-4 h-4 text-muted-foreground mt-0.5" />
                            <div>
                                <p class="text-sm text-muted-foreground">Notes</p>
                                <p class="font-medium">{{ order.notes }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="lg:col-span-1">
                <Card class="sticky top-24 border-border/50">
                    <CardHeader>
                        <CardTitle>Payment Summary</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>{{ formatPrice(order.total_amount) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Shipping</span>
                            <span class="text-green-500">Free</span>
                        </div>
                        <Separator />
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-primary">{{ formatPrice(order.total_amount) }}</span>
                        </div>
                    </CardContent>
                    <CardContent class="pt-0 space-y-3">
                        <Button 
                            v-if="order.payment_status === 'pending' && order.status !== 'cancelled'"
                            class="w-full" 
                            @click="handlePayOrder"
                        >
                            Pay Now (Simulated)
                        </Button>
                        <Button 
                            v-if="['pending', 'processing'].includes(order.status) && order.status !== 'cancelled'"
                            variant="outline" 
                            class="w-full text-destructive hover:text-destructive"
                            @click="handleCancelOrder"
                        >
                            Cancel Order
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>

