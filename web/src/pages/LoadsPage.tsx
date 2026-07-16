import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { loads } from '@/api/resources';
import { LOAD_STATUSES, type Load, type LoadStatus } from '@/api/types';
import { LoadFormModal } from '@/components/LoadFormModal';
import { PageHeader } from '@/components/PageHeader';
import { Button } from '@/components/ui/Button';
import { Card, EmptyState, ErrorBanner, Spinner, StatusBadge } from '@/components/ui/misc';
import { Table, type Column } from '@/components/ui/Table';
import { useAsync } from '@/lib/useAsync';
import { cn, formatCents, formatDate, humanize } from '@/lib/utils';

export function LoadsPage() {
  const navigate = useNavigate();
  const [statusFilter, setStatusFilter] = useState<LoadStatus | 'all'>('all');
  const [creating, setCreating] = useState(false);

  const { data, loading, error } = useAsync(
    () => loads.list({ status: statusFilter === 'all' ? undefined : statusFilter, per_page: 100 }),
    [statusFilter],
  );

  const columns: Column<Load>[] = [
    { header: 'Reference', cell: (l) => l.reference ?? `Load #${l.id}` },
    { header: 'Status', cell: (l) => <StatusBadge status={l.status} /> },
    { header: 'Customer', cell: (l) => l.customer?.name ?? '—' },
    { header: 'Carrier', cell: (l) => l.carrier?.name ?? '—' },
    { header: 'Commodity', cell: (l) => l.commodity ?? '—' },
    { header: 'Pickup', cell: (l) => formatDate(l.pickup_date) },
    {
      header: 'Rate',
      className: 'text-right tabular-nums',
      cell: (l) => formatCents(l.customer_rate_cents),
    },
    {
      header: 'Margin',
      className: 'text-right tabular-nums',
      cell: (l) => formatCents(l.margin_cents),
    },
  ];

  return (
    <div>
      <PageHeader
        title="Loads"
        subtitle="Your internal load board"
        action={<Button onClick={() => setCreating(true)}>+ New load</Button>}
      />

      <div className="mb-4 flex flex-wrap gap-1.5">
        <FilterChip active={statusFilter === 'all'} onClick={() => setStatusFilter('all')}>
          All
        </FilterChip>
        {LOAD_STATUSES.map((status) => (
          <FilterChip
            key={status}
            active={statusFilter === status}
            onClick={() => setStatusFilter(status)}
          >
            {humanize(status)}
          </FilterChip>
        ))}
      </div>

      {error && <ErrorBanner message={error} />}

      <Card>
        {loading ? (
          <Spinner />
        ) : !data || data.loads.length === 0 ? (
          <EmptyState
            title="No loads found"
            description={
              statusFilter === 'all'
                ? 'Create a load to start moving freight.'
                : `No loads with status "${humanize(statusFilter)}".`
            }
            action={<Button onClick={() => setCreating(true)}>+ New load</Button>}
          />
        ) : (
          <Table
            columns={columns}
            rows={data.loads}
            rowKey={(l) => l.id}
            onRowClick={(l) => navigate(`/loads/${l.id}`)}
          />
        )}
      </Card>

      {creating && (
        <LoadFormModal
          title="New load"
          onClose={() => setCreating(false)}
          onSaved={(load) => {
            setCreating(false);
            navigate(`/loads/${load.id}`);
          }}
          submit={(input) => loads.create(input)}
        />
      )}
    </div>
  );
}

function FilterChip({
  active,
  onClick,
  children,
}: {
  active: boolean;
  onClick: () => void;
  children: React.ReactNode;
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={cn(
        'rounded-full px-3 py-1 text-xs font-medium transition-colors',
        active
          ? 'bg-brand-600 text-white'
          : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
      )}
    >
      {children}
    </button>
  );
}
