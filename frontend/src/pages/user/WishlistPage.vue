<script setup>
import { onMounted, ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useWishlistStore } from '@/stores/wishlist'
import { useCartStore } from '@/stores/cart'
import { toast } from 'vue-sonner'
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { Package, Trash2, Heart, ShoppingCart, ArrowRight, Check } from 'lucide-vue-next'
import { confirmModal } from '@/components/ui/confirmation-dialog'
import { Checkbox } from '@/components/ui/checkbox'

const wishlistStore = useWishlistStore()
const cartStore = useCartStore()

const selectedItems = ref(new Set())
const selectAll = ref(false)

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
    if (item.product.quantity === 0) {
        toast.error(`${item.product.name} is out of stock`)
        return
    }

    const result = await cartStore.addToCart(item.product_id)
    if (result.success) {
        toast.success(`${item.product.name} added to cart`)
    } else {
        toast.error(result.message)
    }
}

const selectedWishlistItems = computed(() => {
    return wishlistStore.items.filter(item => selectedItems.value.has(item.id))
})

const handleSelectItem = (itemId) => {
    if (selectedItems.value.has(itemId)) {
        selectedItems.value.delete(itemId)
    } else {
        selectedItems.value.add(itemId)
    }
    updateSelectAllState()
}

const handleSelectAll = () => {
    if (selectAll.value) {
        selectedItems.value.clear()
    } else {
        wishlistStore.items.forEach(item => selectedItems.value.add(item.id))
    }
    updateSelectAllState()
}

const updateSelectAllState = () => {
    selectAll.value = selectedItems.value.size === wishlistStore.items.length && wishlistStore.items.length > 0
}

const handleMoveSelectedToCart = async () => {
    if (selectedItems.value.size === 0) {
        toast.error('Please select at least one item')
        return
    }

    const selectedCount = selectedItems.value.size
    let successCount = 0

    for (const itemId of selectedItems.value) {
        const item = wishlistStore.items.find(i => i.id === itemId)
        if (item) {
            const result = await cartStore.addToCart(item.product_id)
            if (result.success) {
                successCount++
            }
        }
    }

    selectedItems.value.clear()
    updateSelectAllState()
    
    if (successCount > 0) {
        toast.success(`${successCount} item(s) added to cart`)
    } else {
        toast.error('Failed to add items to cart')
    }
}

const handleMoveAllToCart = async () => {
    let successCount = 0
    for (const item of wishlistStore.items) {
        const result = await cartStore.addToCart(item.product_id)
        if (result.success) {
            successCount++
        }
    }
    
    if (successCount > 0) {
        toast.success(`${successCount} item(s) added to cart`)
    } else {
        toast.error('Failed to add items to cart')
    }
}
</script>

<template>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">My Wishlist</h1>
                <p class="text-muted-foreground">{{ wishlistStore.items.length }} item(s) saved</p>
            </div>
            <div class="flex gap-2" v-if="wishlistStore.items.length > 0">
                <Button
                    variant="outline"
                    size="sm"
                    class="gap-2"
                    @click="handleSelectAll"
                >
                    <Checkbox :model-value="selectAll" />
                    {{ selectAll ? 'Deselect All' : 'Select All' }}
                </Button>
                <Button
                    v-if="selectedItems.size > 0"
                    variant="default"
                    size="sm"
                    class="gap-2"
                    @click="handleMoveSelectedToCart"
                >
                    <ShoppingCart class="w-4 h-4" />
                    Add Selected ({{ selectedItems.size }})
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    class="gap-2"
                    @click="handleMoveAllToCart"
                >
                    <ShoppingCart class="w-4 h-4" />
                    Add All to Cart
                </Button>
            </div>
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
            <Card
                v-for="item in wishlistStore.items"
                :key="item.id"
                :class="[
                    'overflow-hidden border-border/50 group cursor-pointer transition-all duration-200',
                    selectedItems.has(item.id) ? 'ring-2 ring-primary bg-primary/5' : 'hover:shadow-md'
                ]"
                @click="handleSelectItem(item.id)"
            >
                <div class="relative h-48 bg-gradient-to-br from-muted to-muted/50 overflow-hidden">
                    <div v-if="selectedItems.has(item.id)" class="absolute top-2 left-2 w-6 h-6 bg-primary rounded-full flex items-center justify-center z-10">
                        <Check class="w-3 h-3 text-primary-foreground" />
                    </div>
                    <RouterLink :to="`/products/${item.product.id}`" @click.stop>
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
                        @click.stop="handleRemoveFromWishlist(item)"
                    >
                        <Trash2 class="w-4 h-4" />
                    </Button>
                </div>
                <CardHeader class="pb-2">
                    <RouterLink :to="`/products/${item.product.id}`" @click.stop>
                        <CardTitle class="text-lg line-clamp-1 hover:text-primary transition-colors">
                            {{ item.product.name }}
                        </CardTitle>
                    </RouterLink>
                </CardHeader>
                <CardContent class="pb-2">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-primary">
                            {{ formatPrice(item.product.price) }}
                        </span>
                        <span class="text-xs" :class="item.product.quantity > 0 ? 'text-green-600' : 'text-red-600'">
                            {{ item.product.quantity > 0 ? `Stock: ${item.product.quantity}` : 'Out of stock' }}
                        </span>
                    </div>
                </CardContent>
                <CardFooter>
                    <Button
                        class="w-full gap-2"
                        @click.stop="handleAddToCart(item)"
                        :disabled="item.product.quantity === 0"
                    >
                        <ShoppingCart class="w-4 h-4" />
                        {{ item.product.quantity === 0 ? 'Out of Stock' : 'Add to Cart' }}
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </div>
</template>

