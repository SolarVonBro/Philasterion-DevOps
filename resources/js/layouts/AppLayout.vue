<template>
  <div class="layout">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <!-- Logo: gradient circle in cube, matches presentation -->
        <div class="brand-logo">
          <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%"   stop-color="#A855F7"/>
                <stop offset="35%"  stop-color="#EC4899"/>
                <stop offset="65%"  stop-color="#F59E0B"/>
                <stop offset="100%" stop-color="#06B6D4"/>
              </linearGradient>
            </defs>
            <rect x="2" y="2" width="28" height="28" rx="7" fill="url(#logoGrad)" opacity="0.15"/>
            <circle cx="16" cy="16" r="10" stroke="url(#logoGrad)" stroke-width="2" fill="none"/>
            <circle cx="16" cy="16" r="6"  stroke="url(#logoGrad)" stroke-width="1.5" fill="none" opacity="0.7"/>
            <circle cx="16" cy="16" r="2.5" fill="url(#logoGrad)"/>
            <path d="M16 6 Q22 11 22 16 Q22 21 16 26 Q10 21 10 16 Q10 11 16 6Z"
                  stroke="url(#logoGrad)" stroke-width="1" fill="none" opacity="0.5"/>
          </svg>
        </div>
        <div class="brand-text">
          <span class="brand-name">PHILASTERION</span>
          <span class="brand-tagline">Твоя погода · самочувствие</span>
        </div>
      </div>

      <nav class="sidebar-nav">
        <router-link :to="{ name: 'dashboard' }" class="nav-link" active-class="" exact-active-class="nav-link--active">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
          </svg>
          Главная
        </router-link>

        <router-link :to="{ name: 'profile' }" class="nav-link" active-class="nav-link--active" exact-active-class="nav-link--active">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          Мой профиль
        </router-link>

        <router-link :to="{ name: 'users.index' }" class="nav-link" active-class="nav-link--active" exact-active-class="nav-link--active">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          Пользователи
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info">
          <div class="user-avatar">{{ initials }}</div>
          <div class="user-details">
            <div class="user-name">{{ auth.user?.name }}</div>
            <div class="user-email">{{ auth.user?.email }}</div>
          </div>
        </div>
        <button class="btn-logout" @click="handleLogout">Выйти</button>
      </div>
    </aside>

    <main class="content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth   = useAuthStore();
const router = useRouter();

const initials = computed(() => {
    if (!auth.user?.name) return '?';
    return auth.user.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
});

async function handleLogout() {
    await auth.logout();
    router.push({ name: 'login' });
}
</script>

<style scoped>
.layout { display: flex; min-height: 100vh; }

/* ── Sidebar ── */
.sidebar {
  width: 256px;
  background: var(--color-sidebar-bg);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
}

/* Brand */
.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 16px 18px;
  border-bottom: 1px solid var(--color-sidebar-border);
}

.brand-logo {
  width: 36px;
  height: 36px;
  flex-shrink: 0;
}

.brand-logo svg { width: 100%; height: 100%; }

.brand-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.brand-name {
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: #fff;
  white-space: nowrap;
}

.brand-tagline {
  font-size: 9.5px;
  color: var(--color-sidebar-text);
  white-space: nowrap;
  letter-spacing: 0.02em;
}

/* Nav */
.sidebar-nav {
  flex: 1;
  padding: 14px 10px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 12px;
  border-radius: var(--radius-sm);
  font-size: 13.5px;
  font-weight: 500;
  color: var(--color-sidebar-text);
  transition: background var(--transition), color var(--transition);
}

.nav-link:hover {
  background: var(--color-sidebar-hover);
  color: #e2e8f0;
}

.nav-link--active {
  background: var(--color-primary);
  color: var(--color-sidebar-text-active);
}

.nav-link--active:hover { background: var(--color-primary-dark); }

.nav-icon { width: 16px; height: 16px; flex-shrink: 0; }

/* Footer */
.sidebar-footer {
  padding: 14px;
  border-top: 1px solid var(--color-sidebar-border);
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.user-info { display: flex; align-items: center; gap: 10px; min-width: 0; }

.user-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: var(--color-primary);
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-details { min-width: 0; }

.user-name {
  font-size: 13px;
  font-weight: 600;
  color: #e2e8f0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-email {
  font-size: 11px;
  color: var(--color-sidebar-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.btn-logout {
  width: 100%;
  padding: 7px;
  border: 1px solid var(--color-sidebar-border);
  border-radius: var(--radius-sm);
  background: transparent;
  color: var(--color-sidebar-text);
  font-size: 12.5px;
  cursor: pointer;
  transition: background var(--transition), color var(--transition), border-color var(--transition);
}

.btn-logout:hover {
  background: var(--color-danger);
  color: #fff;
  border-color: var(--color-danger);
}

/* Main content */
.content {
  flex: 1;
  overflow-y: auto;
  background: var(--color-bg);
  padding: 32px;
}
</style>
