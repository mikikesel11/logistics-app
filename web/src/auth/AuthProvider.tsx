import { useCallback, useEffect, useMemo, useState } from 'react';
import { clearToken, getToken, setToken } from '@/api/client';
import { auth } from '@/api/resources';
import type { User } from '@/api/types';
import { AuthContext, type AuthContextValue } from './context';

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  // On mount, restore the session from a persisted token (if any).
  useEffect(() => {
    let active = true;

    async function restore() {
      if (!getToken()) {
        setLoading(false);
        return;
      }
      try {
        const me = await auth.me();
        if (active) setUser(me);
      } catch {
        // Token invalid/expired — drop it and fall back to the login screen.
        clearToken();
        if (active) setUser(null);
      } finally {
        if (active) setLoading(false);
      }
    }

    void restore();
    return () => {
      active = false;
    };
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    const result = await auth.login(email, password);
    setToken(result.token);
    setUser(result.user);
  }, []);

  const logout = useCallback(async () => {
    try {
      await auth.logout();
    } catch {
      // Best-effort server-side revoke; clear locally regardless.
    } finally {
      clearToken();
      setUser(null);
    }
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({ user, loading, login, logout }),
    [user, loading, login, logout],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
