import { describe, it, expect, vi, beforeEach, beforeAll } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';

vi.mock('@/api', () => ({
    default: {
        defaults: { headers: { common: {} } },
        get: vi.fn(),
        post: vi.fn(),
    },
}));

// localStorage mock with full implementation
const storage = {};
beforeAll(() => {
    vi.stubGlobal('localStorage', {
        getItem:    (key) => storage[key] ?? null,
        setItem:    (key, value) => { storage[key] = String(value); },
        removeItem: (key) => { delete storage[key]; },
        clear:      () => { Object.keys(storage).forEach(k => delete storage[k]); },
    });
});

import { useAuthStore } from '../stores/auth';
import api from '@/api';

describe('useAuthStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        Object.keys(storage).forEach(k => delete storage[k]);
        vi.clearAllMocks();
        api.defaults.headers.common = {};
    });

    it('starts with no user and not initialized', () => {
        const store = useAuthStore();
        expect(store.user).toBeNull();
        expect(store.initialized).toBe(false);
    });

    it('init() sets initialized=true when no token stored', async () => {
        const store = useAuthStore();
        await store.init();
        expect(store.initialized).toBe(true);
        expect(store.user).toBeNull();
    });

    it('init() fetches user when token exists', async () => {
        storage['token'] = 'valid-token';
        api.get.mockResolvedValue({ data: { id: 1, name: 'Test User' } });

        const store = useAuthStore();
        await store.init();

        expect(api.get).toHaveBeenCalledWith('/auth/me');
        expect(store.user).toEqual({ id: 1, name: 'Test User' });
        expect(store.initialized).toBe(true);
    });

    it('init() clears token when API returns error', async () => {
        storage['token'] = 'expired-token';
        api.get.mockRejectedValue(new Error('401 Unauthorized'));

        const store = useAuthStore();
        await store.init();

        expect(storage['token']).toBeUndefined();
        expect(store.user).toBeNull();
        expect(store.initialized).toBe(true);
    });

    it('login() stores token and sets user', async () => {
        api.post.mockResolvedValue({
            data: { user: { id: 2, name: 'John' }, token: 'login-token' },
        });

        const store = useAuthStore();
        await store.login({ email: 'john@test.com', password: 'secret' });

        expect(storage['token']).toBe('login-token');
        expect(api.defaults.headers.common['Authorization']).toBe('Bearer login-token');
        expect(store.user).toEqual({ id: 2, name: 'John' });
    });

    it('register() stores token and sets user', async () => {
        api.post.mockResolvedValue({
            data: { user: { id: 3, name: 'New User' }, token: 'reg-token' },
        });

        const store = useAuthStore();
        await store.register({ name: 'New User', email: 'new@test.com', password: 'pass' });

        expect(storage['token']).toBe('reg-token');
        expect(store.user).toEqual({ id: 3, name: 'New User' });
    });

    it('logout() clears user and token on success', async () => {
        storage['token'] = 'active-token';
        api.post.mockResolvedValue({});

        const store = useAuthStore();
        store.user = { id: 1 };
        await store.logout();

        expect(store.user).toBeNull();
        expect(storage['token']).toBeUndefined();
        expect(api.defaults.headers.common['Authorization']).toBeUndefined();
    });

    it('logout() clears user and token even when API fails', async () => {
        storage['token'] = 'active-token';
        api.post.mockRejectedValue(new Error('Network error'));

        const store = useAuthStore();
        store.user = { id: 1 };
        await store.logout();

        expect(store.user).toBeNull();
        expect(storage['token']).toBeUndefined();
    });
});
