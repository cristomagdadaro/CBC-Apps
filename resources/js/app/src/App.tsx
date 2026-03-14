import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { Toaster } from 'react-hot-toast';
import { AnimatePresence } from 'framer-motion';

// Contexts
import { AuthProvider, useAuth } from '@/contexts/AuthContext';

// Layouts
import { AppLayout } from '@/components/layout/AppLayout';

// Public Pages
import { LandingPage } from '@/pages/public/LandingPage';

// Auth Pages
import { LoginPage } from '@/pages/auth/LoginPage';

// Dashboard Pages
import { DashboardPage } from '@/pages/dashboard/DashboardPage';

// Form Pages
import { FormsListPage } from '@/pages/forms/FormsListPage';
import { FormBuilderPage } from '@/pages/forms/FormBuilderPage';
import { FormSubmissionsPage } from '@/pages/forms/FormSubmissionsPage';

// Inventory Pages
import { ItemsListPage } from '@/pages/inventory/ItemsListPage';
import { ItemDetailPage } from '@/pages/inventory/ItemDetailPage';
import { TransactionsPage } from '@/pages/inventory/TransactionsPage';
import { BarcodesPage } from '@/pages/inventory/BarcodesPage';

// Laboratory Pages
import { EquipmentShowcasePage } from '@/pages/laboratory/EquipmentShowcasePage';
import { UsageLoggerPage } from '@/pages/laboratory/UsageLoggerPage';
import { MonitoringPage } from '@/pages/laboratory/MonitoringPage';

// FES Pages
import { GuestRequestPage } from '@/pages/fes/GuestRequestPage';
import { ApprovalQueuePage } from '@/pages/fes/ApprovalQueuePage';

// Rental Pages
import { VehicleRentalPage } from '@/pages/rentals/VehicleRentalPage';
import { VenueRentalPage } from '@/pages/rentals/VenueRentalPage';

// Certificate Pages
import { CertificateGeneratorPage } from '@/pages/certificates/CertificateGeneratorPage';

// Settings Pages
import { UsersPage } from '@/pages/settings/UsersPage';
import { RolesPage } from '@/pages/settings/RolesPage';

// Protected Route Component
function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuth();
  
  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }
  
  return <>{children}</>;
}

function AppRoutes() {
  return (
    <AnimatePresence mode="wait">
      <Routes>
        {/* Public Routes */}
        <Route path="/" element={<LandingPage />} />
        <Route path="/login" element={<LoginPage />} />
        
        {/* Guest Routes */}
        <Route path="/forms/event" element={<div>Guest Event Form</div>} />
        <Route path="/forms/request-to-use" element={<GuestRequestPage />} />
        
        {/* Protected Routes */}
        <Route element={
          <ProtectedRoute>
            <AppLayout />
          </ProtectedRoute>
        }>
          {/* Dashboard */}
          <Route path="/dashboard" element={<DashboardPage />} />
          
          {/* Forms */}
          <Route path="/forms" element={<FormsListPage />} />
          <Route path="/forms/builder" element={<FormBuilderPage />} />
          <Route path="/forms/builder/:id" element={<FormBuilderPage />} />
          <Route path="/forms/submissions/:id" element={<FormSubmissionsPage />} />
          
          {/* Inventory */}
          <Route path="/inventory/items" element={<ItemsListPage />} />
          <Route path="/inventory/items/:id" element={<ItemDetailPage />} />
          <Route path="/inventory/transactions" element={<TransactionsPage />} />
          <Route path="/inventory/barcodes" element={<BarcodesPage />} />
          
          {/* Laboratory */}
          <Route path="/laboratory/equipments" element={<EquipmentShowcasePage />} />
          <Route path="/laboratory/usage-logger" element={<UsageLoggerPage />} />
          <Route path="/laboratory/monitoring" element={<MonitoringPage />} />
          
          {/* FES */}
          <Route path="/fes/approvals" element={<ApprovalQueuePage />} />
          
          {/* Rentals */}
          <Route path="/rentals/vehicle" element={<VehicleRentalPage />} />
          <Route path="/rentals/venue" element={<VenueRentalPage />} />
          
          {/* Certificates */}
          <Route path="/certificates" element={<CertificateGeneratorPage />} />
          
          {/* Settings */}
          <Route path="/settings/users" element={<UsersPage />} />
          <Route path="/settings/roles" element={<RolesPage />} />
        </Route>
        
        {/* Fallback */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </AnimatePresence>
  );
}

function App() {
  return (
    <AuthProvider>
      <Router>
        <AppRoutes />
        <Toaster
          position="top-right"
          toastOptions={{
            duration: 5000,
            style: {
              background: '#fff',
              color: '#1f2937',
              border: '1px solid #e5e7eb',
              borderRadius: '8px',
              padding: '12px 16px',
              boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
            },
            success: {
              iconTheme: {
                primary: '#22C55E',
                secondary: '#fff',
              },
            },
            error: {
              iconTheme: {
                primary: '#EF4444',
                secondary: '#fff',
              },
            },
          }}
        />
      </Router>
    </AuthProvider>
  );
}

export default App;
