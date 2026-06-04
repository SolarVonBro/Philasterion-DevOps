import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/api';

function _setToken(token) {
    localStorage.setItem('token', token);
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

function _clearToken() {
    localStorage.removeItem('token');
    delete api.defaults.headers.common['Authorization'];
}

export const useAuthStore = defineStore('auth', () => {
    const user        = ref(null);
    const initialized = ref(false);

    async function init() {
        const token = localStorage.getItem('token');
        if (token) {
            api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            try {
                const { data } = await api.get('/auth/me');
                user.value = data;
            } catch {
                localStorage.removeItem('token');
                delete api.defaults.headers.common['Authorization'];
            }
        }
        initialized.value = true;
    }

    async function login(credentials) {
        const { data } = await api.post('/auth/login', credentials);
        _setToken(data.token);
        user.value = data.user;
    }

    async function register(payload) {
        const { data } = await api.post('/auth/register', payload);
        _setToken(data.token);
        user.value = data.user;
    }

    async function logout() {
        try {
            await api.post('/auth/logout');
        } catch {
            // always clear local state regardless of server response
        } finally {
            _clearToken();
            user.value = null;
        }
    }

    return { user, initialized, init, login, register, logout };
});
