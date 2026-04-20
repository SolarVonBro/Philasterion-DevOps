<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">{{ isEdit ? 'Редактировать пользователя' : 'Создать пользователя' }}</h1>
        <p class="page-sub">{{ isEdit ? `Редактирование пользователя #${route.params.id}` : 'Добавить нового пользователя в систему.' }}</p>
      </div>
      <router-link :to="{ name: 'users.index' }" class="btn-back">← Назад к списку</router-link>
    </div>

    <div class="card">
      <div v-if="fetchLoading" class="form-state">Загрузка…</div>

      <form v-else @submit.prevent="handleSubmit" class="form">
        <div class="field">
          <label for="name">Полное имя <span class="required">*</span></label>
          <input id="name" v-model="form.name" type="text" placeholder="Иван Иванов" required />
          <span v-if="errors.name" class="field-error">{{ errors.name }}</span>
        </div>

        <div class="field">
          <label for="email">Email адрес <span class="required">*</span></label>
          <input id="email" v-model="form.email" type="email" placeholder="ivan@example.com" required />
          <span v-if="errors.email" class="field-error">{{ errors.email }}</span>
        </div>

        <div class="field">
          <label for="password">
            Пароль
            <span v-if="!isEdit" class="required">*</span>
            <span v-else class="hint">(оставьте пустым, чтобы не менять)</span>
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="Мин. 8 символов"
            :required="!isEdit"
          />
          <span v-if="errors.password" class="field-error">{{ errors.password }}</span>
        </div>

        <div v-if="generalError" class="alert-error">{{ generalError }}</div>
        <div v-if="successMessage" class="alert-success">{{ successMessage }}</div>

        <div class="form-actions">
          <router-link :to="{ name: 'users.index' }" class="btn-secondary">Отмена</router-link>
          <button type="submit" class="btn-primary" :disabled="saving">
            {{ saving ? 'Сохранение…' : (isEdit ? 'Сохранить' : 'Создать пользователя') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/api';

const route  = useRoute();
const router = useRouter();

const isEdit = computed(() => !!route.params.id);

const form = reactive({ name: '', email: '', password: '' });
const errors         = reactive({});
const generalError   = ref('');
const successMessage = ref('');
const saving         = ref(false);
const fetchLoading   = ref(false);

onMounted(async () => {
    if (!isEdit.value) return;
    fetchLoading.value = true;
    try {
        const { data } = await api.get(`/users/${route.params.id}`);
        form.name  = data.data.name;
        form.email = data.data.email;
    } catch {
        generalError.value = 'Не удалось загрузить пользователя.';
    } finally {
        fetchLoading.value = false;
    }
});

async function handleSubmit() {
    Object.keys(errors).forEach(k => delete errors[k]);
    generalError.value   = '';
    successMessage.value = '';
    saving.value = true;

    try {
        const payload = { name: form.name, email: form.email };
        if (form.password) payload.password = form.password;

        if (isEdit.value) {
            await api.put(`/users/${route.params.id}`, payload);
            successMessage.value = 'Пользователь успешно обновлён.';
        } else {
            await api.post('/users', { ...payload, password: form.password });
            router.push({ name: 'users.index' });
        }
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
        saving.value = false;
    }
}
</script>

<style scoped>
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 24px;
    gap: 16px;
}

.page-title { font-size: 24px; font-weight: 700; color: #0f172a; }
.page-sub   { font-size: 14px; color: #64748b; margin-top: 4px; }

.btn-back {
    padding: 8px 16px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    white-space: nowrap;
    transition: all 0.15s;
}
.btn-back:hover { border-color: #94a3b8; }

.card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    padding: 32px;
    max-width: 560px;
}

.form-state { color: #64748b; font-size: 14px; padding: 20px 0; }

.form { display: flex; flex-direction: column; gap: 20px; }

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

.required { color: #ef4444; margin-left: 2px; }
.hint { font-weight: 400; color: #94a3b8; font-size: 12px; margin-left: 4px; }

.alert-error {
    padding: 10px 14px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    color: #b91c1c;
    font-size: 13px;
}

.alert-success {
    padding: 10px 14px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    color: #15803d;
    font-size: 13px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 8px;
}

.btn-primary {
    padding: 10px 22px;
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

.btn-secondary {
    padding: 10px 22px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all 0.15s;
}
.btn-secondary:hover { background: #f8fafc; }
</style>
