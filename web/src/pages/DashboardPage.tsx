import { useNavigate } from 'react-router-dom';
import { carriers, customers, loads } from '@/api/resources';
import { PageHeader } from '@/components/PageHeader';
import { Card, EmptyState, ErrorBanner, Spinner, StatusBadge } from '@/components/ui/misc';
import { Table, type Column } from '@/components/ui/Table';
import { useAsync } from '@/lib/useAsync';
import { formatCents, formatDate } from '@/lib/utils';
import type { Load } from '@/api/types';

interface DashboardData {
  customerCount: number;
  carrierCount: number;
  loadTotal: number;
  recentLoads: Load[];
}

export function DashboardPage() {
  const navigate = useNavigate();
  const { data, loading, error } = useAsync<DashboardData>(async () => {
    const [customerList, carrierList, loadResult] = await Promise.all([
      customers.list(),
      carriers.list(),
      loads.list({ per_page: 5 }),
    ]);
    return {
      customerCount: customerList.length,
      carrierCount: carrierList.length,
      loadTotal: loadResult.meta.total,
      recentLoads: loadResult.loads,
    };
  });

  const columns: Column<Load>[] = [
    { header: 'Reference', cell: (l) => l.reference ?? `Load #${l.id}` },
    { header: 'Status', cell: (l) => <StatusBadge status={l.status} /> },
    { header: 'Customer', cell: (l) => l.customer?.name ?? '—' },
    { header: 'Pickup', cell: (l) => formatDate(l.pickup_date) },
    {
      header: 'Margin',
      cell: (l) => formatCents(l.margin_cents),
      className: 'text-right tabular-nums',
    },
  ];

  return (
    <div>
      <PageHeader title="Dashboard" subtitle="Your brokerage at a glance" />

      {error && <ErrorBanner message={error} />}
      {loading && <Spinner />}

      {data && (
        <>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <StatCard label="Customers" value={data.customerCount} />
            <StatCard label="Carriers" value={data.carrierCount} />
            <StatCard label="Loads" value={data.loadTotal} />
          </div>

          <Card className="mt-6">
            <div className="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
              <h2 className="text-sm font-semibold text-slate-700 dark:text-slate-200">
                Recent loads
              </h2>
            </div>
            {data.recentLoads.length === 0 ? (
              <EmptyState title="No loads yet" description="Create a load to get started." />
            ) : (
              <Table
                columns={columns}
                rows={data.recentLoads}
                rowKey={(l) => l.id}
                onRowClick={(l) => navigate(`/loads/${l.id}`)}
              />
            )}
          </Card>
        </>
      )}
    </div>
  );
}

function StatCard({ label, value }: { label: string; value: number }) {
  return (
    <Card className="px-5 py-4">
      <p className="text-sm text-slate-500 dark:text-slate-400">{label}</p>
      <p className="mt-1 text-3xl font-semibold text-slate-900 tabular-nums dark:text-slate-100">
        {value}
      </p>
    </Card>
  );
}
