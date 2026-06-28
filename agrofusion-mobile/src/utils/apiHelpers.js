/**
 * Normaliza respuestas de /api/* (array directo o { data: [...] }).
 * Misma forma en todas las pantallas → mismos datos que consume la web vía backend.
 */
export function unwrapApiList(response) {
  const body = response?.data;
  if (Array.isArray(body)) {
    return body;
  }
  if (body && Array.isArray(body.data)) {
    return body.data;
  }
  return [];
}

export function unwrapApiItem(response) {
  const body = response?.data;
  if (body && typeof body === 'object' && body.data !== undefined && !Array.isArray(body.data)) {
    return body.data;
  }
  return body ?? null;
}

/** Mensaje legible para errores de axios (red, 401, validación). */
export function formatApiError(error, fallback = 'Ocurrió un error') {
  if (!error?.response) {
    const isNetwork = error?.message === 'Network Error' || error?.code === 'ERR_NETWORK';
    if (isNetwork) {
      return (
        'No se pudo conectar al servidor (Network Error).\n\n' +
        'Si falla local: run-docker-local.bat + misma WiFi (puerto 8080)\n' +
        'Si falla en nube: activa internet en el teléfono'
      );
    }
    if (error?.code === 'ECONNABORTED') {
      return 'La API tardó demasiado. Revisa tu conexión a internet.';
    }
    return `No se pudo conectar al servidor. ${error?.message || fallback}`;
  }

  const { data, status } = error.response;

  if (data?.message) {
    return data.message;
  }

  if (data?.errors && typeof data.errors === 'object') {
    const first = Object.values(data.errors).flat()[0];
    if (first) {
      return first;
    }
  }

  if (status === 401) {
    return 'Correo o contraseña incorrectos. Usa las mismas credenciales que en la web.';
  }

  if (status === 422) {
    return 'Datos inválidos. Revisa correo y contraseña.';
  }

  return fallback;
}
