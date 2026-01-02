<script setup>
import { onMounted, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { toast } from 'vue-sonner'
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { Skeleton } from '@/components/ui/skeleton'
import { Package, Trash2, Minus, Plus, ShoppingCart, ArrowRight } from 'lucide-vue-next'
import { confirmModal } from '@/components/ui/confirmation-dialog'

const router = useRouter()
const cartStore = useCartStore()

onMounted(async () => {
    await cartStore.fetchCart()
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price)
}

const handleUpdateQuantity = async (item, newQuantity) => {
    if (newQuantity < 1) return
    await cartStore.updateQuantity(item.id, newQuantity)
}

const handleRemoveItem = async (item) => {
    const confirmed = await confirmModal('Remove Item', `Remove "${item.product.name}" from cart?`)
    if (confirmed) {
        const result = await cartStore.removeFromCart(item.id)
        if (result.success) {
            toast.success('Item removed from cart')
        }
    }
}

const handleClearCart = async () => {
    const confirmed = await confirmModal('Clear Cart', 'Are you sure you want to remove all items from your cart?')
    if (confirmed) {
        const result = await cartStore.clearCart()
        if (result.success) {
            toast.success('Cart cleared')
        }
    }
}

const handleCheckout = () => {
    router.push('/user/checkout')
}
</script>

<template>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Shopping Cart</h1>
                <p class="text-muted-foreground">{{ cartStore.totalItems }} item(s) in your cart</p>
            </div>
            <Button 
                v-if="cartStore.items.length > 0"
                variant="outline" 
                size="sm" 
                class="gap-2 text-destructive hover:text-destructive"
                @click="handleClearCart"
            >
                <Trash2 class="w-4 h-4" />
                Clear Cart
            </Button>
        </div>

        <div v-if="cartStore.isLoading" class="space-y-4">
            <Skeleton v-for="i in 3" :key="i" class="h-32 w-full" />
        </div>

        <div v-else-if="cartStore.items.length === 0" class="text-center py-16">
            <ShoppingCart class="w-16 h-16 mx-auto text-muted-foreground/30 mb-4" />
            <h2 class="text-xl font-semibold mb-2">Your cart is empty</h2>
            <p class="text-muted-foreground mb-6">Add some products to get started</p>
            <RouterLink to="/products">
                <Button class="gap-2">
                    Browse Products
                    <ArrowRight class="w-4 h-4" />
                </Button>
            </RouterLink>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                <Card v-for="item in cartStore.items" :key="item.id" class="border-border/50">
                    <CardContent class="p-4">
                        <div class="flex gap-4">
                            <RouterLink :to="`/products/${item.product.id}`" class="flex-shrink-0">
                                <div class="w-24 h-24 rounded-lg bg-muted overflow-hidden">
                                    <img 
                                        v-if="item.product.image_url"
                                        :src="item.product.image_url"
                                        :alt="item.product.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="flex items-center justify-center h-full">
                                        <Package class="w-8 h-8 text-muted-foreground/30" />
                                    </div>
                                </div>
                            </RouterLink>

                            <div class="flex-1 min-w-0">
                                <RouterLink :to="`/products/${item.product.id}`">
                                    <h3 class="font-semibold hover:text-primary transition-colors truncate">
                                        {{ item.product.name }}
                                    </h3>
                                </RouterLink>
                                <p class="text-sm text-muted-foreground mb-2">
                                    {{ item.product.category?.name }}
                                </p>
                                <p class="font-bold text-primary">
                                    {{ formatPrice(item.product.price) }}
                                </p>
                            </div>

                            <div class="flex flex-col items-end justify-between">
                                <Button 
                                    variant="ghost" 
                                    size="icon" 
                                    class="text-muted-foreground hover:text-destructive"
                                    @click="handleRemoveItem(item)"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>

                                <div class="flex items-center border rounded-lg">
                                    <Button 
                                        variant="ghost" 
                                        size="icon" 
                                        class="h-8 w-8"
                                        @click="handleUpdateQuantity(item, item.quantity - 1)"
                                        :disabled="item.quantity <= 1"
                                    >
                                        <Minus class="w-3 h-3" />
                                    </Button>
                                    <span class="w-8 text-center text-sm font-medium">{{ item.quantity }}</span>
                                    <Button 
                                        variant="ghost" 
                                        size="icon" 
                                        class="h-8 w-8"
                                        @click="handleUpdateQuantity(item, item.quantity + 1)"
                                    >
                                        <Plus class="w-3 h-3" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="lg:col-span-1">
                <Card class="sticky top-24 border-border/50">
                    <CardHeader>
                        <CardTitle>Order Summary</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Subtotal ({{ cartStore.totalItems }} items)</span>
                            <span>{{ formatPrice(cartStore.total) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Shipping</span>
                            <span class="text-green-500">Free</span>
                        </div>
                        <Separator />
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-primary">{{ formatPrice(cartStore.total) }}</span>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button class="w-full gap-2" size="lg" @click="handleCheckout">
                            Proceed to Checkout
                            <ArrowRight class="w-4 h-4" />
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </div>
</template>

