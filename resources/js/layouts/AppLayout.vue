<template>
  <div class="layout">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <span class="brand-icon">⚡</span>
        <span>Admin Panel</span>
      </div>

      <nav class="sidebar-nav">
        <router-link :to="{ name: 'dashboard' }" class="nav-link" active-class="nav-link--active">
          <span class="nav-icon">🏠</span> Dashboard
        </router-link>
        <router-link :to="{ name: 'users.index' }" class="nav-link" active-class="nav-link--active">
          <span class="nav-icon">👥</span> Users
        </router-link>
        <router-link :to="{ name: 'failed-jobs.index' }" class="nav-link" active-class="nav-link--active">
          <span class="nav-icon">⚠️</span> Failed Jobs
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
        <button class="btn-logout" @click="handleLogout">Logout</button>
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
    return auth.user.name
        .split(' ')
        .map(w => w[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

async function handleLogout() {
    await auth.logout();
    router.push({ name: 'login' });
}
</script>

<style scoped>
.layout {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 260px;
    background: #1e293b;
    color: #cbd5e1;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 24px 20px;
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    border-bottom: 1px solid #334155;
}

.brand-icon { font-size: 22px; }

.sidebar-nav {
    flex: 1;
    padding: 16px 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #94a3b8;
    transition: background 0.15s, color 0.15s;
}

.nav-link:hover { background: #334155; color: #e2e8f0; }
.nav-link--active { background: #3b82f6 !important; color: #fff !important; }

.nav-icon { font-size: 16px; }

.sidebar-footer {
    padding: 16px;
    border-top: 1px solid #334155;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #3b82f6;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.user-name {
    font-size: 13px;
    font-weight: 600;
    color: #e2e8f0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 140px;
}

.user-email {
    font-size: 11px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 140px;
}

.btn-logout {
    width: 100%;
    padding: 8px;
    border: 1px solid #475569;
    border-radius: 6px;
    background: transparent;
    color: #94a3b8;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}

.btn-logout:hover { background: #ef4444; color: #fff; border-color: #ef4444; }

.content {
    flex: 1;
    overflow-y: auto;
    background: #f1f5f9;
    padding: 32px;
}
</style>
