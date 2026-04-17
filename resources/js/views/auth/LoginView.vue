<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1 class="auth-title">Sign In</h1>
      <p class="auth-subtitle">Welcome back! Please sign in to continue.</p>

      <form @submit.prevent="handleSubmit" class="auth-form">
        <div class="field">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="you@example.com"
            autocomplete="email"
            required
          />
          <span v-if="errors.email" class="field-error">{{ errors.email }}</span>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="••••••••"
            autocomplete="current-password"
            required
          />
          <span v-if="errors.password" class="field-error">{{ errors.password }}</span>
        </div>

        <div v-if="generalError" class="alert-error">{{ generalError }}</div>

        <button type="submit" class="btn-primary" :disabled="loading">
          <span v-if="loading">Signing in…</span>
          <span v-else>Sign In</span>
        </button>
      </form>

      <p class="auth-footer">
        Don't have an account?
        <router-link :to="{ name: 'register' }">Register</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth   = useAuthStore();
const router = useRouter();

const form = reactive({ email: '', password: '' });
const errors       = reactive({});
const generalError = ref('');
const loading      = ref(false);

async function handleSubmit() {
    Object.keys(errors).forEach(k => delete errors[k]);
    generalError.value = '';
    loading.value = true;

    try {
        await auth.login(form);
        router.push({ name: 'dashboard' });
    } catch (err) {
        if (err.response?.status === 422) {
            const errs = err.response.data.errors ?? {};
            Object.assign(errors, Object.fromEntries(
                Object.entries(errs).map(([k, v]) => [k, v[0]])
            ));
        } else {
            generalError.value = err.response?.data?.message ?? 'An error occurred.';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
.auth-page {
    min-height: 100vh;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.auth-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    padding: 40px;
    width: 100%;
    max-width: 420px;
}

.auth-title {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 6px;
}

.auth-subtitle {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 28px;
}

.auth-form { display: flex; flex-direction: column; gap: 18px; }

.field { display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 13px; font-weight: 600; color: #374151; }
.field input {
    padding: 10px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.15s;
}
.field input:focus { border-color: #3b82f6; }
.field-error { font-size: 12px; color: #ef4444; }

.alert-error {
    padding: 10px 14px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    color: #b91c1c;
    font-size: 13px;
}

.btn-primary {
    padding: 12px;
    background: #3b82f6;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-primary:hover:not(:disabled) { background: #2563eb; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.auth-footer {
    margin-top: 20px;
    text-align: center;
    font-size: 13px;
    color: #64748b;
}
.auth-footer a { color: #3b82f6; font-weight: 600; }
.auth-footer a:hover { text-decoration: underline; }
</style>
