<script setup>
import { onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useWishlistStore } from '@/stores/wishlist'
import { useCartStore } from '@/stores/cart'
import { toast } from 'vue-sonner'
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { Package, Trash2, Heart, ShoppingCart, ArrowRight } from 'lucide-vue-next'
import { confirmModal } from '@/components/ui/confirmation-dialog'

const wishlistStore = useWishlistStore()
const cartStore = useCartStore()

onMounted(async () => {
    await wishlistStore.fetchWishlist()
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price)
}

const handleRemoveFromWishlist = async (item) => {
    const confirmed = await confirmModal('Remove from Wishlist', `Remove "${item.product.name}" from wishlist?`)
    if (confirmed) {
        const result = await wishlistStore.removeFromWishlist(item.id)
        if (result.success) {
            toast.success('Item removed from wishlist')
        }
    }
}

const handleAddToCart = async (item) => {
    const result = await cartStore.addToCart(item.product_id)
    if (result.success) {
        toast.success(`${item.product.name} added to cart`)
    } else {
        toast.error(result.message)
    }
}

const handleMoveAllToCart = async () => {
    for (const item of wishlistStore.items) {
        await cartStore.addToCart(item.product_id)
    }
    toast.success('All wishlist items added to cart')
}
</script>

<template>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">My Wishlist</h1>
                <p class="text-muted-foreground">{{ wishlistStore.items.length }} item(s) saved</p>
            </div>
            <Button 
                v-if="wishlistStore.items.length > 0"
                variant="outline" 
                size="sm" 
                class="gap-2"
                @click="handleMoveAllToCart"
            >
                <ShoppingCart class="w-4 h-4" />
                Add All to Cart
            </Button>
        </div>

        <div v-if="wishlistStore.isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <Skeleton v-for="i in 4" :key="i" class="h-72" />
        </div>

        <div v-else-if="wishlistStore.items.length === 0" class="text-center py-16">
            <Heart class="w-16 h-16 mx-auto text-muted-foreground/30 mb-4" />
            <h2 class="text-xl font-semibold mb-2">Your wishlist is empty</h2>
            <p class="text-muted-foreground mb-6">Save items you love to your wishlist</p>
            <RouterLink to="/products">
                <Button class="gap-2">
                    Browse Products
                    <ArrowRight class="w-4 h-4" />
                </Button>
            </RouterLink>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <Card v-for="item in wishlistStore.items" :key="item.id" class="overflow-hidden border-border/50 group">
                <div class="relative h-48 bg-gradient-to-br from-muted to-muted/50 overflow-hidden">
                    <RouterLink :to="`/products/${item.product.id}`">
                        <img 
                            v-if="item.product.image_url"
                            :src="item.product.image_url"
                            :alt="item.product.name"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                        <div v-else class="flex items-center justify-center h-full">
                            <Package class="w-12 h-12 text-muted-foreground/30" />
                        </div>
                    </RouterLink>
                    <Button 
                        variant="outline" 
                        size="icon" 
                        class="absolute top-2 right-2 bg-background/80 backdrop-blur-sm hover:bg-destructive hover:text-destructive-foreground"
                        @click="handleRemoveFromWishlist(item)"
                    >
                        <Trash2 class="w-4 h-4" />
                    </Button>
                </div>
                <CardHeader class="pb-2">
                    <RouterLink :to="`/products/${item.product.id}`">
                        <CardTitle class="text-lg line-clamp-1 hover:text-primary transition-colors">
                            {{ item.product.name }}
                        </CardTitle>
                    </RouterLink>
                </CardHeader>
                <CardContent class="pb-2">
                    <span class="text-lg font-bold text-primary">
                        {{ formatPrice(item.product.price) }}
                    </span>
                </CardContent>
                <CardFooter>
                    <Button class="w-full gap-2" @click="handleAddToCart(item)">
                        <ShoppingCart class="w-4 h-4" />
                        Add to Cart
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </div>
</template>

