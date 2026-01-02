import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import './styles/style.css'
import "nprogress/nprogress.css";
import "./styles/nprogress.css";
import router from './routes/router'
import { useAuthStore } from './stores/auth'

const savedTheme = localStorage.getItem('theme') || 'dark'
if (savedTheme === 'dark') {
    document.documentElement.classList.add('dark')
} else {
    document.documentElement.classList.remove('dark')
}

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

const initApp = async () => {
    const authStore = useAuthStore()
    await authStore.checkAuth()
    app.mount('#app')
}

initApp()