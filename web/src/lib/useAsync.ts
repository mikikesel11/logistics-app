import { useCallback, useEffect, useState } from 'react';
import { ApiRequestError } from '@/api/client';

interface AsyncState<T> {
  data: T | null;
  loading: boolean;
  error: string | null;
}

/** Turn any thrown value into a user-facing message. */
export function toMessage(err: unknown): string {
  if (err instanceof ApiRequestError) return err.message;
  if (err instanceof Error) return err.message;
  return 'Something went wrong.';
}

/**
 * Run an async loader on mount, whenever `reload()` is called, and whenever a
 * value in `deps` changes. Handles the loading/error/data triad and ignores
 * results from stale calls (last-write-wins on rapid changes).
 */
export function useAsync<T>(
  loader: () => Promise<T>,
  deps: React.DependencyList = [],
): AsyncState<T> & { reload: () => void } {
  const [state, setState] = useState<AsyncState<T>>({
    data: null,
    loading: true,
    error: null,
  });
  const [nonce, setNonce] = useState(0);

  const reload = useCallback(() => setNonce((n) => n + 1), []);

  useEffect(() => {
    let active = true;
    setState((s) => ({ ...s, loading: true, error: null }));

    loader()
      .then((data) => {
        if (active) setState({ data, loading: false, error: null });
      })
      .catch((err) => {
        if (active) setState({ data: null, loading: false, error: toMessage(err) });
      });

    return () => {
      active = false;
    };
    // `loader` is recreated per render, so it's intentionally excluded; `deps`
    // (caller-provided) and `nonce` (explicit reload) drive refetches.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [nonce, ...deps]);

  return { ...state, reload };
}
