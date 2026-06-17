import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL     = __ENV.BASE_URL     || 'http://127.0.0.1:30080';
const FRONTEND_URL = __ENV.FRONTEND_URL || '';

export const options = {
  stages: [
    // Медленный рамп — даём HPA поднять все поды
    { duration: '3m', target: 18 },
    // Устойчивая нагрузка, все поды уже запущены
    { duration: '2m', target: 18 },
    { duration: '30s',  target: 0  },
  ],
  noConnectionReuse: true,
  thresholds: {
    http_req_duration: ['p(95)<3000'],
  },
};

export function setup() {
  // Retry login до 10 раз — бэкенд может ещё стартовать когда начинается setup
  for (let i = 0; i < 10; i++) {
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
  throw new Error('Could not authenticate after 10 attempts');
}

export default function (data) {
  const apiHeaders = {
    'Accept':        'application/json',
    'Content-Type':  'application/json',
    'Authorization': `Bearer ${data.token}`,
  };

  // VU роли: 0=frontend, 1=list, 2=create, 3=profile
  const role = __VU % 4;

  if (role === 0 && FRONTEND_URL) {
    const res = http.get(`${FRONTEND_URL}/`, { headers: { 'Accept': 'text/html' } });
    check(res, { 'frontend 200': (r) => r.status === 200 });
    sleep(1);
  } else if (role === 1 || (role === 0 && !FRONTEND_URL)) {
    const res = http.get(`${BASE_URL}/api/diary`, { headers: apiHeaders });
    check(res, { 'diary list 200': (r) => r.status === 200 });
    sleep(1);
  } else if (role === 2) {
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
    sleep(1);
  } else {
    const res = http.get(`${BASE_URL}/api/auth/me`, { headers: apiHeaders });
    check(res, { 'me 200': (r) => r.status === 200 });
    sleep(1);
  }
}
