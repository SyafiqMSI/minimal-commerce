<script setup>
import { ref, onMounted, computed, h } from 'vue'
import { useOrderStore } from '@/stores/order'
import { toast } from 'vue-sonner'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Separator } from '@/components/ui/separator'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Package, Search, Eye, TrendingUp, ShoppingBag, Clock, CheckCircle, XCircle, Truck, MapPin, Phone, User, MoreVertical } from 'lucide-vue-next'
import DataTable from '@/components/ui/datatable/DataTable.vue'
import DataTableColumnHeader from '@/components/ui/datatable/DataTableColumnHeader.vue'

const orderStore = useOrderStore()

const filters = ref({
    status: 'all',
    payment_status: 'all',
    search: ''
})

const selectedOrder = ref(null)
const showOrderDetail = ref(false)
const isUpdatingStatus = ref(false)

onMounted(async () => {
    await Promise.all([
        orderStore.fetchAdminOrders(),
        orderStore.fetchStats()
    ])
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

const getApiFilters = () => {
    return {
        status: filters.value.status === 'all' ? '' : filters.value.status,
        payment_status: filters.value.payment_status === 'all' ? '' : filters.value.payment_status,
        search: filters.value.search
    }
}

const handleSearch = async () => {
    await orderStore.fetchAdminOrders(getApiFilters())
}

const handleFilterChange = async () => {
    await orderStore.fetchAdminOrders(getApiFilters())
}

const handleViewOrder = (order) => {
    selectedOrder.value = order
    showOrderDetail.value = true
}

const handleUpdateStatus = async (newStatus) => {
    if (!selectedOrder.value) return
    
    isUpdatingStatus.value = true
    const result = await orderStore.updateOrderStatus(selectedOrder.value.id, newStatus)
    
    if (result.success) {
        selectedOrder.value = result.data
        toast.success(`Order status changed to ${newStatus}`)
        await orderStore.fetchAdminOrders(getApiFilters())
        await orderStore.fetchStats()
    } else {
        toast.error(result.message)
    }
    
    isUpdatingStatus.value = false
}

const statCards = computed(() => [
    { 
        title: 'Total Orders', 
        value: orderStore.stats?.total_orders || 0, 
        icon: ShoppingBag,
        color: 'text-primary'
    },
    { 
        title: 'Pending', 
        value: orderStore.stats?.pending_orders || 0, 
        icon: Clock,
        color: 'text-yellow-500'
    },
    { 
        title: 'Processing', 
        value: orderStore.stats?.processing_orders || 0, 
        icon: Truck,
        color: 'text-blue-500'
    },
    { 
        title: 'Delivered', 
        value: orderStore.stats?.delivered_orders || 0, 
        icon: CheckCircle,
        color: 'text-green-500'
    },
])

const paymentMethodLabels = {
    bank_transfer: 'Bank Transfer',
    e_wallet: 'E-Wallet',
    cod: 'Cash on Delivery',
}

const columns = [
    {
        accessorKey: 'order_number',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Order' }),
        cell: ({ row }) => h('div', {}, [
            h('p', { class: 'font-medium' }, row.original.order_number),
            h('p', { class: 'text-sm text-muted-foreground' }, `${row.original.items?.length || 0} items`)
        ])
    },
    {
        accessorKey: 'user.name',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Customer' }),
        cell: ({ row }) => h('div', {}, [
            h('p', { class: 'font-medium' }, row.original.user?.name),
            h('p', { class: 'text-sm text-muted-foreground' }, row.original.user?.email)
        ])
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Date' }),
        cell: ({ row }) => h('span', { class: 'text-sm' }, formatDate(row.original.created_at))
    },
    {
        accessorKey: 'status',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Status' }),
        cell: ({ row }) => h(Badge, { 
            class: getStatusColor(row.original.status), 
            variant: 'outline' 
        }, () => row.original.status)
    },
    {
        accessorKey: 'payment_status',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Payment' }),
        cell: ({ row }) => h(Badge, { 
            class: getPaymentStatusColor(row.original.payment_status), 
            variant: 'outline' 
        }, () => row.original.payment_status)
    },
    {
        accessorKey: 'total_amount',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Amount' }),
        cell: ({ row }) => h('span', { class: 'font-bold text-primary' }, formatPrice(row.original.total_amount))
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'text-center' }, 'Actions'),
        meta: { isActionColumn: true },
        cell: ({ row }) => h('div', { class: 'flex justify-center' }, [
            h(DropdownMenu, {}, {
                default: () => [
                    h(DropdownMenuTrigger, { asChild: true }, {
                        default: () => h(Button, { variant: 'ghost', size: 'icon', class: 'h-8 w-8' }, {
                            default: () => h(MoreVertical, { class: 'h-4 w-4' })
                        })
                    }),
                    h(DropdownMenuContent, { align: 'end' }, {
                        default: () => [
                            h(DropdownMenuItem, { 
                                class: 'cursor-pointer',
                                onClick: () => handleViewOrder(row.original)
                            }, {
                                default: () => [h(Eye, { class: 'mr-2 h-4 w-4' }), 'View Details']
                            })
                        ]
                    })
                ]
            })
        ])
    }
]
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-3xl font-bold mb-2">Orders Management</h1>
            <p class="text-muted-foreground">Track and manage all customer orders</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <Card v-for="stat in statCards" :key="stat.title" class="border-border/50">
                <CardHeader class="pb-2">
                    <CardDescription class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center">
                            <component :is="stat.icon" class="w-4 h-4" :class="stat.color" />
                        </div>
                        {{ stat.title }}
                    </CardDescription>
                    <CardTitle class="text-3xl">{{ stat.value }}</CardTitle>
                </CardHeader>
            </Card>
        </div>

        <Card class="border-border/50">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle>Revenue Overview</CardTitle>
                        <CardDescription>Total and today's revenue</CardDescription>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-4 rounded-lg bg-muted/50">
                        <div class="flex items-center gap-3 mb-2">
                            <TrendingUp class="w-5 h-5 text-green-500" />
                            <span class="text-sm text-muted-foreground">Total Revenue</span>
                        </div>
                        <p class="text-2xl font-bold text-primary">{{ formatPrice(orderStore.stats?.total_revenue || 0) }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-muted/50">
                        <div class="flex items-center gap-3 mb-2">
                            <TrendingUp class="w-5 h-5 text-blue-500" />
                            <span class="text-sm text-muted-foreground">Today's Revenue</span>
                        </div>
                        <p class="text-2xl font-bold text-primary">{{ formatPrice(orderStore.stats?.today_revenue || 0) }}</p>
                        <p class="text-sm text-muted-foreground">{{ orderStore.stats?.today_orders || 0 }} orders today</p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Card class="border-border/50">
            <CardHeader>
                <CardTitle>All Orders</CardTitle>
                <CardDescription>Manage and update order statuses</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="flex flex-col sm:flex-row gap-4 mb-6">
                    <div class="flex-1">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input 
                                v-model="filters.search" 
                                placeholder="Search by order number, customer name..."
                                class="pl-9"
                                @keyup.enter="handleSearch"
                            />
                        </div>
                    </div>
                    <Select v-model="filters.status" @update:modelValue="handleFilterChange">
                        <SelectTrigger class="w-full sm:w-40">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="processing">Processing</SelectItem>
                            <SelectItem value="shipped">Shipped</SelectItem>
                            <SelectItem value="delivered">Delivered</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filters.payment_status" @update:modelValue="handleFilterChange">
                        <SelectTrigger class="w-full sm:w-40">
                            <SelectValue placeholder="Payment" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Payment</SelectItem>
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="paid">Paid</SelectItem>
                            <SelectItem value="failed">Failed</SelectItem>
                            <SelectItem value="refunded">Refunded</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <DataTable
                    :columns="columns"
                    :data="orderStore.orders"
                    :isLoading="orderStore.isLoading"
                    :hide-toolbar="true"
                />
            </CardContent>
        </Card>

        <Dialog v-model:open="showOrderDetail">
            <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Order Details</DialogTitle>
                    <DialogDescription v-if="selectedOrder">
                        {{ selectedOrder.order_number }}
                    </DialogDescription>
                </DialogHeader>

                <div v-if="selectedOrder" class="space-y-6">
                    <div class="flex gap-2">
                        <Badge :class="getStatusColor(selectedOrder.status)" variant="outline">
                            {{ selectedOrder.status }}
                        </Badge>
                        <Badge :class="getPaymentStatusColor(selectedOrder.payment_status)" variant="outline">
                            {{ selectedOrder.payment_status }}
                        </Badge>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-muted-foreground">Customer</p>
                            <p class="font-medium">{{ selectedOrder.user?.name }}</p>
                            <p class="text-muted-foreground">{{ selectedOrder.user?.email }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Payment Method</p>
                            <p class="font-medium">{{ paymentMethodLabels[selectedOrder.payment_method] || selectedOrder.payment_method }}</p>
                        </div>
                    </div>

                    <Separator />

                    <div>
                        <h4 class="font-semibold mb-3">Shipping Information</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-start gap-2">
                                <User class="w-4 h-4 text-muted-foreground mt-0.5" />
                                <span>{{ selectedOrder.shipping_name }}</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Phone class="w-4 h-4 text-muted-foreground mt-0.5" />
                                <span>{{ selectedOrder.shipping_phone }}</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <MapPin class="w-4 h-4 text-muted-foreground mt-0.5" />
                                <span>{{ selectedOrder.shipping_address }}</span>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <div>
                        <h4 class="font-semibold mb-3">Order Items</h4>
                        <div class="space-y-3">
                            <div v-for="item in selectedOrder.items" :key="item.id" class="flex gap-3">
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
                                <div class="flex-1">
                                    <p class="font-medium">{{ item.product_name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ formatPrice(item.price) }} x {{ item.quantity }}</p>
                                </div>
                                <p class="font-bold">{{ formatPrice(item.subtotal) }}</p>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <div class="flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span class="text-primary">{{ formatPrice(selectedOrder.total_amount) }}</span>
                    </div>

                    <Separator />

                    <div>
                        <h4 class="font-semibold mb-3">Update Status</h4>
                        <div class="flex flex-wrap gap-2">
                            <Button 
                                v-if="selectedOrder.status !== 'pending'"
                                variant="outline" 
                                size="sm"
                                :disabled="isUpdatingStatus"
                                @click="handleUpdateStatus('pending')"
                            >
                                Pending
                            </Button>
                            <Button 
                                v-if="selectedOrder.status !== 'processing'"
                                variant="outline" 
                                size="sm"
                                :disabled="isUpdatingStatus"
                                @click="handleUpdateStatus('processing')"
                            >
                                Processing
                            </Button>
                            <Button 
                                v-if="selectedOrder.status !== 'shipped'"
                                variant="outline" 
                                size="sm"
                                :disabled="isUpdatingStatus"
                                @click="handleUpdateStatus('shipped')"
                            >
                                Shipped
                            </Button>
                            <Button 
                                v-if="selectedOrder.status !== 'delivered'"
                                size="sm"
                                :disabled="isUpdatingStatus"
                                @click="handleUpdateStatus('delivered')"
                            >
                                <CheckCircle class="w-4 h-4 mr-1" />
                                Delivered
                            </Button>
                            <Button 
                                v-if="selectedOrder.status !== 'cancelled'"
                                variant="destructive" 
                                size="sm"
                                :disabled="isUpdatingStatus"
                                @click="handleUpdateStatus('cancelled')"
                            >
                                <XCircle class="w-4 h-4 mr-1" />
                                Cancel
                            </Button>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showOrderDetail = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
