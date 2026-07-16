/**
 * Types mirroring the Laravel API Resources. Kept in sync by hand — the API is
 * the source of truth (see api/app/Http/Resources/*).
 */

export interface ApiEnvelope<T> {
  success: boolean;
  data: T;
  error: ApiError | null;
  meta?: Record<string, unknown>;
}

export interface ApiError {
  message: string;
  /** Laravel validation errors: field -> messages. Present on 422 responses. */
  details?: Record<string, string[]> | null;
}

export interface PaginationMeta {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}

export interface User {
  id: number;
  name: string;
  email: string;
  organization_id: number;
  roles: string[];
  created_at: string;
}

export interface Contact {
  id: number;
  name: string;
  title: string | null;
  email: string | null;
  phone: string | null;
  is_primary: boolean;
}

/** Shape used when creating/updating contacts (no server-assigned id). */
export interface ContactInput {
  name: string;
  title?: string | null;
  email?: string | null;
  phone?: string | null;
  is_primary?: boolean;
}

export interface Customer {
  id: number;
  name: string;
  email: string | null;
  phone: string | null;
  billing_terms: string | null;
  credit_limit: string | number | null;
  notes: string | null;
  contacts?: Contact[];
  contacts_count?: number;
  created_at: string;
  updated_at: string;
}

export interface CustomerInput {
  name: string;
  email?: string | null;
  phone?: string | null;
  billing_terms?: string | null;
  credit_limit?: number | null;
  notes?: string | null;
  contacts?: ContactInput[];
}

export interface Carrier {
  id: number;
  name: string;
  mc_number: string | null;
  dot_number: string | null;
  email: string | null;
  phone: string | null;
  insurance_expires_at: string | null;
  notes: string | null;
  contacts?: Contact[];
  contacts_count?: number;
  created_at: string;
  updated_at: string;
}

export interface CarrierInput {
  name: string;
  mc_number?: string | null;
  dot_number?: string | null;
  email?: string | null;
  phone?: string | null;
  insurance_expires_at?: string | null;
  notes?: string | null;
  contacts?: ContactInput[];
}

export interface Location {
  id: number;
  name: string | null;
  address_line1: string | null;
  address_line2: string | null;
  city: string | null;
  state: string | null;
  postal_code: string | null;
  country: string | null;
  contact_name: string | null;
  contact_phone: string | null;
}

export interface LocationInput {
  name?: string | null;
  address_line1: string;
  address_line2?: string | null;
  city: string;
  state: string;
  postal_code: string;
  country?: string | null;
  contact_name?: string | null;
  contact_phone?: string | null;
}

export interface FreightItem {
  id: number;
  description: string;
  pieces: number | null;
  weight_lbs: number | null;
  freight_class: string | null;
}

export interface FreightItemInput {
  description: string;
  pieces?: number | null;
  weight_lbs?: number | null;
  freight_class?: string | null;
}

export const LOAD_STATUSES = [
  'quoted',
  'booked',
  'dispatched',
  'in_transit',
  'delivered',
  'invoiced',
  'cancelled',
] as const;

export type LoadStatus = (typeof LOAD_STATUSES)[number];

/**
 * Guarded transitions, mirrored from api/app/Domain/Loads/LoadStatus.php so the
 * UI only offers legal next states. The server remains the enforcement point.
 */
export const ALLOWED_NEXT_STATUS: Record<LoadStatus, LoadStatus[]> = {
  quoted: ['booked', 'cancelled'],
  booked: ['dispatched', 'cancelled'],
  dispatched: ['in_transit', 'cancelled'],
  in_transit: ['delivered'],
  delivered: ['invoiced'],
  invoiced: [],
  cancelled: [],
};

export interface Load {
  id: number;
  reference: string | null;
  status: LoadStatus;
  customer_id: number | null;
  carrier_id: number | null;
  origin_location_id: number | null;
  destination_location_id: number | null;
  commodity: string | null;
  weight_lbs: number | null;
  pickup_date: string | null;
  delivery_date: string | null;
  customer_rate_cents: number | null;
  carrier_cost_cents: number | null;
  margin_cents: number | null;
  notes: string | null;
  customer?: Customer;
  carrier?: Carrier;
  origin?: Location;
  destination?: Location;
  freight_items?: FreightItem[];
  created_at: string;
  updated_at: string;
}

export interface LoadInput {
  reference?: string | null;
  customer_id?: number | null;
  carrier_id?: number | null;
  origin_location_id?: number | null;
  destination_location_id?: number | null;
  commodity?: string | null;
  weight_lbs?: number | null;
  pickup_date?: string | null;
  delivery_date?: string | null;
  customer_rate_cents?: number | null;
  carrier_cost_cents?: number | null;
  notes?: string | null;
  freight_items?: FreightItemInput[];
}

export interface BillOfLading {
  id: number;
  load_id: number;
  bol_number: string;
  customer_name: string | null;
  carrier_name: string | null;
  ship_from: Record<string, unknown> | null;
  ship_to: Record<string, unknown> | null;
  freight: unknown;
  special_instructions: string | null;
  is_ready: boolean;
  generated_at: string | null;
  created_at: string;
}
