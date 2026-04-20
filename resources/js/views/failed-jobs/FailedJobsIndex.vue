<template>
  <div>
    <div class="page-header">
      <h1 class="page-title">Failed Jobs</h1>
      <span v-if="meta" class="total-badge">{{ meta.total }} total</span>
    </div>

    <div class="card">
      <div v-if="loading" class="table-state">Loading…</div>

      <div v-else-if="!jobs.length" class="table-state table-state--success">
        No failed jobs. Everything looks good.
      </div>

      <table v-else class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>UUID</th>
            <th>Connection</th>
            <th>Queue</th>
            <th>Failed At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="job in jobs" :key="job.id">
            <td class="col-id">{{ job.id }}</td>
            <td class="col-uuid">{{ truncate(job.uuid, 20) }}</td>
            <td>{{ job.connection }}</td>
            <td><span class="queue-badge">{{ job.queue }}</span></td>
            <td class="col-date">{{ formatDate(job.failed_at) }}</td>
            <td class="col-actions">
              <button class="btn-icon btn-icon--info" title="View exception" @click="viewJob(job)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
              <button class="btn-icon btn-icon--delete" title="Delete" @click="confirmDelete(job)">
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
        <button class="page-btn" :disabled="meta.current_page <= 1" @click="loadPage(meta.current_page - 1)">← Prev</button>
        <span class="page-info">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
        <button class="page-btn" :disabled="meta.current_page >= meta.last_page" @click="loadPage(meta.current_page + 1)">Next →</button>
      </div>
    </div>

    <!-- Exception viewer -->
    <div v-if="viewTarget" class="modal-overlay" @click.self="viewTarget = null">
      <div class="modal modal--wide">
        <div class="modal-header">
          <h3 class="modal-title">Job Exception</h3>
          <button class="modal-close" @click="viewTarget = null">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div class="exception-meta">
          <div><strong>UUID:</strong> {{ viewTarget.uuid }}</div>
          <div><strong>Queue:</strong> {{ viewTarget.queue }}</div>
          <div><strong>Failed At:</strong> {{ formatDate(viewTarget.failed_at) }}</div>
        </div>
        <pre class="exception-text">{{ viewTarget.exception }}</pre>
      </div>
    </div>

    <!-- Delete confirmation -->
    <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
      <div class="modal">
        <h3 class="modal-title">Delete Failed Job</h3>
        <p class="modal-body">
          Are you sure you want to delete job <strong>#{{ deleteTarget.id }}</strong>?
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

const jobs         = ref([]);
const meta         = ref(null);
const loading      = ref(true);
const viewTarget   = ref(null);
const deleteTarget = ref(null);
const deleting     = ref(false);

async function loadPage(page = 1) {
    loading.value = true;
    try {
        const { data } = await api.get(`/failed-jobs?page=${page}`);
        jobs.value = data.data;
        meta.value = data.meta;
    } finally {
        loading.value = false;
    }
}

function viewJob(job) { viewTarget.value = job; }
function confirmDelete(job) { deleteTarget.value = job; }

async function executeDelete() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    try {
        await api.delete(`/failed-jobs/${deleteTarget.value.id}`);
        jobs.value = jobs.value.filter(j => j.id !== deleteTarget.value.id);
        if (meta.value) meta.value.total -= 1;
        deleteTarget.value = null;
    } finally {
        deleting.value = false;
    }
}

function truncate(str, len) {
    if (!str) return '—';
    return str.length > len ? str.slice(0, len) + '…' : str;
}

function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleString('en-GB', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

onMounted(() => loadPage());
</script>

<style scoped>
.page-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}

.page-title { font-size: 24px; font-weight: 700; color: #0f172a; }

.total-badge {
    padding: 3px 10px;
    background: #fef3c7;
    color: #92400e;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    overflow: hidden;
}

.table-state { padding: 40px; text-align: center; color: #64748b; font-size: 14px; }
.table-state--success { color: #15803d; }

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
.col-uuid    { font-family: monospace; font-size: 12px; color: #64748b; }
.col-date    { color: #64748b; font-size: 13px; white-space: nowrap; }
.col-actions { width: 80px; white-space: nowrap; }

.queue-badge {
    display: inline-block;
    padding: 2px 10px;
    background: #f1f5f9;
    color: #475569;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
}

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
    color: #64748b;
}
.btn-icon svg { width: 14px; height: 14px; }
.btn-icon--info:hover   { background: #eff6ff; color: #3b82f6; }
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
    padding: 24px;
}

.modal {
    background: #fff;
    border-radius: 12px;
    padding: 28px;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.15);
}

.modal--wide { max-width: 760px; }

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.modal-title { font-size: 18px; font-weight: 700; color: #0f172a; }
.modal-body  { font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 24px; }
.modal-body strong { color: #0f172a; }

.modal-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background: none;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    color: #94a3b8;
}
.modal-close svg { width: 16px; height: 16px; }
.modal-close:hover { background: #f1f5f9; color: #374151; }

.exception-meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 13px;
    color: #374151;
    margin-bottom: 16px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
}

.exception-text {
    font-size: 12px;
    font-family: monospace;
    background: #0f172a;
    color: #e2e8f0;
    padding: 16px;
    border-radius: 8px;
    overflow-x: auto;
    max-height: 360px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-all;
}

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
