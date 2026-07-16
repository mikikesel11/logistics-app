import { useState } from 'react';
import { ApiRequestError } from '@/api/client';
import { customers } from '@/api/resources';
import type { Customer, CustomerInput, ContactInput } from '@/api/types';
import { ContactsEditor } from '@/components/ContactsEditor';
import { PageHeader } from '@/components/PageHeader';
import { Button } from '@/components/ui/Button';
import { InputField, TextareaField } from '@/components/ui/Field';
import { Modal } from '@/components/ui/Modal';
import { Card, EmptyState, ErrorBanner, Spinner } from '@/components/ui/misc';
import { Table, type Column } from '@/components/ui/Table';
import { useAsync, toMessage } from '@/lib/useAsync';

export function CustomersPage() {
  const { data, loading, error, reload } = useAsync(() => customers.list());
  const [editing, setEditing] = useState<Customer | null>(null);
  const [creating, setCreating] = useState(false);

  const columns: Column<Customer>[] = [
    { header: 'Name', cell: (c) => <span className="font-medium">{c.name}</span> },
    { header: 'Email', cell: (c) => c.email ?? '—' },
    { header: 'Phone', cell: (c) => c.phone ?? '—' },
    { header: 'Terms', cell: (c) => c.billing_terms ?? '—' },
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
        title="Customers"
        subtitle="Shippers you broker freight for"
        action={<Button onClick={() => setCreating(true)}>+ New customer</Button>}
      />

      {error && <ErrorBanner message={error} />}

      <Card>
        {loading ? (
          <Spinner />
        ) : !data || data.length === 0 ? (
          <EmptyState
            title="No customers yet"
            description="Add your first shipper to start booking loads."
            action={<Button onClick={() => setCreating(true)}>+ New customer</Button>}
          />
        ) : (
          <Table columns={columns} rows={data} rowKey={(c) => c.id} />
        )}
      </Card>

      {creating && (
        <CustomerFormModal
          title="New customer"
          onClose={() => setCreating(false)}
          onSaved={() => {
            setCreating(false);
            reload();
          }}
          submit={(input) => customers.create(input)}
        />
      )}

      {editing && (
        <CustomerFormModal
          title={`Edit ${editing.name}`}
          initial={editing}
          onClose={() => setEditing(null)}
          onSaved={() => {
            setEditing(null);
            reload();
          }}
          submit={(input) => customers.update(editing.id, input)}
        />
      )}
    </div>
  );
}

function toInput(customer: Customer | undefined): CustomerInput {
  return {
    name: customer?.name ?? '',
    email: customer?.email ?? '',
    phone: customer?.phone ?? '',
    billing_terms: customer?.billing_terms ?? '',
    credit_limit:
      customer?.credit_limit != null ? Number(customer.credit_limit) : null,
    notes: customer?.notes ?? '',
  };
}

function CustomerFormModal({
  title,
  initial,
  onClose,
  onSaved,
  submit,
}: {
  title: string;
  initial?: Customer;
  onClose: () => void;
  onSaved: () => void;
  submit: (input: CustomerInput) => Promise<Customer>;
}) {
  const [form, setForm] = useState<CustomerInput>(() => toInput(initial));
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

  function patch(next: Partial<CustomerInput>) {
    setForm((prev) => ({ ...prev, ...next }));
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    setFieldErrors({});
    setFormError(null);
    try {
      await submit({ ...form, contacts });
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
          <Button type="submit" form="customer-form" disabled={saving}>
            {saving ? 'Saving…' : 'Save customer'}
          </Button>
        </>
      }
    >
      <form id="customer-form" onSubmit={onSubmit} className="space-y-4">
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
            label="Billing terms"
            placeholder="Net 30"
            value={form.billing_terms ?? ''}
            error={fieldErrors.billing_terms?.[0]}
            onChange={(e) => patch({ billing_terms: e.target.value })}
          />
          <InputField
            label="Credit limit"
            type="number"
            min={0}
            step="0.01"
            value={form.credit_limit ?? ''}
            error={fieldErrors.credit_limit?.[0]}
            onChange={(e) =>
              patch({ credit_limit: e.target.value === '' ? null : Number(e.target.value) })
            }
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
