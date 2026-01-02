<script setup>
import { RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Home, Search, ArrowLeft } from 'lucide-vue-next'

</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-background via-background to-muted/30 p-4">
        <div class="absolute top-20 left-10 w-72 h-72 bg-primary/10 rounded-full blur-3xl" />
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-primary/5 rounded-full blur-3xl" />
        
        <Card class="w-full max-w-lg text-center relative backdrop-blur-sm bg-card/80 border-border/50 shadow-2xl">
            <CardHeader class="pb-4">
                <CardTitle class="text-4xl font-bold mb-2">404</CardTitle>
                <CardDescription class="text-lg">
                    Oops! The page you're looking for doesn't exist.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <p class="text-muted-foreground">
                    The page might have been moved, deleted, or never existed in the first place.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                    <RouterLink to="/">
                        <Button class="gap-2 w-full sm:w-auto">
                            <Home class="w-4 h-4" />
                            Go to Home
                        </Button>
                    </RouterLink>
                    <RouterLink to="/products">
                        <Button variant="outline" class="gap-2 w-full sm:w-auto">
                            <Search class="w-4 h-4" />
                            Browse Products
                        </Button>
                    </RouterLink>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
