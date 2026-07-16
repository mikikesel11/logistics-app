import { useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { ApiRequestError } from '@/api/client';
import { billOfLading, loads } from '@/api/resources';
import { ALLOWED_NEXT_STATUS, type Load, type LoadStatus } from '@/api/types';
import { LoadFormModal } from '@/components/LoadFormModal';
import { PageHeader } from '@/components/PageHeader';
import { Button } from '@/components/ui/Button';
import { Card, ErrorBanner, Spinner, StatusBadge } from '@/components/ui/misc';
import { useAsync, toMessage } from '@/lib/useAsync';
import { formatCents, formatDate, humanize } from '@/lib/utils';

export function LoadDetailPage() {
  const { id } = useParams<{ id: string }>();
  const loadId = Number(id);
  const navigate = useNavigate();

  const { data: load, loading, error, reload } = useAsync(() => loads.get(loadId));
  const [editing, setEditing] = useState(false);
  const [actionError, setActionError] = useState<string | null>(null);

  if (loading) return <Spinner />;
  if (error) return <ErrorBanner message={error} />;
  if (!load) return <ErrorBanner message="Load not found." />;

  return (
    <div>
      <div className="mb-4">
        <Link to="/loads" className="text-sm text-brand-600 hover:underline">
          ← Back to loads
        </Link>
      </div>

      <PageHeader
        title={load.reference ?? `Load #${load.id}`}
        subtitle={load.commodity ?? 'No commodity specified'}
        action={
          <div className="flex items-center gap-2">
            <StatusBadge status={load.status} />
            <Button variant="secondary" onClick={() => setEditing(true)}>
              Edit
            </Button>
          </div>
        }
      />

      {actionError && (
        <div className="mb-4">
          <ErrorBanner message={actionError} />
        </div>
      )}

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <LoadSummary load={load} />
          <StatusPanel
            load={load}
            onError={setActionError}
            onChanged={() => {
              setActionError(null);
              reload();
            }}
          />
        </div>

        <div className="space-y-6">
          <BolPanel load={load} onError={setActionError} onGenerated={() => setActionError(null)} />
        </div>
      </div>

      {editing && (
        <LoadFormModal
          title={`Edit ${load.reference ?? `Load #${load.id}`}`}
          initial={load}
          onClose={() => setEditing(false)}
          onSaved={() => {
            setEditing(false);
            reload();
          }}
          submit={(input) => loads.update(load.id, input)}
        />
      )}

      <div className="mt-8">
        <Button
          variant="danger"
          size="sm"
          onClick={async () => {
            if (!confirm('Delete this load? This cannot be undone.')) return;
            try {
              await loads.remove(load.id);
              navigate('/loads');
            } catch (err) {
              setActionError(toMessage(err));
            }
          }}
        >
          Delete load
        </Button>
      </div>
    </div>
  );
}

function LoadSummary({ load }: { load: Load }) {
  return (
    <Card>
      <SectionHeading>Details</SectionHeading>
      <dl className="grid grid-cols-1 gap-x-6 gap-y-4 px-4 py-4 sm:grid-cols-2">
        <Detail label="Customer" value={load.customer?.name ?? '—'} />
        <Detail label="Carrier" value={load.carrier?.name ?? '—'} />
        <Detail label="Pickup" value={formatDate(load.pickup_date)} />
        <Detail label="Delivery" value={formatDate(load.delivery_date)} />
        <Detail
          label="Weight"
          value={load.weight_lbs != null ? `${load.weight_lbs.toLocaleString()} lbs` : '—'}
        />
        <Detail label="Customer rate" value={formatCents(load.customer_rate_cents)} />
        <Detail label="Carrier cost" value={formatCents(load.carrier_cost_cents)} />
        <Detail
          label="Margin"
          value={<span className="font-semibold">{formatCents(load.margin_cents)}</span>}
        />
        {load.origin && <Detail label="Origin" value={formatLocation(load.origin)} />}
        {load.destination && <Detail label="Destination" value={formatLocation(load.destination)} />}
      </dl>
      {load.notes && (
        <div className="border-t border-slate-200 px-4 py-3 dark:border-slate-700">
          <p className="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
            Notes
          </p>
          <p className="mt-1 whitespace-pre-wrap text-sm text-slate-700 dark:text-slate-200">
            {load.notes}
          </p>
        </div>
      )}
    </Card>
  );
}

function StatusPanel({
  load,
  onError,
  onChanged,
}: {
  load: Load;
  onError: (message: string) => void;
  onChanged: () => void;
}) {
  const [updating, setUpdating] = useState<LoadStatus | null>(null);
  const nextStatuses = ALLOWED_NEXT_STATUS[load.status];

  async function transition(target: LoadStatus) {
    setUpdating(target);
    try {
      await loads.updateStatus(load.id, target);
      onChanged();
    } catch (err) {
      if (err instanceof ApiRequestError) onError(err.message);
      else onError(toMessage(err));
    } finally {
      setUpdating(null);
    }
  }

  return (
    <Card>
      <SectionHeading>Status</SectionHeading>
      <div className="px-4 py-4">
        <div className="mb-3 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
          Current: <StatusBadge status={load.status} />
        </div>
        {nextStatuses.length === 0 ? (
          <p className="text-sm text-slate-500 dark:text-slate-400">
            This load is in a terminal state — no further transitions.
          </p>
        ) : (
          <div className="flex flex-wrap gap-2">
            {nextStatuses.map((status) => (
              <Button
                key={status}
                variant={status === 'cancelled' ? 'danger' : 'primary'}
                size="sm"
                disabled={updating !== null}
                onClick={() => transition(status)}
              >
                {updating === status ? 'Updating…' : `Move to ${humanize(status)}`}
              </Button>
            ))}
          </div>
        )}
      </div>
    </Card>
  );
}

function BolPanel({
  load,
  onError,
  onGenerated,
}: {
  load: Load;
  onError: (message: string) => void;
  onGenerated: () => void;
}) {
  const [generating, setGenerating] = useState(false);
  const [downloading, setDownloading] = useState(false);
  const [generated, setGenerated] = useState(false);

  async function generate() {
    setGenerating(true);
    try {
      await billOfLading.generate(load.id);
      setGenerated(true);
      onGenerated();
    } catch (err) {
      onError(toMessage(err));
    } finally {
      setGenerating(false);
    }
  }

  async function downloadPdf() {
    setDownloading(true);
    try {
      const blob = await billOfLading.downloadPdf(load.id);
      triggerBrowserDownload(blob, `BOL-load-${load.id}.pdf`);
    } catch (err) {
      // 404 = not generated yet; 409 = still rendering (async in prod).
      onError(toMessage(err));
    } finally {
      setDownloading(false);
    }
  }

  return (
    <Card>
      <SectionHeading>Bill of Lading</SectionHeading>
      <div className="space-y-3 px-4 py-4">
        <p className="text-sm text-slate-500 dark:text-slate-400">
          Generate a BOL from this load, then download the PDF. Data is snapshotted at generation
          time.
        </p>
        <div className="flex flex-col gap-2">
          <Button onClick={generate} disabled={generating}>
            {generating ? 'Generating…' : generated ? 'Regenerate BOL' : 'Generate BOL'}
          </Button>
          <Button variant="secondary" onClick={downloadPdf} disabled={downloading}>
            {downloading ? 'Preparing…' : 'Download PDF'}
          </Button>
        </div>
      </div>
    </Card>
  );
}

function triggerBrowserDownload(blob: Blob, filename: string) {
  const url = URL.createObjectURL(blob);
  const anchor = document.createElement('a');
  anchor.href = url;
  anchor.download = filename;
  document.body.appendChild(anchor);
  anchor.click();
  anchor.remove();
  URL.revokeObjectURL(url);
}

function SectionHeading({ children }: { children: React.ReactNode }) {
  return (
    <div className="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
      <h2 className="text-sm font-semibold text-slate-700 dark:text-slate-200">{children}</h2>
    </div>
  );
}

function Detail({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div>
      <dt className="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
        {label}
      </dt>
      <dd className="mt-0.5 text-sm text-slate-800 dark:text-slate-100">{value}</dd>
    </div>
  );
}

function formatLocation(loc: NonNullable<Load['origin']>): string {
  return [loc.city, loc.state].filter(Boolean).join(', ') || loc.name || '—';
}
