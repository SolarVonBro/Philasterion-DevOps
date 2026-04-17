<template>
  <div>
    <h1 class="page-title">Dashboard</h1>

    <div v-if="loading" class="loading">Loading…</div>

    <div v-else class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon stat-icon--blue">👥</div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.users }}</div>
          <div class="stat-label">Total Users</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-icon--red">⚠️</div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.failedJobs }}</div>
          <div class="stat-label">Failed Jobs</div>
        </div>
      </div>
    </div>

    <div class="quick-links">
      <h2 class="section-title">Quick Actions</h2>
      <div class="link-grid">
        <router-link :to="{ name: 'users.create' }" class="quick-link">
          <span>➕</span> Add New User
        </router-link>
        <router-link :to="{ name: 'users.index' }" class="quick-link">
          <span>📋</span> Manage Users
        </router-link>
        <router-link :to="{ name: 'failed-jobs.index' }" class="quick-link quick-link--warning">
          <span>🔍</span> View Failed Jobs
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import api from '@/api';

const loading = ref(true);
const stats   = reactive({ users: 0, failedJobs: 0 });

onMounted(async () => {
    try {
        const [usersRes, jobsRes] = await Promise.all([
            api.get('/users?per_page=1'),
            api.get('/failed-jobs?per_page=1'),
        ]);
        stats.users      = usersRes.data.meta?.total ?? 0;
        stats.failedJobs = jobsRes.data.meta?.total ?? 0;
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
    color: #0f172a;
    margin-bottom: 28px;
}

.loading { color: #64748b; font-size: 14px; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 36px;
}

.stat-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    font-size: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon--blue { background: #eff6ff; }
.stat-icon--red  { background: #fef2f2; }

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
}

.stat-label {
    font-size: 13px;
    color: #64748b;
    margin-top: 4px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 14px;
}

.link-grid {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.quick-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    transition: all 0.15s;
}

.quick-link:hover { background: #3b82f6; color: #fff; border-color: #3b82f6; }
.quick-link--warning:hover { background: #f59e0b; border-color: #f59e0b; color: #fff; }
</style>
