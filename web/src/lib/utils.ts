/** Join class names, dropping falsy values. Tiny clsx stand-in (KISS). */
export function cn(...classes: Array<string | false | null | undefined>): string {
  return classes.filter(Boolean).join(' ');
}

/** Format integer cents as USD. Returns an em dash for null/undefined. */
export function formatCents(cents: number | null | undefined): string {
  if (cents === null || cents === undefined) return '—';
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(cents / 100);
}

/** Dollars (as entered in a form) to integer cents for the API. */
export function dollarsToCents(dollars: string | number | null | undefined): number | null {
  if (dollars === '' || dollars === null || dollars === undefined) return null;
  const value = typeof dollars === 'string' ? Number.parseFloat(dollars) : dollars;
  if (Number.isNaN(value)) return null;
  return Math.round(value * 100);
}

/** Integer cents to a dollars string for editing in a form. */
export function centsToDollars(cents: number | null | undefined): string {
  if (cents === null || cents === undefined) return '';
  return (cents / 100).toFixed(2);
}

/** Human-readable date (or em dash). Accepts ISO strings / date-only strings. */
export function formatDate(value: string | null | undefined): string {
  if (!value) return '—';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

/** "in_transit" -> "In transit" for display. */
export function humanize(value: string): string {
  const spaced = value.replace(/_/g, ' ');
  return spaced.charAt(0).toUpperCase() + spaced.slice(1);
}
