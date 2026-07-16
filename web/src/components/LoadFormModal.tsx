import { useState } from 'react';
import { ApiRequestError } from '@/api/client';
import { carriers, customers, locations } from '@/api/resources';
import type { Load, LoadInput, Location } from '@/api/types';
import { Button } from '@/components/ui/Button';
import { InputField, SelectField, TextareaField } from '@/components/ui/Field';
import { Modal } from '@/components/ui/Modal';
import { ErrorBanner, Spinner } from '@/components/ui/misc';
import { useAsync, toMessage } from '@/lib/useAsync';
import { centsToDollars, dollarsToCents } from '@/lib/utils';

interface LoadFormState {
  reference: string;
  customer_id: string;
  carrier_id: string;
  origin_location_id: string;
  destination_location_id: string;
  commodity: string;
  weight_lbs: string;
  pickup_date: string;
  delivery_date: string;
  customer_rate: string; // dollars
  carrier_cost: string; // dollars
  notes: string;
}

function toState(load: Load | undefined): LoadFormState {
  return {
    reference: load?.reference ?? '',
    customer_id: load?.customer_id != null ? String(load.customer_id) : '',
    carrier_id: load?.carrier_id != null ? String(load.carrier_id) : '',
    origin_location_id: load?.origin_location_id != null ? String(load.origin_location_id) : '',
    destination_location_id:
      load?.destination_location_id != null ? String(load.destination_location_id) : '',
    commodity: load?.commodity ?? '',
    weight_lbs: load?.weight_lbs != null ? String(load.weight_lbs) : '',
    pickup_date: load?.pickup_date ?? '',
    delivery_date: load?.delivery_date ?? '',
    customer_rate: centsToDollars(load?.customer_rate_cents),
    carrier_cost: centsToDollars(load?.carrier_cost_cents),
    notes: load?.notes ?? '',
  };
}

/** "Acme DC — Columbus, OH" style label for a location option. */
function locationLabel(location: Location): string {
  const place = [location.city, location.state].filter(Boolean).join(', ');
  return location.name ? `${location.name} — ${place}` : place || (location.address_line1 ?? '');
}

function toInput(state: LoadFormState): LoadInput {
  return {
    reference: state.reference || null,
    customer_id: state.customer_id ? Number(state.customer_id) : null,
    carrier_id: state.carrier_id ? Number(state.carrier_id) : null,
    origin_location_id: state.origin_location_id ? Number(state.origin_location_id) : null,
    destination_location_id: state.destination_location_id
      ? Number(state.destination_location_id)
      : null,
    commodity: state.commodity || null,
    weight_lbs: state.weight_lbs ? Number(state.weight_lbs) : null,
    pickup_date: state.pickup_date || null,
    delivery_date: state.delivery_date || null,
    customer_rate_cents: dollarsToCents(state.customer_rate) ?? 0,
    carrier_cost_cents: dollarsToCents(state.carrier_cost) ?? 0,
    notes: state.notes || null,
  };
}

