<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useOrderStore } from '@/stores/order'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue-sonner'
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Separator } from '@/components/ui/separator'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { Package, CreditCard, Wallet, Truck, ArrowLeft, CheckCircle } from 'lucide-vue-next'

const router = useRouter()
const cartStore = useCartStore()
const orderStore = useOrderStore()
const authStore = useAuthStore()

const isSubmitting = ref(false)
const orderCreated = ref(false)
const createdOrder = ref(null)

const form = ref({
    shipping_name: '',
    shipping_phone: '',
    shipping_address: '',
    payment_method: 'bank_transfer',
    notes: ''
})

const errors = ref({})

onMounted(async () => {
    await cartStore.fetchCart()
    
    if (cartStore.items.length === 0) {
        router.push('/user/cart')
        return
    }

    if (authStore.user) {
        form.value.shipping_name = authStore.user.name
    }
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price)
}

const paymentMethods = [
    { id: 'bank_transfer', name: 'Bank Transfer', icon: CreditCard, description: 'Transfer to our bank account' },
    { id: 'e_wallet', name: 'E-Wallet', icon: Wallet, description: 'Pay with GoPay, OVO, Dana' },
    { id: 'cod', name: 'Cash on Delivery', icon: Truck, description: 'Pay when package arrives' },
]

const handleSubmit = async () => {
    errors.value = {}

    if (!form.value.shipping_name) {
        errors.value.shipping_name = 'Name is required'
    }
    if (!form.value.shipping_phone) {
        errors.value.shipping_phone = 'Phone number is required'
    }
    if (!form.value.shipping_address) {
        errors.value.shipping_address = 'Address is required'
    }

    if (Object.keys(errors.value).length > 0) {
        return
    }

    isSubmitting.value = true
    
    const result = await orderStore.createOrder(form.value)
    
    if (result.success) {
        createdOrder.value = result.data
        orderCreated.value = true
        await cartStore.fetchCart()
    } else {
        if (result.errors) {
            errors.value = result.errors
        }
        toast.error(result.message)
    }
    
    isSubmitting.value = false
}

const handlePayNow = async () => {
    if (!createdOrder.value) return
    
    isSubmitting.value = true
    const result = await orderStore.payOrder(createdOrder.value.id)
    
    if (result.success) {
        toast.success('Payment successful')
        router.push(`/user/orders/${createdOrder.value.id}`)
    } else {
        toast.error(result.message)
    }
    
    isSubmitting.value = false
}

const handleViewOrder = () => {
    if (createdOrder.value) {
        router.push(`/user/orders/${createdOrder.value.id}`)
    }
}
</script>

