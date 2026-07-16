import { useState } from 'react';
import { ApiRequestError } from '@/api/client';
import { carriers } from '@/api/resources';
import type { Carrier, CarrierInput, ContactInput } from '@/api/types';
import { ContactsEditor } from '@/components/ContactsEditor';
import { PageHeader } from '@/components/PageHeader';
import { Button } from '@/components/ui/Button';
import { InputField, TextareaField } from '@/components/ui/Field';
import { Modal } from '@/components/ui/Modal';
import { Card, EmptyState, ErrorBanner, Spinner } from '@/components/ui/misc';
import { Table, type Column } from '@/components/ui/Table';
import { useAsync, toMessage } from '@/lib/useAsync';
import { formatDate } from '@/lib/utils';

export function CarriersPage() {
  const { data, loading, error, reload } = useAsync(() => carriers.list());
  const [editing, setEditing] = useState<Carrier | null>(null);
  const [creating, setCreating] = useState(false);

  const columns: Column<Carrier>[] = [
    { header: 'Name', cell: (c) => <span className="font-medium">{c.name}</span> },
    { header: 'MC #', cell: (c) => c.mc_number ?? '—' },
    { header: 'DOT #', cell: (c) => c.dot_number ?? '—' },
    { header: 'Insurance exp.', cell: (c) => formatDate(c.insurance_expires_at) },
    {
      header: '',
      className: 'text-right',
      cell: (c) => (
        <Button
          variant="secondary"
          size="sm"
          onClick={(e) => {
            e.stopPropagation();
            setEditing(c);
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
        title="Carriers"
        subtitle="Trucking companies that haul your loads"
        action={<Button onClick={() => setCreating(true)}>+ New carrier</Button>}
      />

      {error && <ErrorBanner message={error} />}

      <Card>
        {loading ? (
          <Spinner />
        ) : !data || data.length === 0 ? (
          <EmptyState
            title="No carriers yet"
            description="Add a carrier so you can assign it to loads."
            action={<Button onClick={() => setCreating(true)}>+ New carrier</Button>}
          />
        ) : (
          <Table columns={columns} rows={data} rowKey={(c) => c.id} />
        )}
      </Card>

      {creating && (
        <CarrierFormModal
          title="New carrier"
          onClose={() => setCreating(false)}
          onSaved={() => {
            setCreating(false);
            reload();
          }}
          submit={(input) => carriers.create(input)}
        />
      )}

      {editing && (
        <CarrierFormModal
          title={`Edit ${editing.name}`}
          initial={editing}
          onClose={() => setEditing(null)}
          onSaved={() => {
            setEditing(null);
            reload();
          }}
          submit={(input) => carriers.update(editing.id, input)}
        />
      )}
    </div>
  );
}

function toInput(carrier: Carrier | undefined): CarrierInput {
  return {
    name: carrier?.name ?? '',
    mc_number: carrier?.mc_number ?? '',
    dot_number: carrier?.dot_number ?? '',
    email: carrier?.email ?? '',
    phone: carrier?.phone ?? '',
    insurance_expires_at: carrier?.insurance_expires_at ?? '',
    notes: carrier?.notes ?? '',
  };
}

function CarrierFormModal({
  title,
  initial,
  onClose,
  onSaved,
  submit,
}: {
  title: string;
  initial?: Carrier;
  onClose: () => void;
  onSaved: () => void;
  submit: (input: CarrierInput) => Promise<Carrier>;
}) {
  const [form, setForm] = useState<CarrierInput>(() => toInput(initial));
  const [contacts, setContacts] = useState<ContactInput[]>(
    () =>
      initial?.contacts?.map((c) => ({
        name: c.name,
        title: c.title,
        email: c.email,
        phone: c.phone,
        is_primary: c.is_primary,
      })) ?? [],
  );
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [formError, setFormError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);

  function patch(next: Partial<CarrierInput>) {
    setForm((prev) => ({ ...prev, ...next }));
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    setFieldErrors({});
    setFormError(null);
    try {
      // Send null (not "") for the empty insurance date so validation passes.
      await submit({
        ...form,
        insurance_expires_at: form.insurance_expires_at || null,
        contacts,
      });
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
          <Button type="submit" form="carrier-form" disabled={saving}>
            {saving ? 'Saving…' : 'Save carrier'}
          </Button>
        </>
      }
    >
      <form id="carrier-form" onSubmit={onSubmit} className="space-y-4">
        {formError && <ErrorBanner message={formError} />}

        <InputField
          label="Name"
          required
          value={form.name}
          error={fieldErrors.name?.[0]}
          onChange={(e) => patch({ name: e.target.value })}
        />
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <InputField
            label="MC number"
            value={form.mc_number ?? ''}
            error={fieldErrors.mc_number?.[0]}
            onChange={(e) => patch({ mc_number: e.target.value })}
          />
          <InputField
            label="DOT number"
            value={form.dot_number ?? ''}
            error={fieldErrors.dot_number?.[0]}
            onChange={(e) => patch({ dot_number: e.target.value })}
          />
          <InputField
            label="Email"
            type="email"
            value={form.email ?? ''}
            error={fieldErrors.email?.[0]}
            onChange={(e) => patch({ email: e.target.value })}
          />
          <InputField
            label="Phone"
            value={form.phone ?? ''}
            error={fieldErrors.phone?.[0]}
            onChange={(e) => patch({ phone: e.target.value })}
          />
          <InputField
            label="Insurance expires"
            type="date"
            value={form.insurance_expires_at ?? ''}
            error={fieldErrors.insurance_expires_at?.[0]}
            onChange={(e) => patch({ insurance_expires_at: e.target.value })}
          />
        </div>
        <TextareaField
          label="Notes"
          value={form.notes ?? ''}
          error={fieldErrors.notes?.[0]}
          onChange={(e) => patch({ notes: e.target.value })}
        />

        <ContactsEditor contacts={contacts} onChange={setContacts} />
      </form>
    </Modal>
  );
}
