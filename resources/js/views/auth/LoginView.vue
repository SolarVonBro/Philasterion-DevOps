<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-brand">
        <div class="auth-logo">
          <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="lg" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%"   stop-color="#A855F7"/>
                <stop offset="35%"  stop-color="#EC4899"/>
                <stop offset="65%"  stop-color="#F59E0B"/>
                <stop offset="100%" stop-color="#06B6D4"/>
              </linearGradient>
            </defs>
            <rect x="2" y="2" width="28" height="28" rx="7" fill="url(#lg)" opacity="0.15"/>
            <circle cx="16" cy="16" r="10" stroke="url(#lg)" stroke-width="2" fill="none"/>
            <circle cx="16" cy="16" r="6"  stroke="url(#lg)" stroke-width="1.5" fill="none" opacity="0.7"/>
            <circle cx="16" cy="16" r="2.5" fill="url(#lg)"/>
            <path d="M16 6 Q22 11 22 16 Q22 21 16 26 Q10 21 10 16 Q10 11 16 6Z" stroke="url(#lg)" stroke-width="1" fill="none" opacity="0.5"/>
          </svg>
        </div>
        <span class="auth-brand-name">PHILASTERION</span>
      </div>
      <h1 class="auth-title">Вход</h1>
      <p class="auth-subtitle">Твоя погода · твоё самочувствие · твои помощники</p>

      <form @submit.prevent="handleSubmit" class="auth-form">
        <div class="field">
          <label for="email">Электронная почта</label>
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
          <label for="password">Пароль</label>
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
          <span v-if="loading">Вход…</span>
          <span v-else>Войти</span>
        </button>
      </form>

      <p class="auth-footer">
        Нет аккаунта?
        <router-link :to="{ name: 'register' }">Зарегистрироваться</router-link>
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
            generalError.value = err.response?.data?.message ?? 'Произошла ошибка.';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
.auth-page {
    min-height: 100vh;
    background: var(--color-sidebar-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.auth-card {
    background: var(--color-card);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: 40px;
    width: 100%;
    max-width: 420px;
}

.auth-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 28px;
}

.auth-logo { width: 38px; height: 38px; flex-shrink: 0; }
.auth-logo svg { width: 100%; height: 100%; }

.auth-brand-name {
    font-size: 17px;
    font-weight: 800;
    letter-spacing: 0.08em;
    color: var(--color-primary);
}

.auth-title { font-size: 22px; font-weight: 700; color: var(--color-text-primary); margin-bottom: 6px; }
.auth-subtitle { font-size: 13px; color: var(--color-text-secondary); margin-bottom: 28px; }
.auth-form { display: flex; flex-direction: column; gap: 18px; }

.field { display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 13px; font-weight: 600; color: var(--color-text-secondary); }
.field input {
    padding: 10px 14px;
    border: 1.5px solid var(--color-border);
    border-radius: var(--radius-md);
    font-size: 14px;
    outline: none;
    transition: border-color var(--transition);
}
.field input:focus { border-color: var(--color-primary); }
.field-error { font-size: 12px; color: var(--color-danger); }

.alert-error {
    padding: 10px 14px;
    background: var(--color-danger-light);
    border: 1px solid #fecaca;
    border-radius: var(--radius-md);
    color: #b91c1c;
    font-size: 13px;
}

.btn-primary {
    padding: 12px;
    background: var(--color-primary);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background var(--transition);
}
.btn-primary:hover:not(:disabled) { background: var(--color-primary-dark); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.auth-footer { margin-top: 20px; text-align: center; font-size: 13px; color: var(--color-text-secondary); }
.auth-footer a { color: var(--color-primary); font-weight: 600; }
.auth-footer a:hover { text-decoration: underline; }
</style>
