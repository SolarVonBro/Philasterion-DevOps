import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL     = __ENV.BASE_URL     || 'http://127.0.0.1:30080';
const FRONTEND_URL = __ENV.FRONTEND_URL || '';

export const options = {
  stages: [
    { duration: '1m30s', target: 6 },  // плавный рост — HPA замечает нагрузку
    { duration: '3m30s', target: 6 },  // держим — HPA окно 90с + старт пода ~60с
    { duration: '30s',   target: 0 },  // сброс
  ],
  thresholds: {
    http_req_duration: ['p(95)<3000'],
  },
};

export function setup() {
  // Retry login до 15 раз — контейнер стартует ~60с, 15×5=75с покрывает это с запасом
  for (let i = 0; i < 15; i++) {
    const res = http.post(
      `${BASE_URL}/api/auth/login`,
      JSON.stringify({
        email:    __ENV.LOGIN_EMAIL    || 'admin@test.com',
        password: __ENV.LOGIN_PASSWORD || '000333him',
      }),
      { headers: { 'Content-Type': 'application/json' } },
    );
    const token = res.json('token');
    if (res.status === 200 && token) {
      return { token };
    }
    console.log(`Login attempt ${i + 1} failed (status ${res.status}), retrying in 5s...`);
    sleep(5);
  }
  throw new Error('Could not authenticate after 15 attempts');
}

export default function (data) {
  const apiHeaders = {
    'Accept':        'application/json',
    'Content-Type':  'application/json',
    'Authorization': `Bearer ${data.token}`,
  };

  // Распределение ролей по 6 VU:
  //   0        → frontend (без MySQL)
  //   1, 2, 3  → diary list (лёгкий SELECT)
  //   4        → auth/me   (SELECT по PK, самый дешёвый)
  //   5        → create, но только каждую 4-ю итерацию — иначе list
  const role = __VU % 6;

  if (role === 0 && FRONTEND_URL) {
    const res = http.get(`${FRONTEND_URL}/`, { headers: { 'Accept': 'text/html' } });
    check(res, { 'frontend 200': (r) => r.status === 200 });
    sleep(2);
  } else if (role === 5 && __ITER % 4 === 0) {
    // INSERT — не чаще 1 раза на 4 итерации (~1 запись каждые 16-20 сек на VU)
    const res = http.post(
      `${BASE_URL}/api/diary`,
      JSON.stringify({
        recorded_at: new Date().toISOString().slice(0, 10),
        mood:        (__VU % 5) + 1,
        energy:      (__ITER % 10) + 1,
        sleep_hours: 7,
        notes:       `k6 vu=${__VU} iter=${__ITER}`,
      }),
      { headers: apiHeaders },
    );
    check(res, { 'diary create 2xx': (r) => r.status >= 200 && r.status < 300 });
    sleep(4);
  } else if (role === 4) {
    const res = http.get(`${BASE_URL}/api/auth/me`, { headers: apiHeaders });
    check(res, { 'me 200': (r) => r.status === 200 });
    sleep(2);
  } else {
    // role 1,2,3 + role 0 без FRONTEND_URL + role 5 на "пустых" итерациях
    const res = http.get(`${BASE_URL}/api/diary`, { headers: apiHeaders });
    check(res, { 'diary list 200': (r) => r.status === 200 });
    sleep(2);
  }
}
