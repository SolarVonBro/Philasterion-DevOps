import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL     = __ENV.BASE_URL     || 'http://127.0.0.1:30080';
const FRONTEND_URL = __ENV.FRONTEND_URL || '';

export const options = {
  stages: [
    { duration: '30s', target: 25 },
    { duration: '4m',  target: 25 },
    { duration: '30s', target: 0  },
  ],
  thresholds: {
    http_req_duration: ['p(95)<3000'],
  },
};

export function setup() {
  const res = http.post(
    `${BASE_URL}/api/auth/login`,
    JSON.stringify({
      email:    __ENV.LOGIN_EMAIL    || 'admin@test.com',
      password: __ENV.LOGIN_PASSWORD || '000333him',
    }),
    { headers: { 'Content-Type': 'application/json' } },
  );
  return { token: res.json('token') };
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
    sleep(0.5);
  } else if (role === 2) {
    const res = http.post(
      `${BASE_URL}/api/diary`,
      JSON.stringify({ title: `load-test-${__VU}-${__ITER}`, content: 'k6' }),
      { headers: apiHeaders },
    );
    check(res, { 'diary create 2xx': (r) => r.status >= 200 && r.status < 300 });
    sleep(0.5);
  } else {
    const res = http.get(`${BASE_URL}/api/auth/me`, { headers: apiHeaders });
    check(res, { 'me 200': (r) => r.status === 200 });
    sleep(0.5);
  }
}
