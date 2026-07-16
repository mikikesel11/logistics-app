import { Navigate, Route, Routes } from 'react-router-dom';
import { useAuth } from '@/auth/useAuth';
import { AppLayout } from '@/components/AppLayout';
import { Spinner } from '@/components/ui/misc';
import { LoginPage } from '@/pages/LoginPage';
import { DashboardPage } from '@/pages/DashboardPage';
import { CustomersPage } from '@/pages/CustomersPage';
import { CarriersPage } from '@/pages/CarriersPage';
import { LocationsPage } from '@/pages/LocationsPage';
import { LoadsPage } from '@/pages/LoadsPage';
import { LoadDetailPage } from '@/pages/LoadDetailPage';

export function App() {
  const { user, loading } = useAuth();

  if (loading) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <Spinner label="Starting…" />
      </div>
    );
  }

  if (!user) {
    // Unauthenticated: only the login screen is reachable.
    return (
      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route path="*" element={<Navigate to="/login" replace />} />
      </Routes>
    );
  }

  return (
    <Routes>
      <Route element={<AppLayout />}>
        <Route path="/" element={<DashboardPage />} />
        <Route path="/customers" element={<CustomersPage />} />
        <Route path="/carriers" element={<CarriersPage />} />
        <Route path="/locations" element={<LocationsPage />} />
        <Route path="/loads" element={<LoadsPage />} />
        <Route path="/loads/:id" element={<LoadDetailPage />} />
      </Route>
      {/* Already authenticated — bounce /login back to the dashboard. */}
      <Route path="/login" element={<Navigate to="/" replace />} />
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
