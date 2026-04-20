<template>
  <div>
    <h1 class="page-title">Главная</h1>

    <div v-if="loading" class="loading">Загрузка…</div>

    <div v-else class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon stat-icon--blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.users }}</div>
          <div class="stat-label">Всего пользователей</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-icon--green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
        </div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.diaryEntries }}</div>
          <div class="stat-label">Записей в дневнике</div>
        </div>
      </div>
    </div>

    <div class="quick-links">
      <h2 class="section-title">Быстрые действия</h2>
      <div class="link-grid">
        <router-link :to="{ name: 'profile' }" class="quick-link quick-link--accent">
          Мой профиль
        </router-link>
        <router-link :to="{ name: 'users.create' }" class="quick-link">
          Добавить пользователя
        </router-link>
        <router-link :to="{ name: 'users.index' }" class="quick-link">
          Управление пользователями
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import api from '@/api';

const loading = ref(true);
const stats   = reactive({ users: 0, diaryEntries: 0 });

onMounted(async () => {
    try {
        const [usersRes, diaryRes] = await Promise.all([
            api.get('/users?per_page=1'),
            api.get('/diary?per_page=1'),
        ]);
        stats.users        = usersRes.data.meta?.total ?? 0;
        stats.diaryEntries = diaryRes.data.meta?.total ?? 0;
    } catch {
        // non-critical
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
.page-title {
  font-size: 24px;
  font-weight: 700;
  color: var(--color-text-primary);
  margin-bottom: 28px;
}

.loading { color: var(--color-text-secondary); font-size: 14px; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 36px;
}

.stat-card {
  background: var(--color-card);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stat-icon svg { width: 22px; height: 22px; }
.stat-icon--blue  { background: var(--color-primary-light); color: var(--color-primary); }
.stat-icon--green { background: var(--color-accent-light);  color: var(--color-accent); }

.stat-value { font-size: 28px; font-weight: 700; color: var(--color-text-primary); line-height: 1; }
.stat-label { font-size: 13px; color: var(--color-text-secondary); margin-top: 4px; }

.section-title { font-size: 16px; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 14px; }

.link-grid { display: flex; gap: 12px; flex-wrap: wrap; }

.quick-link {
  display: inline-flex;
  align-items: center;
  padding: 10px 18px;
  background: var(--color-card);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: 14px;
  font-weight: 500;
  color: var(--color-text-primary);
  transition: all var(--transition);
}
.quick-link:hover { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
.quick-link--accent:hover { background: var(--color-accent); border-color: var(--color-accent); }
</style>