export function LoadFormModal({
  title,
  initial,
  onClose,
  onSaved,
  submit,
}: {
  title: string;
  initial?: Load;
  onClose: () => void;
  onSaved: (load: Load) => void;
  submit: (input: LoadInput) => Promise<Load>;
}) {
  // Load the customer/carrier/location options for the assignment dropdowns.
  const options = useAsync(async () => {
    const [customerList, carrierList, locationList] = await Promise.all([
      customers.list(),
      carriers.list(),
      locations.list(),
    ]);
    return { customerList, carrierList, locationList };
  });

  const locationOptions = [
    { value: '', label: '— None —' },
    ...(options.data?.locationList.map((l) => ({
      value: String(l.id),
      label: locationLabel(l),
    })) ?? []),
  ];

  const [form, setForm] = useState<LoadFormState>(() => toState(initial));
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [formError, setFormError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);

  function patch(next: Partial<LoadFormState>) {
    setForm((prev) => ({ ...prev, ...next }));
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    setFieldErrors({});
    setFormError(null);
    try {
      const load = await submit(toInput(form));
      onSaved(load);
    } catch (err) {
      if (err instanceof ApiRequestError && err.status === 422 && err.details) {
        setFieldErrors(err.details);
      } else {
        setFormError(toMessage(err));
      }
    } finally {
      setSaving(false);
    }
  }

  return (
    <Modal
      open
      title={title}
      onClose={onClose}
      footer={
        <>
          <Button variant="secondary" onClick={onClose} disabled={saving}>
            Cancel
          </Button>
          <Button type="submit" form="load-form" disabled={saving || options.loading}>
            {saving ? 'Saving…' : 'Save load'}
          </Button>
        </>
      }
    >
      {options.loading ? (
        <Spinner />
      ) : options.error ? (
        <ErrorBanner message={options.error} />
      ) : (
        <form id="load-form" onSubmit={onSubmit} className="space-y-4">
          {formError && <ErrorBanner message={formError} />}

          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <InputField
              label="Reference"
              placeholder="Auto-generated if blank"
              value={form.reference}
              error={fieldErrors.reference?.[0]}
              onChange={(e) => patch({ reference: e.target.value })}
            />
            <InputField
              label="Commodity"
              value={form.commodity}
              error={fieldErrors.commodity?.[0]}
              onChange={(e) => patch({ commodity: e.target.value })}
            />
            <SelectField
              label="Customer"
              value={form.customer_id}
              error={fieldErrors.customer_id?.[0]}
              onChange={(e) => patch({ customer_id: e.target.value })}
              options={[
                { value: '', label: '— None —' },
                ...(options.data?.customerList.map((c) => ({
                  value: String(c.id),
                  label: c.name,
                })) ?? []),
              ]}
            />
            <SelectField
              label="Carrier"
              value={form.carrier_id}
              error={fieldErrors.carrier_id?.[0]}
              onChange={(e) => patch({ carrier_id: e.target.value })}
              options={[
                { value: '', label: '— None —' },
                ...(options.data?.carrierList.map((c) => ({
                  value: String(c.id),
                  label: c.name,
                })) ?? []),
              ]}
            />
            <SelectField
              label="Origin"
              value={form.origin_location_id}
              error={fieldErrors.origin_location_id?.[0]}
              onChange={(e) => patch({ origin_location_id: e.target.value })}
              options={locationOptions}
            />
            <SelectField
              label="Destination"
              value={form.destination_location_id}
              error={fieldErrors.destination_location_id?.[0]}
              onChange={(e) => patch({ destination_location_id: e.target.value })}
              options={locationOptions}
            />
            <InputField
              label="Pickup date"
              type="date"
              value={form.pickup_date}
              error={fieldErrors.pickup_date?.[0]}
              onChange={(e) => patch({ pickup_date: e.target.value })}
            />
            <InputField
              label="Delivery date"
              type="date"
              value={form.delivery_date}
              error={fieldErrors.delivery_date?.[0]}
              onChange={(e) => patch({ delivery_date: e.target.value })}
            />
            <InputField
              label="Weight (lbs)"
              type="number"
              min={0}
              value={form.weight_lbs}
              error={fieldErrors.weight_lbs?.[0]}
              onChange={(e) => patch({ weight_lbs: e.target.value })}
            />
            <div />
            <InputField
              label="Customer rate ($)"
              type="number"
              min={0}
              step="0.01"
              value={form.customer_rate}
              error={fieldErrors.customer_rate_cents?.[0]}
              onChange={(e) => patch({ customer_rate: e.target.value })}
            />
            <InputField
              label="Carrier cost ($)"
              type="number"
              min={0}
              step="0.01"
              value={form.carrier_cost}
              error={fieldErrors.carrier_cost_cents?.[0]}
              onChange={(e) => patch({ carrier_cost: e.target.value })}
            />
          </div>

          <TextareaField
            label="Notes"
            value={form.notes}
            error={fieldErrors.notes?.[0]}
            onChange={(e) => patch({ notes: e.target.value })}
          />
        </form>
      )}
    </Modal>
  );
}
