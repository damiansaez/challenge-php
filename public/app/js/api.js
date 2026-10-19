const BASE_URL = '/productos';

export class ApiError extends Error {
    constructor(message, status, details = {}) {
        super(message);
        this.status = status;
        this.details = details;
    }
}

async function request(url, options = {}) {
    let response;

    try {
        response = await fetch(url, {
            ...options,
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        });
    } catch {
        throw new ApiError('No se pudo conectar con el servidor', 0);
    }

    if (response.status === 204) {
        return null;
    }

    let body = null;
    try {
        body = await response.json();
    } catch {
    }

    if (!response.ok) {
        const error = body?.error ?? {};
        throw new ApiError(error.message ?? `Error ${response.status}`, response.status, error.details ?? {});
    }

    return body?.data ?? null;
}

export const listProducts = () => request(BASE_URL);

export const createProduct = (product) =>
    request(BASE_URL, { method: 'POST', body: JSON.stringify(product) });

export const updateProduct = (id, product) =>
    request(`${BASE_URL}/${id}`, { method: 'PUT', body: JSON.stringify(product) });

export const deleteProduct = (id) =>
    request(`${BASE_URL}/${id}`, { method: 'DELETE' });