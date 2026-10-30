/**
 * Thin fetch wrapper: same-origin, JSON, CSRF header, typed errors.
 * Auth lives in the Laravel session cookie (never in localStorage).
 */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

export class ApiError extends Error {
    constructor(message, status, data) {
        super(message);
        this.status = status;
        this.data = data ?? {};
    }
}

export async function api(path, { method = 'GET', body, form } = {}) {
    const options = {
        method,
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
    };

    if (form) {
        options.body = form;
    } else if (body !== undefined) {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
    }

    let response;

    try {
        response = await fetch(path, options);
    } catch (error) {
        throw new ApiError('নেটওয়ার্ক সমস্যা', 0, { offline: true });
    }

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new ApiError(data.message ?? data.error ?? 'অনুরোধ ব্যর্থ হয়েছে', response.status, data);
    }

    return data;
}

export function getPath(object, path) {
    return path.split('.').reduce((acc, key) => (acc == null ? undefined : acc[key]), object);
}

export function setPath(object, path, value) {
    const keys = path.split('.');
    const last = keys.pop();
    let target = object;

    for (const key of keys) {
        if (typeof target[key] !== 'object' || target[key] === null) {
            target[key] = {};
        }
        target = target[key];
    }

    target[last] = value;
}
