<script setup>
import { ref } from 'vue'
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

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const showPassword = ref(false)
const errors = ref({})

const handleSubmit = async () => {
    errors.value = {}
    
    if (!name.value) {
        errors.value.name = 'Name is required'
    }
    if (!email.value) {
        errors.value.email = 'Email is required'
    }
    if (!password.value) {
        errors.value.password = 'Password is required'
    } else if (password.value.length < 8) {
        errors.value.password = 'Password must be at least 8 characters'
    }
    if (password.value !== password_confirmation.value) {
        errors.value.password_confirmation = 'Passwords do not match'
    }

    if (Object.keys(errors.value).length > 0) return

    const result = await authStore.register(
        name.value,
        email.value,
        password.value,
        password_confirmation.value
    )
    
    if (result.success) {
        toast.success('Registration successful!')
        router.push('/user')
    } else {
        if (result.errors) {
            errors.value = result.errors
        }
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
                <CardTitle class="text-2xl font-bold">Create Account</CardTitle>
                <CardDescription>Sign up to start shopping</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Full Name</Label>
                        <Input 
                            id="name"
                            v-model="name"
                            type="text"
                            placeholder="Enter your full name"
                            :class="{ 'border-destructive': errors.name }"
                        />
                        <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
                    </div>
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
                                placeholder="Create a password"
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
                    <div class="space-y-2">
                        <Label for="password_confirmation">Confirm Password</Label>
                        <Input 
                            id="password_confirmation"
                            v-model="password_confirmation"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Confirm your password"
                            :class="{ 'border-destructive': errors.password_confirmation }"
                        />
                        <p v-if="errors.password_confirmation" class="text-sm text-destructive">{{ errors.password_confirmation }}</p>
                    </div>
                    <Button type="submit" class="w-full" :disabled="authStore.isLoading">
                        <Loader2 v-if="authStore.isLoading" class="w-4 h-4 mr-2 animate-spin" />
                        {{ authStore.isLoading ? 'Creating account...' : 'Create Account' }}
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
                    Already have an account?
                    <RouterLink to="/login" class="font-medium text-primary hover:underline">
                        Sign in
                    </RouterLink>
                </p>
            </CardFooter>
        </Card>
    </div>
</template>
