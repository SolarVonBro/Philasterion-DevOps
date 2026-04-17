<template>
  <div>
    <div class="page-header">
      <h1 class="page-title">Users</h1>
      <router-link :to="{ name: 'users.create' }" class="btn-primary">+ Add User</router-link>
    </div>

    <div class="card">
      <div v-if="loading" class="table-state">Loading…</div>

      <div v-else-if="!users.length" class="table-state">No users found.</div>

      <table v-else class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Verified</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td class="col-id">{{ user.id }}</td>
            <td class="col-name">{{ user.name }}</td>
            <td class="col-email">{{ user.email }}</td>
            <td>
              <span :class="['badge', user.email_verified_at ? 'badge--green' : 'badge--gray']">
                {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
              </span>
            </td>
            <td class="col-date">{{ formatDate(user.created_at) }}</td>
            <td class="col-actions">
              <router-link :to="{ name: 'users.edit', params: { id: user.id } }" class="btn-icon btn-icon--edit" title="Edit">✏️</router-link>
              <button class="btn-icon btn-icon--delete" title="Delete" @click="confirmDelete(user)">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="meta && meta.last_page > 1" class="pagination">
        <button class="page-btn" :disabled="meta.current_page <= 1" @click="loadPage(meta.current_page - 1)">← Prev</button>
        <span class="page-info">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
        <button class="page-btn" :disabled="meta.current_page >= meta.last_page" @click="loadPage(meta.current_page + 1)">Next →</button>
      </div>
    </div>

    <!-- Delete confirmation modal -->
    <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
      <div class="modal">
        <h3 class="modal-title">Delete User</h3>
        <p class="modal-body">
          Are you sure you want to delete <strong>{{ deleteTarget.name }}</strong>? This action cannot be undone.
        </p>
        <div class="modal-actions">
          <button class="btn-secondary" @click="deleteTarget = null">Cancel</button>
          <button class="btn-danger" :disabled="deleting" @click="executeDelete">
            {{ deleting ? 'Deleting…' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api';

const users       = ref([]);
const meta        = ref(null);
const loading     = ref(true);
const deleteTarget = ref(null);
const deleting    = ref(false);

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
    return new Date(val).toLocaleDateString('en-GB', {
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

.col-id    { color: #94a3b8; font-size: 13px; width: 50px; }
.col-date  { color: #64748b; font-size: 13px; }
.col-actions { width: 90px; white-space: nowrap; }

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
    font-size: 15px;
    transition: background 0.15s;
    text-decoration: none;
}
.btn-icon--edit:hover   { background: #eff6ff; }
.btn-icon--delete:hover { background: #fef2f2; }

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

/* Modal */
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
