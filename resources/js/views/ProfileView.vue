<template>
  <div>
    <h1 class="page-title">Мой профиль</h1>
    <p class="page-sub">{{ auth.user?.name }} &middot; {{ auth.user?.email }}</p>

    <!-- ─── Weather ─────────────────────────────────────────── -->
    <section class="section">
      <h2 class="section-title">
        <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/>
          <line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/>
          <line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
        </svg>
        Погода
      </h2>

      <div v-if="weather.error" class="weather-location-form">
        <p class="hint">Не удалось определить местоположение. Введите название города:</p>
        <div class="location-row">
          <input v-model="cityInput" type="text" placeholder="Москва" class="field-input" @keyup.enter="searchCity" />
          <button class="btn-primary" :disabled="weather.searching" @click="searchCity">
            {{ weather.searching ? 'Поиск…' : 'Найти' }}
          </button>
        </div>
        <p v-if="weather.cityError" class="field-error">{{ weather.cityError }}</p>
      </div>

      <div v-if="weather.loading" class="state-placeholder">Загрузка данных о погоде…</div>

      <div v-else-if="weather.current" class="weather-wrap">
        <div class="weather-current">
          <div class="weather-main">
            <div class="weather-temp">{{ Math.round(weather.current.temperature_2m) }}<span class="temp-unit">°C</span></div>
            <div class="weather-desc">{{ wmoLabel(weather.current.weathercode) }}</div>
            <div class="weather-location-name">{{ weather.locationName }}</div>
          </div>
          <div class="weather-details">
            <div class="weather-detail">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2a7 7 0 0 1 7 7c0 5-7 13-7 13S5 14 5 9a7 7 0 0 1 7-7z"/>
                <circle cx="12" cy="9" r="2.5"/>
              </svg>
              <span>Влажность {{ weather.current.relative_humidity_2m }}%</span>
            </div>
            <div class="weather-detail">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/>
              </svg>
              <span>Ветер {{ weather.current.wind_speed_10m }} км/ч</span>
            </div>
          </div>
        </div>

        <div class="forecast-row">
          <div v-for="(day, i) in weather.forecast" :key="i" class="forecast-card">
            <div class="forecast-day">{{ forecastDay(i) }}</div>
            <div class="forecast-icon">{{ wmoIcon(day.weathercode) }}</div>
            <div class="forecast-max">{{ Math.round(day.temperature_2m_max) }}°</div>
            <div class="forecast-min">{{ Math.round(day.temperature_2m_min) }}°</div>
            <div class="forecast-precip">{{ day.precipitation_sum }} mm</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── Diary ─────────────────────────────────────────────── -->
    <section class="section">
      <div class="section-header">
        <h2 class="section-title">
          <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          Дневник самочувствия
        </h2>
        <button v-if="!diary.showForm" class="btn-primary btn-sm" @click="openCreate">+ Новая запись</button>
      </div>

      <!-- Entry form -->
      <div v-if="diary.showForm" class="diary-form-card">
        <h3 class="form-title">{{ diary.editId ? 'Редактировать запись' : 'Новая запись' }}</h3>
        <div class="form-grid">
          <div class="field">
            <label>Дата</label>
            <input v-model="diary.form.recorded_at" type="date" class="field-input" />
            <span v-if="diary.errors.recorded_at" class="field-error">{{ diary.errors.recorded_at }}</span>
          </div>

          <div class="field">
            <label>Настроение <span class="field-range">1–5</span></label>
            <div class="scale-row">
              <button
                v-for="n in 5" :key="n"
                :class="['scale-btn', { 'scale-btn--active': diary.form.mood === n, [`scale-btn--mood-${n}`]: diary.form.mood === n }]"
                type="button"
                @click="diary.form.mood = n"
              >{{ n }}</button>
            </div>
            <span v-if="diary.errors.mood" class="field-error">{{ diary.errors.mood }}</span>
          </div>

          <div class="field">
            <label>Энергия <span class="field-range">1–10</span></label>
            <div class="scale-row">
              <button
                v-for="n in 10" :key="n"
                :class="['scale-btn', 'scale-btn--sm', { 'scale-btn--active scale-btn--energy': diary.form.energy === n }]"
                type="button"
                @click="diary.form.energy = n"
              >{{ n }}</button>
            </div>
            <span v-if="diary.errors.energy" class="field-error">{{ diary.errors.energy }}</span>
          </div>

          <div class="field">
            <label>Сон (часы)</label>
            <input v-model.number="diary.form.sleep_hours" type="number" min="0" max="24" step="0.5" placeholder="7.5" class="field-input field-input--short" />
            <span v-if="diary.errors.sleep_hours" class="field-error">{{ diary.errors.sleep_hours }}</span>
          </div>

          <div class="field field--full">
            <label>Заметки</label>
            <textarea v-model="diary.form.notes" rows="3" placeholder="Как вы себя чувствуете сегодня?" class="field-input field-textarea" />
          </div>
        </div>

        <div v-if="diary.generalError" class="alert-error">{{ diary.generalError }}</div>

        <div class="form-actions">
          <button class="btn-ghost" @click="closeForm">Отмена</button>
          <button class="btn-primary" :disabled="diary.saving" @click="saveEntry">
            {{ diary.saving ? 'Сохранение…' : (diary.editId ? 'Сохранить' : 'Добавить запись') }}
          </button>
        </div>
      </div>

      <!-- Entry list -->
      <div v-if="diary.loading" class="state-placeholder">Загрузка записей…</div>

      <div v-else-if="!diary.entries.length && !diary.showForm" class="state-empty">
        Записей пока нет. Начните отслеживать своё самочувствие сегодня.
      </div>

      <div v-else class="diary-list">
        <div v-for="entry in diary.entries" :key="entry.id" class="diary-card">
          <div class="diary-card-header">
            <span class="diary-date">{{ formatDate(entry.recorded_at) }}</span>
            <div class="diary-actions">
              <button class="btn-icon btn-icon--edit" title="Редактировать" @click="openEdit(entry)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </button>
              <button class="btn-icon btn-icon--delete" title="Удалить" @click="confirmDeleteEntry(entry)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                  <path d="M10 11v6"/><path d="M14 11v6"/>
                  <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="diary-metrics">
            <div class="metric">
              <span class="metric-label">Настроение</span>
              <div class="metric-dots">
                <span v-for="n in 5" :key="n" :class="['dot', n <= entry.mood ? 'dot--mood' : '']" />
              </div>
              <span class="metric-value">{{ entry.mood }}/5</span>
            </div>
            <div class="metric">
              <span class="metric-label">Энергия</span>
              <div class="metric-dots metric-dots--10">
                <span v-for="n in 10" :key="n" :class="['dot', 'dot--sm', n <= entry.energy ? 'dot--energy' : '']" />
              </div>
              <span class="metric-value">{{ entry.energy }}/10</span>
            </div>
            <div v-if="entry.sleep_hours != null" class="metric">
              <span class="metric-label">Сон</span>
              <span class="metric-value">{{ entry.sleep_hours }} ч</span>
            </div>
          </div>

          <p v-if="entry.notes" class="diary-notes">{{ entry.notes }}</p>
        </div>
      </div>

      <div v-if="diary.meta && diary.meta.last_page > 1" class="pagination">
        <button class="page-btn" :disabled="diary.meta.current_page <= 1" @click="loadDiary(diary.meta.current_page - 1)">← Назад</button>
        <span class="page-info">Страница {{ diary.meta.current_page }} из {{ diary.meta.last_page }}</span>
        <button class="page-btn" :disabled="diary.meta.current_page >= diary.meta.last_page" @click="loadDiary(diary.meta.current_page + 1)">Вперёд →</button>
      </div>
    </section>

    <!-- Delete confirm modal -->
    <div v-if="diary.deleteTarget" class="modal-overlay" @click.self="diary.deleteTarget = null">
      <div class="modal">
        <h3 class="modal-title">Удалить запись</h3>
        <p class="modal-body">Удалить запись от <strong>{{ formatDate(diary.deleteTarget.recorded_at) }}</strong>? Это действие нельзя отменить.</p>
        <div class="modal-actions">
          <button class="btn-ghost" @click="diary.deleteTarget = null">Отмена</button>
          <button class="btn-danger" :disabled="diary.deleting" @click="deleteEntry">
            {{ diary.deleting ? 'Удаление…' : 'Удалить' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import api from '@/api';

const auth = useAuthStore();

// ─── Weather ───────────────────────────────────────────────────────────────

const cityInput = ref('');

const weather = reactive({
    loading: false,
    error: false,
    searching: false,
    cityError: '',
    locationName: '',
    current: null,
    forecast: [],
});

const WMO_LABELS = {
    0: 'Ясно', 1: 'Преимущественно ясно', 2: 'Переменная облачность', 3: 'Пасмурно',
    45: 'Туман', 48: 'Изморозь', 51: 'Слабая морось', 53: 'Морось',
    55: 'Сильная морось', 61: 'Слабый дождь', 63: 'Дождь', 65: 'Сильный дождь',
    71: 'Слабый снег', 73: 'Снег', 75: 'Сильный снег', 80: 'Ливень',
    81: 'Ливневый дождь', 82: 'Сильный ливень', 95: 'Гроза',
    96: 'Гроза с градом', 99: 'Сильная гроза',
};

const WMO_ICONS = {
    0: '☀', 1: '🌤', 2: '⛅', 3: '☁',
    45: '🌫', 48: '🌫',
    51: '🌦', 53: '🌦', 55: '🌧',
    61: '🌧', 63: '🌧', 65: '🌧',
    71: '🌨', 73: '🌨', 75: '🌨',
    80: '🌦', 81: '🌧', 82: '⛈',
    95: '⛈', 96: '⛈', 99: '⛈',
};

function wmoLabel(code) { return WMO_LABELS[code] ?? 'Неизвестно'; }
function wmoIcon(code)  { return WMO_ICONS[code]  ?? '—'; }

function forecastDay(offset) {
    const d = new Date();
    d.setDate(d.getDate() + offset);
    return offset === 0 ? 'Сегодня' : d.toLocaleDateString('ru-RU', { weekday: 'short' });
}

async function fetchWeather(lat, lon, name = '') {
    weather.loading = true;
    try {
        const url = `https://api.open-meteo.com/v1/forecast`
            + `?latitude=${lat}&longitude=${lon}`
            + `&current=temperature_2m,relative_humidity_2m,wind_speed_10m,weathercode`
            + `&daily=temperature_2m_max,temperature_2m_min,weathercode,precipitation_sum`
            + `&timezone=auto&forecast_days=7`;
        const res  = await fetch(url);
        const data = await res.json();
        weather.current      = data.current;
        weather.locationName = name;
        weather.forecast     = data.daily.time.map((_, i) => ({
            weathercode:       data.daily.weathercode[i],
            temperature_2m_max: data.daily.temperature_2m_max[i],
            temperature_2m_min: data.daily.temperature_2m_min[i],
            precipitation_sum:  data.daily.precipitation_sum[i],
        }));
    } finally {
        weather.loading = false;
    }
}

async function searchCity() {
    if (!cityInput.value.trim()) return;
    weather.searching  = true;
    weather.cityError  = '';
    try {
        const res  = await fetch(`https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(cityInput.value)}&count=1`);
        const data = await res.json();
        if (!data.results?.length) {
            weather.cityError = 'Город не найден. Попробуйте другое название.';
            return;
        }
        const { latitude, longitude, name, country } = data.results[0];
        weather.error = false;
        await fetchWeather(latitude, longitude, `${name}, ${country}`);
    } finally {
        weather.searching = false;
    }
}

const SPB = { lat: 59.9343, lon: 30.3351, name: 'Санкт-Петербург' };

function initWeather() {
    if (!navigator.geolocation) {
        fetchWeather(SPB.lat, SPB.lon, SPB.name);
        return;
    }
    weather.loading = true;
    navigator.geolocation.getCurrentPosition(
        (pos) => fetchWeather(pos.coords.latitude, pos.coords.longitude),
        ()    => fetchWeather(SPB.lat, SPB.lon, SPB.name),
        { timeout: 8000 },
    );
}

// ─── Diary ─────────────────────────────────────────────────────────────────

const diary = reactive({
    entries: [],
    meta: null,
    loading: true,
    showForm: false,
    editId: null,
    saving: false,
    deleting: false,
    deleteTarget: null,
    generalError: '',
    errors: {},
    form: { recorded_at: today(), mood: null, energy: null, sleep_hours: '', notes: '' },
});

function today() {
    return new Date().toISOString().slice(0, 10);
}

async function loadDiary(page = 1) {
    diary.loading = true;
    try {
        const { data } = await api.get(`/diary?page=${page}`);
        diary.entries = data.data;
        diary.meta    = data.meta;
    } finally {
        diary.loading = false;
    }
}

function openCreate() {
    diary.editId   = null;
    diary.errors   = {};
    diary.generalError = '';
    diary.form     = { recorded_at: today(), mood: null, energy: null, sleep_hours: '', notes: '' };
    diary.showForm = true;
}

function openEdit(entry) {
    diary.editId   = entry.id;
    diary.errors   = {};
    diary.generalError = '';
    diary.form     = {
        recorded_at: entry.recorded_at,
        mood:        entry.mood,
        energy:      entry.energy,
        sleep_hours: entry.sleep_hours ?? '',
        notes:       entry.notes ?? '',
    };
    diary.showForm = true;
}

function closeForm() {
    diary.showForm = false;
    diary.editId   = null;
}

async function saveEntry() {
    diary.errors       = {};
    diary.generalError = '';
    diary.saving       = true;

    const payload = {
        recorded_at: diary.form.recorded_at,
        mood:        diary.form.mood,
        energy:      diary.form.energy,
        notes:       diary.form.notes || null,
    };
    if (diary.form.sleep_hours !== '') payload.sleep_hours = diary.form.sleep_hours;

    try {
        if (diary.editId) {
            const { data } = await api.put(`/diary/${diary.editId}`, payload);
            const idx = diary.entries.findIndex(e => e.id === diary.editId);
            if (idx !== -1) diary.entries[idx] = data.data;
        } else {
            const { data } = await api.post('/diary', payload);
            diary.entries.unshift(data.data);
            if (diary.meta) diary.meta.total += 1;
        }
        closeForm();
    } catch (err) {
        if (err.response?.status === 422) {
            const errs = err.response.data.errors ?? {};
            Object.assign(diary.errors, Object.fromEntries(
                Object.entries(errs).map(([k, v]) => [k, v[0]])
            ));
        } else {
            diary.generalError = err.response?.data?.message ?? 'Произошла ошибка.';
        }
    } finally {
        diary.saving = false;
    }
}

function confirmDeleteEntry(entry) { diary.deleteTarget = entry; }

async function deleteEntry() {
    if (!diary.deleteTarget) return;
    diary.deleting = true;
    try {
        await api.delete(`/diary/${diary.deleteTarget.id}`);
        diary.entries    = diary.entries.filter(e => e.id !== diary.deleteTarget.id);
        if (diary.meta) diary.meta.total -= 1;
        diary.deleteTarget = null;
    } finally {
        diary.deleting = false;
    }
}

function formatDate(val) {
    if (!val) return '—';
    return new Date(val + 'T00:00:00').toLocaleDateString('ru-RU', {
        day: '2-digit', month: 'long', year: 'numeric',
    });
}

onMounted(() => {
    initWeather();
    loadDiary();
});
</script>

<style scoped>
.page-title { font-size: 24px; font-weight: 700; color: var(--color-text-primary); margin-bottom: 4px; }
.page-sub   { font-size: 14px; color: var(--color-text-secondary); margin-bottom: 32px; }

.section { margin-bottom: 40px; }

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 17px;
  font-weight: 700;
  color: var(--color-text-primary);
  margin-bottom: 16px;
}

.section-header .section-title { margin-bottom: 0; }

.section-icon { width: 18px; height: 18px; color: var(--color-primary); flex-shrink: 0; }

/* ── Weather ── */
.state-placeholder { color: var(--color-text-secondary); font-size: 14px; padding: 16px 0; }

.weather-location-form { margin-bottom: 20px; }
.hint { font-size: 13px; color: var(--color-text-secondary); margin-bottom: 10px; }
.location-row { display: flex; gap: 10px; }

.weather-wrap { display: flex; flex-direction: column; gap: 20px; }

.weather-current {
  background: linear-gradient(135deg, var(--color-sidebar-bg) 0%, #3D2860 50%, var(--color-primary) 100%);
  border-radius: var(--radius-xl);
  padding: 28px 32px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.weather-temp {
  font-size: 64px;
  font-weight: 700;
  line-height: 1;
}

.temp-unit { font-size: 28px; font-weight: 400; opacity: 0.8; }

.weather-desc {
  font-size: 18px;
  font-weight: 500;
  margin-top: 6px;
  opacity: 0.9;
}

.weather-location-name {
  font-size: 13px;
  opacity: 0.7;
  margin-top: 4px;
}

.weather-details { display: flex; flex-direction: column; gap: 12px; align-items: flex-end; }

.weather-detail {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  opacity: 0.9;
}

.weather-detail svg { width: 16px; height: 16px; opacity: 0.8; }

.forecast-row {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
}

.forecast-card {
  background: var(--color-card);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
  padding: 14px 8px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.forecast-day   { font-size: 11px; font-weight: 600; color: var(--color-text-secondary); text-transform: uppercase; letter-spacing: 0.04em; }
.forecast-icon  { font-size: 22px; line-height: 1; margin: 2px 0; }
.forecast-max   { font-size: 15px; font-weight: 700; color: var(--color-text-primary); }
.forecast-min   { font-size: 13px; color: var(--color-text-muted); }
.forecast-precip { font-size: 11px; color: var(--color-primary); }

/* ── Diary form ── */
.diary-form-card {
  background: var(--color-card);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: 24px;
  margin-bottom: 20px;
  border: 1.5px solid var(--color-primary-light);
}

.form-title { font-size: 15px; font-weight: 700; color: var(--color-text-primary); margin-bottom: 18px; }

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.field { display: flex; flex-direction: column; gap: 6px; }
.field--full { grid-column: 1 / -1; }
.field label { font-size: 12.5px; font-weight: 600; color: var(--color-text-secondary); }
.field-range { font-weight: 400; color: var(--color-text-muted); margin-left: 4px; }

.field-input {
  padding: 9px 12px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: 14px;
  outline: none;
  transition: border-color var(--transition);
  background: #fff;
  color: var(--color-text-primary);
}

.field-input:focus { border-color: var(--color-primary); }
.field-input--short { max-width: 120px; }
.field-textarea { resize: vertical; min-height: 72px; }
.field-error { font-size: 12px; color: var(--color-danger); }

.scale-row { display: flex; gap: 6px; }

.scale-btn {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  border: 1.5px solid var(--color-border);
  background: #fff;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  color: var(--color-text-secondary);
  transition: all var(--transition);
}
.scale-btn--sm { width: 30px; height: 30px; font-size: 12px; }
.scale-btn:hover { border-color: var(--color-primary); color: var(--color-primary); }
.scale-btn--active { background: var(--color-primary); border-color: var(--color-primary); color: #fff; }
.scale-btn--energy { background: var(--color-accent) !important; border-color: var(--color-accent) !important; }

.alert-error {
  padding: 10px 14px;
  background: var(--color-danger-light);
  border: 1px solid #fecaca;
  border-radius: var(--radius-md);
  color: #b91c1c;
  font-size: 13px;
  margin-top: 8px;
}

.form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }

/* ── Diary list ── */
.state-empty { color: var(--color-text-secondary); font-size: 14px; padding: 32px; text-align: center; background: var(--color-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }

.diary-list { display: flex; flex-direction: column; gap: 12px; }

.diary-card {
  background: var(--color-card);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: 20px 24px;
  border-left: 4px solid var(--color-primary);
}

.diary-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

.diary-date { font-size: 14px; font-weight: 700; color: var(--color-text-primary); }

.diary-actions { display: flex; gap: 4px; }

.diary-metrics { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 10px; }

.metric { display: flex; align-items: center; gap: 8px; }
.metric-label { font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.04em; min-width: 50px; }
.metric-value { font-size: 13px; font-weight: 600; color: var(--color-text-secondary); }

.metric-dots { display: flex; gap: 4px; align-items: center; }
.metric-dots--10 { gap: 3px; }

.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: var(--color-border);
  flex-shrink: 0;
}
.dot--sm { width: 8px; height: 8px; }
.dot--mood   { background: var(--color-primary); }
.dot--energy { background: var(--color-accent); }

.diary-notes { font-size: 13.5px; color: var(--color-text-secondary); line-height: 1.6; border-top: 1px solid var(--color-border-light); padding-top: 10px; margin-top: 4px; }

/* ── Buttons ── */
.btn-primary {
  padding: 9px 18px;
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
.btn-sm { padding: 7px 14px; font-size: 13px; }

.btn-ghost {
  padding: 9px 18px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  background: #fff;
  font-size: 14px;
  font-weight: 500;
  color: var(--color-text-secondary);
  cursor: pointer;
  transition: all var(--transition);
}
.btn-ghost:hover { background: var(--color-border-light); }

.btn-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  border-radius: var(--radius-sm);
  border: none;
  background: transparent;
  cursor: pointer;
  transition: background var(--transition);
  color: var(--color-text-muted);
}
.btn-icon svg { width: 14px; height: 14px; }
.btn-icon--edit:hover   { background: var(--color-primary-light); color: var(--color-primary); }
.btn-icon--delete:hover { background: var(--color-danger-light);  color: var(--color-danger); }

/* ── Pagination ── */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 20px 0 0;
}

.page-btn {
  padding: 7px 14px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-sm);
  background: var(--color-card);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition);
}
.page-btn:hover:not(:disabled) { border-color: var(--color-primary); color: var(--color-primary); }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.page-info { font-size: 13px; color: var(--color-text-secondary); }

/* ── Modal ── */
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
  background: var(--color-card);
  border-radius: var(--radius-lg);
  padding: 28px;
  width: 100%;
  max-width: 420px;
  box-shadow: var(--shadow-lg);
}

.modal-title { font-size: 18px; font-weight: 700; color: var(--color-text-primary); margin-bottom: 10px; }
.modal-body  { font-size: 14px; color: var(--color-text-secondary); line-height: 1.6; margin-bottom: 24px; }
.modal-body strong { color: var(--color-text-primary); }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; }

.btn-danger {
  padding: 9px 18px;
  background: var(--color-danger);
  color: #fff;
  border: none;
  border-radius: var(--radius-md);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background var(--transition);
}
.btn-danger:hover:not(:disabled) { background: #dc2626; }
.btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
