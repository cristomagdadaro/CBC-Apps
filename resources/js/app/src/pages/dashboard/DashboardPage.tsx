import { motion } from 'framer-motion';
import {
  CalendarPlus,
  CheckCircle,
  PlusCircle,
  Award,
  Package,
  BarChart3,
  FileText,
  Send,
  Eye,
  Car,
  Truck,
  Building,
  Microscope,
  FlaskConical,
  ClipboardList,
  Activity,
  Clock,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { useAuth } from '@/contexts/AuthContext';
import { PageHeader } from '@/components/layout/PageHeader';
import { StatCard } from '@/components/dashboard/StatCard';
import { QuickActionCard } from '@/components/dashboard/QuickActionCard';
import { ActivityFeed } from '@/components/dashboard/ActivityFeed';
import { PendingQueue } from '@/components/dashboard/PendingQueue';
import { 
  mockActivities, 
  mockPendingItems, 
  getDashboardStats 
} from '@/lib/mockData';

const containerVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1,
      delayChildren: 0.1,
    },
  },
};

const itemVariants = {
  hidden: { opacity: 0, y: 20 },
  visible: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.4,
      ease: [0.16, 1, 0.3, 1] as const,
    }
  },
};

// Quick actions by role
const quickActionsByRole: Record<string, Array<{
  title: string;
  description?: string;
  icon: string;
  href: string;
  color: string;
}>> = {
  admin: [
    { title: 'Create Event Form', description: 'Set up a new event registration', icon: 'calendar-plus', href: '/forms/builder', color: 'blue' },
    { title: 'Approve Requests', description: 'Review pending approvals', icon: 'check-circle', href: '/fes/approvals', color: 'green' },
    { title: 'Log Transaction', description: 'Record inventory movement', icon: 'plus-circle', href: '/inventory/transactions', color: 'purple' },
    { title: 'Generate Certificate', description: 'Create participant certificates', icon: 'award', href: '/certificates', color: 'orange' },
    { title: 'Manage Inventory', description: 'View and update stock levels', icon: 'package', href: '/inventory/items', color: 'teal' },
    { title: 'View Reports', description: 'Access analytics and reports', icon: 'barchart', href: '/reports', color: 'primary' },
  ],
  lab_manager: [
    { title: 'Log Equipment Use', description: 'Record equipment usage', icon: 'microscope', href: '/laboratory/usage-logger', color: 'blue' },
    { title: 'Approve FES Request', description: 'Review facility requests', icon: 'check-circle', href: '/fes/approvals', color: 'green' },
    { title: 'View Monitoring', description: 'Check experiment status', icon: 'activity', href: '/laboratory/monitoring', color: 'purple' },
    { title: 'Generate Report', description: 'Create usage reports', icon: 'barchart', href: '/reports', color: 'primary' },
  ],
  ict_manager: [
    { title: 'Create Form', description: 'Build new event form', icon: 'calendar-plus', href: '/forms/builder', color: 'blue' },
    { title: 'View Submissions', description: 'Check form responses', icon: 'file-text', href: '/forms/submissions', color: 'green' },
    { title: 'Generate Certificates', description: 'Create batch certificates', icon: 'award', href: '/certificates', color: 'orange' },
    { title: 'View Analytics', description: 'Check form statistics', icon: 'barchart', href: '/forms/analytics', color: 'primary' },
  ],
  admin_assistant: [
    { title: 'Approve Rental', description: 'Review rental requests', icon: 'check-circle', href: '/rentals/vehicle', color: 'green' },
    { title: 'View Calendar', description: 'Check upcoming events', icon: 'calendar-plus', href: '/calendar', color: 'blue' },
    { title: 'Send Reminder', description: 'Notify participants', icon: 'send', href: '/reminders', color: 'purple' },
    { title: 'Update Inventory', description: 'Manage stock levels', icon: 'package', href: '/inventory/items', color: 'teal' },
  ],
  researcher: [
    { title: 'Submit FES Request', description: 'Request lab access', icon: 'clipboard-list', href: '/forms/request-to-use', color: 'blue' },
    { title: 'Register for Event', description: 'Join upcoming events', icon: 'calendar-plus', href: '/forms/event', color: 'green' },
    { title: 'Reserve Equipment', description: 'Book lab equipment', icon: 'microscope', href: '/laboratory/equipments', color: 'purple' },
    { title: 'View Status', description: 'Check request status', icon: 'eye', href: '/profile', color: 'primary' },
  ],
  guest: [
    { title: 'Register for Event', description: 'Join upcoming events', icon: 'calendar-plus', href: '/forms/event', color: 'blue' },
    { title: 'Submit Request', description: 'Request lab access', icon: 'clipboard-list', href: '/forms/request-to-use', color: 'green' },
    { title: 'Browse Equipment', description: 'View lab equipment', icon: 'microscope', href: '/laboratory/equipments', color: 'purple' },
    { title: 'File Report', description: 'Report an issue', icon: 'file-text', href: '/file-report/create-guest', color: 'orange' },
  ],
};

export function DashboardPage() {
  const { user } = useAuth();
  const userRole = user?.role || 'guest';
  
  const stats = getDashboardStats(userRole);
  const quickActions = quickActionsByRole[userRole] || quickActionsByRole.guest;

  return (
    <div className="space-y-6">
      <PageHeader
        title="Dashboard"
        subtitle={`Welcome back, ${user?.name || 'Guest'}`}
      />

      {/* Stats Grid */}
      <motion.div
        variants={containerVariants}
        initial="hidden"
        animate="visible"
        className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
      >
        {stats.map((stat) => (
          <motion.div key={stat.title} variants={itemVariants}>
            <StatCard
              title={stat.title}
              value={stat.value}
              icon={stat.icon}
              trend={stat.trend}
              trendLabel={stat.trendLabel}
              color={stat.color}
            />
          </motion.div>
        ))}
      </motion.div>

      {/* Quick Actions */}
      <div>
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
        >
          {quickActions.map((action) => (
            <motion.div key={action.title} variants={itemVariants}>
              <QuickActionCard
                title={action.title}
                description={action.description}
                icon={action.icon}
                href={action.href}
                color={action.color}
              />
            </motion.div>
          ))}
        </motion.div>
      </div>

      {/* Two Column Layout */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <ActivityFeed activities={mockActivities} />
        <PendingQueue items={mockPendingItems} />
      </div>
    </div>
  );
}
