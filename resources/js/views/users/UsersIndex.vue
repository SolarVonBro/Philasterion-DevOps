<template>
  <div>
    <div class="page-header">
      <h1 class="page-title">Пользователи</h1>
      <router-link :to="{ name: 'users.create' }" class="btn-primary">+ Добавить</router-link>
    </div>

    <div class="card">
      <div v-if="loading" class="table-state">Загрузка…</div>

      <div v-else-if="!users.length" class="table-state">Пользователи не найдены.</div>

      <table v-else class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Имя</th>
            <th>Email</th>
            <th>Верификация</th>
            <th>Создан</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td class="col-id">{{ user.id }}</td>
            <td class="col-name">{{ user.name }}</td>
            <td class="col-email">{{ user.email }}</td>
            <td>
              <span :class="['badge', user.email_verified_at ? 'badge--green' : 'badge--gray']">
                {{ user.email_verified_at ? 'Подтверждён' : 'Не подтверждён' }}
              </span>
            </td>
            <td class="col-date">{{ formatDate(user.created_at) }}</td>
            <td class="col-actions">
              <router-link :to="{ name: 'users.edit', params: { id: user.id } }" class="btn-icon btn-icon--edit" title="Редактировать">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </router-link>
              <button class="btn-icon btn-icon--delete" title="Удалить" @click="confirmDelete(user)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                  <path d="M10 11v6"/><path d="M14 11v6"/>
                  <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="meta && meta.last_page > 1" class="pagination">
        <button class="page-btn" :disabled="meta.current_page <= 1" @click="loadPage(meta.current_page - 1)">← Назад</button>
        <span class="page-info">Страница {{ meta.current_page }} из {{ meta.last_page }}</span>
        <button class="page-btn" :disabled="meta.current_page >= meta.last_page" @click="loadPage(meta.current_page + 1)">Вперёд →</button>
      </div>
    </div>

    <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
      <div class="modal">
        <h3 class="modal-title">Удалить пользователя</h3>
        <p class="modal-body">
          Удалить пользователя <strong>{{ deleteTarget.name }}</strong>? Это действие нельзя отменить.
        </p>
        <div class="modal-actions">
          <button class="btn-secondary" @click="deleteTarget = null">Отмена</button>
          <button class="btn-danger" :disabled="deleting" @click="executeDelete">
            {{ deleting ? 'Удаление…' : 'Удалить' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api';

const users        = ref([]);
const meta         = ref(null);
const loading      = ref(true);
const deleteTarget = ref(null);
const deleting     = ref(false);

async function loadPage(page = 1) {
    loading.value = true;
    try {
        const { data } = await api.get(`/users?page=${page}`);
        users.value = data.data;
        meta.value  = data.meta;
    } finally {
        loading.value = false;
    }
}

function confirmDelete(user) {
    deleteTarget.value = user;
}

async function executeDelete() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    try {
        await api.delete(`/users/${deleteTarget.value.id}`);
        users.value = users.value.filter(u => u.id !== deleteTarget.value.id);
        deleteTarget.value = null;
    } finally {
        deleting.value = false;
    }
}

function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('ru-RU', {
        day: '2-digit', month: 'short', year: 'numeric',
    });
}

onMounted(() => loadPage());
</script>

<style scoped>
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.page-title { font-size: 24px; font-weight: 700; color: #0f172a; }

.btn-primary {
    padding: 9px 18px;
    background: #3b82f6;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
    text-decoration: none;
}
.btn-primary:hover { background: #2563eb; }

.card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    overflow: hidden;
}

.table-state { padding: 40px; text-align: center; color: #64748b; font-size: 14px; }

.table { width: 100%; border-collapse: collapse; }
.table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.table td {
    padding: 12px 16px;
    font-size: 14px;
    color: #374151;
    border-bottom: 1px solid #f1f5f9;
}
.table tr:last-child td { border-bottom: none; }
.table tr:hover td { background: #f8fafc; }

.col-id      { color: #94a3b8; font-size: 13px; width: 50px; }
.col-date    { color: #64748b; font-size: 13px; }
.col-actions { width: 80px; white-space: nowrap; }

.badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
}
.badge--green { background: #dcfce7; color: #15803d; }
.badge--gray  { background: #f1f5f9; color: #64748b; }

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 6px;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: background 0.15s;
    text-decoration: none;
    color: #64748b;
}
.btn-icon svg { width: 14px; height: 14px; }
.btn-icon--edit:hover   { background: #eff6ff; color: #3b82f6; }
.btn-icon--delete:hover { background: #fef2f2; color: #ef4444; }

.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 16px;
    border-top: 1px solid #f1f5f9;
}

.page-btn {
    padding: 7px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 7px;
    background: #fff;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
}
.page-btn:hover:not(:disabled) { border-color: #3b82f6; color: #3b82f6; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.page-info { font-size: 13px; color: #64748b; }

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.modal {
    background: #fff;
    border-radius: 12px;
    padding: 28px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.15);
}

.modal-title { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 12px; }
.modal-body  { font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 24px; }
.modal-body strong { color: #0f172a; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; }

.btn-secondary {
    padding: 9px 18px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-secondary:hover { background: #f8fafc; }

.btn-danger {
    padding: 9px 18px;
    background: #ef4444;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-danger:hover:not(:disabled) { background: #dc2626; }
.btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
