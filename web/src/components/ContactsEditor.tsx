import type { ContactInput } from '@/api/types';
import { Button } from '@/components/ui/Button';
import { InputField } from '@/components/ui/Field';

const EMPTY_CONTACT: ContactInput = {
  name: '',
  title: '',
  email: '',
  phone: '',
  is_primary: false,
};

/**
 * Edits a list of nested contacts immutably — every change produces a new array
 * (no in-place mutation), matching the project's immutability rule.
 */
export function ContactsEditor({
  contacts,
  onChange,
}: {
  contacts: ContactInput[];
  onChange: (next: ContactInput[]) => void;
}) {
  function updateAt(index: number, patch: Partial<ContactInput>) {
    onChange(contacts.map((c, i) => (i === index ? { ...c, ...patch } : c)));
  }

  function removeAt(index: number) {
    onChange(contacts.filter((_, i) => i !== index));
  }

  function add() {
    onChange([...contacts, { ...EMPTY_CONTACT }]);
  }

  return (
    <fieldset className="space-y-3">
      <div className="flex items-center justify-between">
        <legend className="text-sm font-medium text-slate-700 dark:text-slate-300">Contacts</legend>
        <Button variant="secondary" size="sm" onClick={add}>
          + Add contact
        </Button>
      </div>

      {contacts.length === 0 && (
        <p className="text-xs text-slate-500 dark:text-slate-400">No contacts added.</p>
      )}

      {contacts.map((contact, index) => (
        <div
          key={index}
          className="rounded-md border border-slate-200 p-3 dark:border-slate-700"
        >
          <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <InputField
              label="Name"
              required
              value={contact.name}
              onChange={(e) => updateAt(index, { name: e.target.value })}
            />
            <InputField
              label="Title"
              value={contact.title ?? ''}
              onChange={(e) => updateAt(index, { title: e.target.value })}
            />
            <InputField
              label="Email"
              type="email"
              value={contact.email ?? ''}
              onChange={(e) => updateAt(index, { email: e.target.value })}
            />
            <InputField
              label="Phone"
              value={contact.phone ?? ''}
              onChange={(e) => updateAt(index, { phone: e.target.value })}
            />
          </div>
          <div className="mt-2 flex items-center justify-between">
            <label className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
              <input
                type="checkbox"
                checked={contact.is_primary ?? false}
                onChange={(e) => updateAt(index, { is_primary: e.target.checked })}
              />
              Primary contact
            </label>
            <Button variant="ghost" size="sm" onClick={() => removeAt(index)}>
              Remove
            </Button>
          </div>
        </div>
      ))}
    </fieldset>
  );
}
