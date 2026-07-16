import { useId } from 'react';
import { cn } from '@/lib/utils';

const INPUT_CLASSES = cn(
  'block w-full rounded-md border-0 px-3 py-2 text-sm shadow-sm ring-1 ring-inset',
  'ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-600',
  'bg-white text-slate-900 dark:bg-slate-800 dark:text-slate-100 dark:ring-slate-600',
  'disabled:cursor-not-allowed disabled:opacity-60',
);

interface BaseProps {
  label: string;
  error?: string;
  hint?: string;
}

type InputFieldProps = BaseProps & React.InputHTMLAttributes<HTMLInputElement>;

export function InputField({ label, error, hint, className, id, ...props }: InputFieldProps) {
  const generatedId = useId();
  const fieldId = id ?? generatedId;
  return (
    <div className={className}>
      <FieldLabel htmlFor={fieldId} label={label} required={props.required} />
      <input
        id={fieldId}
        className={cn(INPUT_CLASSES, error && 'ring-red-500 focus:ring-red-500')}
        aria-invalid={error ? true : undefined}
        {...props}
      />
      <FieldMessage error={error} hint={hint} />
    </div>
  );
}

type TextareaFieldProps = BaseProps & React.TextareaHTMLAttributes<HTMLTextAreaElement>;

export function TextareaField({ label, error, hint, className, id, ...props }: TextareaFieldProps) {
  const generatedId = useId();
  const fieldId = id ?? generatedId;
  return (
    <div className={className}>
      <FieldLabel htmlFor={fieldId} label={label} required={props.required} />
      <textarea
        id={fieldId}
        rows={3}
        className={cn(INPUT_CLASSES, error && 'ring-red-500 focus:ring-red-500')}
        aria-invalid={error ? true : undefined}
        {...props}
      />
      <FieldMessage error={error} hint={hint} />
    </div>
  );
}

interface SelectOption {
  value: string;
  label: string;
}

type SelectFieldProps = BaseProps &
  React.SelectHTMLAttributes<HTMLSelectElement> & { options: SelectOption[] };

export function SelectField({
  label,
  error,
  hint,
  options,
  className,
  id,
  ...props
}: SelectFieldProps) {
  const generatedId = useId();
  const fieldId = id ?? generatedId;
  return (
    <div className={className}>
      <FieldLabel htmlFor={fieldId} label={label} required={props.required} />
      <select
        id={fieldId}
        className={cn(INPUT_CLASSES, error && 'ring-red-500 focus:ring-red-500')}
        aria-invalid={error ? true : undefined}
        {...props}
      >
        {options.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
      <FieldMessage error={error} hint={hint} />
    </div>
  );
}

function FieldLabel({
  htmlFor,
  label,
  required,
}: {
  htmlFor: string;
  label: string;
  required?: boolean;
}) {
  return (
    <label
      htmlFor={htmlFor}
      className="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300"
    >
      {label}
      {required && (
        <span className="ml-0.5 text-red-500" aria-hidden="true">
          *
        </span>
      )}
    </label>
  );
}

function FieldMessage({ error, hint }: { error?: string; hint?: string }) {
  if (error) {
    return <p className="mt-1 text-xs text-red-600 dark:text-red-400">{error}</p>;
  }
  if (hint) {
    return <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">{hint}</p>;
  }
  return null;
}
