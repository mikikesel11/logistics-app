import { cn } from '@/lib/utils';

/**
 * Minimal column-driven table. Rows are keyed by `rowKey`; clicking a row (when
 * `onRowClick` is provided) navigates or opens a detail view.
 */
export interface Column<T> {
  header: string;
  cell: (row: T) => React.ReactNode;
  className?: string;
}

interface TableProps<T> {
  columns: Column<T>[];
  rows: T[];
  rowKey: (row: T) => string | number;
  onRowClick?: (row: T) => void;
}

export function Table<T>({ columns, rows, rowKey, onRowClick }: TableProps<T>) {
  return (
    <div className="overflow-x-auto">
      <table className="w-full text-left text-sm">
        <thead>
          <tr className="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:text-slate-400">
            {columns.map((col) => (
              <th key={col.header} className={cn('px-4 py-2.5 font-medium', col.className)}>
                {col.header}
              </th>
            ))}
          </tr>
        </thead>
        <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
          {rows.map((row) => (
            <tr
              key={rowKey(row)}
              onClick={onRowClick ? () => onRowClick(row) : undefined}
              className={cn(
                'text-slate-700 dark:text-slate-200',
                onRowClick && 'cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50',
              )}
            >
              {columns.map((col) => (
                <td key={col.header} className={cn('px-4 py-3', col.className)}>
                  {col.cell(row)}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
