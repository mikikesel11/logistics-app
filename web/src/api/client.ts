import type { ApiEnvelope, ApiError } from './types';

const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL as string | undefined) ?? '/api';
const TOKEN_STORAGE_KEY = 'freight_os.token';

/**
 * Error thrown for any non-2xx API response. Carries the HTTP status and the
 * parsed API error envelope so callers can branch on 401 (session expired) or
 * surface 422 field errors.
 */
export class ApiRequestError extends Error {
  readonly status: number;
  readonly details: Record<string, string[]> | null;

  constructor(status: number, error: ApiError | null) {
    super(error?.message ?? `Request failed with status ${status}`);
    this.name = 'ApiRequestError';
    this.status = status;
    this.details = error?.details ?? null;
  }

  /** First validation message for a field, if any (convenience for forms). */
  fieldError(field: string): string | undefined {
    return this.details?.[field]?.[0];
  }
}

// --- Token storage (bearer auth; the SPA persists the Sanctum PAT) ---

export function getToken(): string | null {
  return localStorage.getItem(TOKEN_STORAGE_KEY);
}

export function setToken(token: string): void {
  localStorage.setItem(TOKEN_STORAGE_KEY, token);
}

export function clearToken(): void {
  localStorage.removeItem(TOKEN_STORAGE_KEY);
}

interface RequestOptions {
  method?: string;
  body?: unknown;
  /** Query params appended to the URL (undefined/null values are skipped). */
  query?: Record<string, string | number | undefined | null>;
  signal?: AbortSignal;
}

function buildUrl(path: string, query?: RequestOptions['query']): string {
  const url = `${API_BASE_URL}${path}`;
  if (!query) return url;

  const params = new URLSearchParams();
  for (const [key, value] of Object.entries(query)) {
    if (value !== undefined && value !== null && value !== '') {
      params.append(key, String(value));
    }
  }
  const qs = params.toString();
  return qs ? `${url}?${qs}` : url;
}

/**
 * Core JSON request. Attaches the bearer token, parses the envelope, and throws
 * ApiRequestError on failure. Returns the unwrapped `data` payload on success.
 */
export async function request<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const { method = 'GET', body, query, signal } = options;

  const headers: Record<string, string> = {
    Accept: 'application/json',
  };
  const token = getToken();
  if (token) headers.Authorization = `Bearer ${token}`;
  if (body !== undefined) headers['Content-Type'] = 'application/json';

  const response = await fetch(buildUrl(path, query), {
    method,
    headers,
    body: body !== undefined ? JSON.stringify(body) : undefined,
    signal,
  });

  const envelope = await parseEnvelope<T>(response);

  if (!response.ok || !envelope?.success) {
    throw new ApiRequestError(response.status, envelope?.error ?? null);
  }

  return envelope.data;
}

async function parseEnvelope<T>(response: Response): Promise<ApiEnvelope<T> | null> {
  const text = await response.text();
  if (!text) return null;
  try {
    return JSON.parse(text) as ApiEnvelope<T>;
  } catch {
    // Non-JSON error (e.g. an HTML 500 page) — surface as a generic failure.
    return null;
  }
}

/** GET that also returns the envelope `meta` (used for pagination). */
export async function requestWithMeta<T>(
  path: string,
  options: RequestOptions = {},
): Promise<{ data: T; meta?: Record<string, unknown> }> {
  const { method = 'GET', body, query, signal } = options;

  const headers: Record<string, string> = { Accept: 'application/json' };
  const token = getToken();
  if (token) headers.Authorization = `Bearer ${token}`;
  if (body !== undefined) headers['Content-Type'] = 'application/json';

  const response = await fetch(buildUrl(path, query), {
    method,
    headers,
    body: body !== undefined ? JSON.stringify(body) : undefined,
    signal,
  });

  const envelope = await parseEnvelope<T>(response);
  if (!response.ok || !envelope?.success) {
    throw new ApiRequestError(response.status, envelope?.error ?? null);
  }

  return { data: envelope.data, meta: envelope.meta };
}

/** Download a binary artifact (BOL PDF). Throws ApiRequestError on failure. */
export async function download(path: string): Promise<Blob> {
  const headers: Record<string, string> = { Accept: 'application/pdf' };
  const token = getToken();
  if (token) headers.Authorization = `Bearer ${token}`;

  const response = await fetch(buildUrl(path), { headers });

  if (!response.ok) {
    // Error responses are JSON envelopes even on the download route.
    const envelope = await parseEnvelope<never>(response);
    throw new ApiRequestError(response.status, envelope?.error ?? null);
  }

  return response.blob();
}
