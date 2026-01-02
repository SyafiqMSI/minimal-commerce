<script setup>
import { ref, computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { toast } from 'vue-sonner'
import { Eye, EyeOff, Loader2 } from 'lucide-vue-next'
import Image from '@/components/Image.vue'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const errors = ref({})

const handleSubmit = async () => {
    errors.value = {}
    
    if (!email.value) {
        errors.value.email = 'Email is required'
        return
    }
    if (!password.value) {
        errors.value.password = 'Password is required'
        return
    }

    const result = await authStore.login(email.value, password.value)
    
    if (result.success) {
        toast.success('Login successful!')
        router.push(authStore.isAdmin ? '/admin' : '/user')
    } else {
        toast.error(result.message)
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-background p-4">
        <Card class="w-full max-w-md relative border-border/50">
            <CardHeader class="text-center pb-2">
                <RouterLink to="/" class="flex items-center justify-center mb-4 group">
                    <Image src="/logo.png" alt="Logo" class="h-14 w-auto" />
                </RouterLink>
                <CardTitle class="text-2xl font-bold">Welcome Back</CardTitle>
                <CardDescription>Sign in to your account to continue</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input 
                            id="email"
                            v-model="email"
                            type="email"
                            placeholder="Enter your email"
                            :class="{ 'border-destructive': errors.email }"
                        />
                        <p v-if="errors.email" class="text-sm text-destructive">{{ errors.email }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <div class="relative">
                            <Input 
                                id="password"
                                v-model="password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Enter your password"
                                class="pr-10"
                                :class="{ 'border-destructive': errors.password }"
                            />
                            <button 
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                            >
                                <EyeOff v-if="showPassword" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="errors.password" class="text-sm text-destructive">{{ errors.password }}</p>
                    </div>
                    <Button type="submit" class="w-full" :disabled="authStore.isLoading">
                        <Loader2 v-if="authStore.isLoading" class="w-4 h-4 mr-2 animate-spin" />
                        {{ authStore.isLoading ? 'Signing in...' : 'Sign In' }}
                    </Button>
                </form>
            </CardContent>
            <CardFooter class="flex flex-col gap-4">
                <div class="relative w-full">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t" />
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-card px-2 text-muted-foreground">Or</span>
                    </div>
                </div>
                <p class="text-sm text-center text-muted-foreground">
                    Don't have an account?
                    <RouterLink to="/register" class="font-medium text-primary hover:underline">
                        Sign up
                    </RouterLink>
                </p>
            </CardFooter>
        </Card>
    </div>
</template>
