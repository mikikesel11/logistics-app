import { download, request, requestWithMeta } from './client';
import type {
  BillOfLading,
  Carrier,
  CarrierInput,
  Customer,
  CustomerInput,
  Load,
  LoadInput,
  LoadStatus,
  Location,
  LocationInput,
  PaginationMeta,
  User,
} from './types';

// --- Auth ---

export interface LoginResult {
  token: string;
  user: User;
}

export const auth = {
  login: (email: string, password: string) =>
    request<LoginResult>('/login', { method: 'POST', body: { email, password } }),
  me: () => request<User>('/me'),
  logout: () => request<{ message: string }>('/logout', { method: 'POST' }),
};

// --- Customers ---

export const customers = {
  list: () => request<Customer[]>('/customers'),
  get: (id: number) => request<Customer>(`/customers/${id}`),
  create: (input: CustomerInput) =>
    request<Customer>('/customers', { method: 'POST', body: input }),
  update: (id: number, input: CustomerInput) =>
    request<Customer>(`/customers/${id}`, { method: 'PUT', body: input }),
  remove: (id: number) => request<{ message: string }>(`/customers/${id}`, { method: 'DELETE' }),
};

// --- Carriers ---

export const carriers = {
  list: () => request<Carrier[]>('/carriers'),
  get: (id: number) => request<Carrier>(`/carriers/${id}`),
  create: (input: CarrierInput) => request<Carrier>('/carriers', { method: 'POST', body: input }),
  update: (id: number, input: CarrierInput) =>
    request<Carrier>(`/carriers/${id}`, { method: 'PUT', body: input }),
  remove: (id: number) => request<{ message: string }>(`/carriers/${id}`, { method: 'DELETE' }),
};

// --- Locations (reusable addresses; paginated, first page holds up to 50) ---

export const locations = {
  list: async (): Promise<Location[]> => {
    const { data } = await requestWithMeta<Location[]>('/locations', {
      query: { per_page: 100 },
    });
    return data;
  },
  get: (id: number) => request<Location>(`/locations/${id}`),
  create: (input: LocationInput) =>
    request<Location>('/locations', { method: 'POST', body: input }),
  update: (id: number, input: LocationInput) =>
    request<Location>(`/locations/${id}`, { method: 'PUT', body: input }),
  remove: (id: number) => request<{ message: string }>(`/locations/${id}`, { method: 'DELETE' }),
};

// --- Loads ---

export interface LoadListResult {
  loads: Load[];
  meta: PaginationMeta;
}

export const loads = {
  list: async (params?: { status?: LoadStatus; per_page?: number }): Promise<LoadListResult> => {
    const { data, meta } = await requestWithMeta<Load[]>('/loads', {
      query: { status: params?.status, per_page: params?.per_page },
    });
    return { loads: data, meta: meta as unknown as PaginationMeta };
  },
  get: (id: number) => request<Load>(`/loads/${id}`),
  create: (input: LoadInput) => request<Load>('/loads', { method: 'POST', body: input }),
  update: (id: number, input: LoadInput) =>
    request<Load>(`/loads/${id}`, { method: 'PUT', body: input }),
  updateStatus: (id: number, status: LoadStatus) =>
    request<Load>(`/loads/${id}/status`, { method: 'PATCH', body: { status } }),
  remove: (id: number) => request<{ message: string }>(`/loads/${id}`, { method: 'DELETE' }),
};

// --- Bill of Lading ---

export const billOfLading = {
  generate: (loadId: number) =>
    request<BillOfLading>(`/loads/${loadId}/bol`, { method: 'POST' }),
  downloadPdf: (loadId: number) => download(`/loads/${loadId}/bol/download`),
};
