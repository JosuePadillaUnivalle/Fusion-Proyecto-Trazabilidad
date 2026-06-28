import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_CANDIDATES, PING_TIMEOUT_MS } from '../config/api';

const STORAGE_KEY = 'agrofusion_api_base_url';

let activeBaseUrl = API_CANDIDATES[0].url;
let activeKey = API_CANDIDATES[0].key;
let resolving = null;

async function ping(baseUrl) {
  await axios.get(`${baseUrl}/test-api`, {
    timeout: PING_TIMEOUT_MS,
    headers: { Accept: 'application/json' },
  });
}

/**
 * Prueba producción primero; si falla, prueba local.
 * Guarda la URL que funcionó para la próxima vez.
 */
export async function resolveApiBaseUrl({ force = false } = {}) {
  if (resolving && !force) {
    return resolving;
  }

  resolving = (async () => {
    for (const candidate of API_CANDIDATES) {
      try {
        await ping(candidate.url);
        activeBaseUrl = candidate.url;
        activeKey = candidate.key;
        await AsyncStorage.setItem(STORAGE_KEY, candidate.url);
        if (__DEV__) {
          console.log('[AgroFusion] API conectada:', candidate.key, candidate.url);
        }
        return candidate.url;
      } catch {
        if (__DEV__) {
          console.log('[AgroFusion] API no disponible:', candidate.key);
        }
      }
    }

    const cached = await AsyncStorage.getItem(STORAGE_KEY);
    if (cached) {
      activeBaseUrl = cached;
      activeKey = cached.includes('railway') ? 'production' : 'local';
      return cached;
    }

    activeBaseUrl = API_CANDIDATES[0].url;
    activeKey = API_CANDIDATES[0].key;
    return activeBaseUrl;
  })();

  try {
    return await resolving;
  } finally {
    resolving = null;
  }
}

export function getApiBaseUrl() {
  return activeBaseUrl;
}

export function getApiModeKey() {
  return activeKey;
}

export function isNetworkFailure(error) {
  return !error?.response || error?.message === 'Network Error' || error?.code === 'ERR_NETWORK';
}

/** Tras un fallo de red, fuerza re-detección (p. ej. login). */
export async function retryWithFallbackApi() {
  await AsyncStorage.removeItem(STORAGE_KEY);
  return resolveApiBaseUrl({ force: true });
}
