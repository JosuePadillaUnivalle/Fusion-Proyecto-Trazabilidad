/**
 * API de la app.
 *
 * APK / release (__DEV__ = false):
 *   Solo Railway — abres la app como cualquier otra, con WiFi o datos móviles.
 *   No hace falta PC ni Expo Go.
 *
 * Desarrollo con Expo Go (__DEV__ = true):
 *   1) Railway (nube)
 *   2) Docker local (misma BD que http://localhost:8080)
 */

export const PRODUCTION_API_URL = 'https://agronexus-api-production.up.railway.app/api';

/** Solo en desarrollo — Docker local (ipconfig → 192.168.1.x) */
export const LOCAL_HOST = '192.168.1.17';
export const LOCAL_PORT = 8080;
export const LOCAL_API_URL = `http://${LOCAL_HOST}:${LOCAL_PORT}/api`;

export const API_CANDIDATES = __DEV__
  ? [
      { key: 'production', url: PRODUCTION_API_URL },
      { key: 'docker', url: LOCAL_API_URL },
    ]
  : [{ key: 'production', url: PRODUCTION_API_URL }];

export const PING_TIMEOUT_MS = 8000;

export const LOCAL_WEB_URL = `http://${LOCAL_HOST}:${LOCAL_PORT}`;
