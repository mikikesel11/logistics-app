import { useState } from 'react';
import { ApiRequestError } from '@/api/client';
import { locations } from '@/api/resources';
import type { Location, LocationInput } from '@/api/types';
import { PageHeader } from '@/components/PageHeader';
import { Button } from '@/components/ui/Button';
import { InputField } from '@/components/ui/Field';
import { Modal } from '@/components/ui/Modal';
import { Card, EmptyState, ErrorBanner, Spinner } from '@/components/ui/misc';
import { Table, type Column } from '@/components/ui/Table';
import { useAsync, toMessage } from '@/lib/useAsync';

export function LocationsPage() {
  const { data, loading, error, reload } = useAsync(() => locations.list());
  const [editing, setEditing] = useState<Location | null>(null);
  const [creating, setCreating] = useState(false);

  const columns: Column<Location>[] = [
    { header: 'Name', cell: (l) => <span className="font-medium">{l.name ?? '—'}</span> },
    { header: 'Address', cell: (l) => l.address_line1 ?? '—' },
    { header: 'City', cell: (l) => l.city ?? '—' },
    { header: 'State', cell: (l) => l.state ?? '—' },
    { header: 'Postal', cell: (l) => l.postal_code ?? '—' },
    {
      header: '',
      className: 'text-right',
      cell: (l) => (
        <Button
          variant="secondary"
          size="sm"
          onClick={(e) => {
            e.stopPropagation();
            setEditing(l);
          }}
        >
          Edit
        </Button>
      ),
    },
  ];

  return (
    <div>
      <PageHeader
        title="Locations"
        subtitle="Reusable pickup & delivery addresses (used on the Bill of Lading)"
        action={<Button onClick={() => setCreating(true)}>+ New location</Button>}
      />

      {error && <ErrorBanner message={error} />}

      <Card>
        {loading ? (
          <Spinner />
        ) : !data || data.length === 0 ? (
          <EmptyState
            title="No locations yet"
            description="Add an address to set as a load's origin or destination."
            action={<Button onClick={() => setCreating(true)}>+ New location</Button>}
          />
        ) : (
          <Table columns={columns} rows={data} rowKey={(l) => l.id} />
        )}
      </Card>

      {creating && (
        <LocationFormModal
          title="New location"
          onClose={() => setCreating(false)}
          onSaved={() => {
            setCreating(false);
            reload();
          }}
          submit={(input) => locations.create(input)}
        />
      )}

      {editing && (
        <LocationFormModal
          title={`Edit ${editing.name ?? 'location'}`}
          initial={editing}
          onClose={() => setEditing(null)}
          onSaved={() => {
            setEditing(null);
            reload();
          }}
          submit={(input) => locations.update(editing.id, input)}
        />
      )}
    </div>
  );
}

function toInput(location: Location | undefined): LocationInput {
  return {
    name: location?.name ?? '',
    address_line1: location?.address_line1 ?? '',
    address_line2: location?.address_line2 ?? '',
    city: location?.city ?? '',
    state: location?.state ?? '',
    postal_code: location?.postal_code ?? '',
    country: location?.country ?? 'US',
    contact_name: location?.contact_name ?? '',
    contact_phone: location?.contact_phone ?? '',
  };
}

function LocationFormModal({
  title,
  initial,
  onClose,
  onSaved,
  submit,
}: {
  title: string;
  initial?: Location;
  onClose: () => void;
  onSaved: () => void;
  submit: (input: LocationInput) => Promise<Location>;
}) {
  const [form, setForm] = useState<LocationInput>(() => toInput(initial));
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [formError, setFormError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);

  function patch(next: Partial<LocationInput>) {
    setForm((prev) => ({ ...prev, ...next }));
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    setFieldErrors({});
    setFormError(null);
    try {
      await submit(form);
      onSaved();
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
          <Button type="submit" form="location-form" disabled={saving}>
            {saving ? 'Saving…' : 'Save location'}
          </Button>
        </>
      }
    >
      <form id="location-form" onSubmit={onSubmit} className="space-y-4">
        {formError && <ErrorBanner message={formError} />}

        <InputField
          label="Facility name"
          placeholder="Acme Distribution Center"
          value={form.name ?? ''}
          error={fieldErrors.name?.[0]}
          onChange={(e) => patch({ name: e.target.value })}
        />
        <InputField
          label="Address line 1"
          required
          value={form.address_line1}
          error={fieldErrors.address_line1?.[0]}
          onChange={(e) => patch({ address_line1: e.target.value })}
        />
        <InputField
          label="Address line 2"
          value={form.address_line2 ?? ''}
          error={fieldErrors.address_line2?.[0]}
          onChange={(e) => patch({ address_line2: e.target.value })}
        />
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <InputField
            label="City"
            required
            value={form.city}
            error={fieldErrors.city?.[0]}
            onChange={(e) => patch({ city: e.target.value })}
          />
          <InputField
            label="State"
            required
            placeholder="OH"
            value={form.state}
            error={fieldErrors.state?.[0]}
            onChange={(e) => patch({ state: e.target.value })}
          />
          <InputField
            label="Postal code"
            required
            value={form.postal_code}
            error={fieldErrors.postal_code?.[0]}
            onChange={(e) => patch({ postal_code: e.target.value })}
          />
          <InputField
            label="Country"
            placeholder="US"
            maxLength={2}
            hint="2-letter code"
            value={form.country ?? ''}
            error={fieldErrors.country?.[0]}
            onChange={(e) => patch({ country: e.target.value.toUpperCase() })}
          />
          <InputField
            label="Contact name"
            value={form.contact_name ?? ''}
            error={fieldErrors.contact_name?.[0]}
            onChange={(e) => patch({ contact_name: e.target.value })}
          />
          <InputField
            label="Contact phone"
            value={form.contact_phone ?? ''}
            error={fieldErrors.contact_phone?.[0]}
            onChange={(e) => patch({ contact_phone: e.target.value })}
          />
        </div>
      </form>
    </Modal>
  );
}
