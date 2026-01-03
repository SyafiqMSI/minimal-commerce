import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || '',
});

api.interceptors.request.use(config => {
    const token = localStorage.getItem("access_token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, error => {
    return Promise.reject(error);
});

api.interceptors.response.use(
    response => response,
    error => {
        const { response } = error;
        if (response) {
            const isAuthPage = window.location.pathname === '/login' || window.location.pathname === '/register';
            
            if (response.status === 401 || response.status === 403 || response.status === 502) {
                if (isAuthPage) {
                    return Promise.reject(error);
                }
                
                try {
                    localStorage.removeItem('access_token');
                    localStorage.removeItem('refresh_token');
                    localStorage.removeItem('auth_user');
                } catch (e) {
                }
                if (window && window.location) {
                    if (window.opener && window.location.pathname.startsWith('/print/')) {
                        window.close();
                    } else {
                        window.location.href = '/';
                    }
                }
            }
        }
        return Promise.reject(error);
    }
);

export default api;