<template>
    <div class="space-y-8">
        <div class="flex items-center gap-4">
            <Button variant="ghost" size="icon" @click="router.back()">
                <ArrowLeft class="w-5 h-5" />
            </Button>
            <div>
                <h1 class="text-3xl font-bold mb-1">Checkout</h1>
                <p class="text-muted-foreground">Complete your order</p>
            </div>
        </div>

        <div v-if="orderCreated" class="max-w-2xl mx-auto">
            <Card class="border-border/50 text-center">
                <CardContent class="pt-12 pb-8">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-green-500/10 flex items-center justify-center">
                        <CheckCircle class="w-10 h-10 text-green-500" />
                    </div>
                    <h2 class="text-2xl font-bold mb-2">Order Placed Successfully!</h2>
                    <p class="text-muted-foreground mb-6">
                        Your order <span class="font-semibold text-foreground">{{ createdOrder.order_number }}</span> has been created.
                    </p>
                    <div class="bg-muted/50 rounded-lg p-4 mb-6">
                        <p class="text-sm text-muted-foreground mb-1">Total Amount</p>
                        <p class="text-2xl font-bold text-primary">{{ formatPrice(createdOrder.total_amount) }}</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Button v-if="createdOrder.payment_status === 'pending'" @click="handlePayNow" :disabled="isSubmitting">
                            Pay Now (Simulated)
                        </Button>
                        <Button variant="outline" @click="handleViewOrder">
                            View Order Details
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <Card class="border-border/50">
                    <CardHeader>
                        <CardTitle>Shipping Information</CardTitle>
                        <CardDescription>Enter your delivery details</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="shipping_name">Full Name</Label>
                            <Input 
                                id="shipping_name" 
                                v-model="form.shipping_name" 
                                placeholder="Enter your full name"
                                :class="{ 'border-destructive': errors.shipping_name }"
                            />
                            <p v-if="errors.shipping_name" class="text-sm text-destructive">{{ errors.shipping_name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="shipping_phone">Phone Number</Label>
                            <Input 
                                id="shipping_phone" 
                                v-model="form.shipping_phone" 
                                placeholder="Enter your phone number"
                                :class="{ 'border-destructive': errors.shipping_phone }"
                            />
                            <p v-if="errors.shipping_phone" class="text-sm text-destructive">{{ errors.shipping_phone }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="shipping_address">Shipping Address</Label>
                            <Textarea 
                                id="shipping_address" 
                                v-model="form.shipping_address" 
                                placeholder="Enter your complete address"
                                rows="3"
                                :class="{ 'border-destructive': errors.shipping_address }"
                            />
                            <p v-if="errors.shipping_address" class="text-sm text-destructive">{{ errors.shipping_address }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="notes">Order Notes (Optional)</Label>
                            <Textarea 
                                id="notes" 
                                v-model="form.notes" 
                                placeholder="Any special instructions?"
                                rows="2"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-border/50">
                    <CardHeader>
                        <CardTitle>Payment Method</CardTitle>
                        <CardDescription>Select how you want to pay</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <RadioGroup v-model="form.payment_method" class="space-y-3">
                            <div 
                                v-for="method in paymentMethods" 
                                :key="method.id"
                                class="flex items-center space-x-3 p-4 border rounded-lg cursor-pointer hover:border-primary/50 transition-colors"
                                :class="{ 'border-primary bg-primary/5': form.payment_method === method.id }"
                                @click="form.payment_method = method.id"
                            >
                                <RadioGroupItem :value="method.id" :id="method.id" />
                                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <component :is="method.icon" class="w-5 h-5 text-primary" />
                                </div>
                                <div class="flex-1">
                                    <Label :for="method.id" class="font-medium cursor-pointer">{{ method.name }}</Label>
                                    <p class="text-sm text-muted-foreground">{{ method.description }}</p>
                                </div>
                            </div>
                        </RadioGroup>
                    </CardContent>
                </Card>
            </div>

            <div class="lg:col-span-1">
                <Card class="sticky top-24 border-border/50">
                    <CardHeader>
                        <CardTitle>Order Summary</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            <div v-for="item in cartStore.items" :key="item.id" class="flex gap-3">
                                <div class="w-12 h-12 rounded-lg bg-muted overflow-hidden flex-shrink-0">
                                    <img 
                                        v-if="item.product.image_url"
                                        :src="item.product.image_url"
                                        :alt="item.product.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="flex items-center justify-center h-full">
                                        <Package class="w-5 h-5 text-muted-foreground/30" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ item.product.name }}</p>
                                    <p class="text-xs text-muted-foreground">x{{ item.quantity }}</p>
                                </div>
                                <p class="text-sm font-medium">{{ formatPrice(item.subtotal) }}</p>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span>{{ formatPrice(cartStore.total) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Shipping</span>
                                <span class="text-green-500">Free</span>
                            </div>
                        </div>

                        <Separator />

                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-primary">{{ formatPrice(cartStore.total) }}</span>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button 
                            class="w-full" 
                            size="lg" 
                            @click="handleSubmit"
                            :disabled="isSubmitting || cartStore.items.length === 0"
                        >
                            {{ isSubmitting ? 'Processing...' : 'Place Order' }}
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </div>
</template>

