import { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import {
  LayoutDashboard,
  Calendar,
  Package,
  Microscope,
  ClipboardList,
  Car,
  Award,
  Settings,
  Users,
  Shield,
  Sliders,
  List,
  Plus,
  FileText,
  Box,
  ArrowLeftRight,
  QrCode,
  Truck,
  Cpu,
  Clock,
  Activity,
  Building,
  Bed,
  ChevronRight,
  X,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { useAuth } from '@/contexts/AuthContext';
import { SIDEBAR_NAVIGATION } from '@/lib/constants';
import type { UserRole } from '@/types';

const iconMap: Record<string, React.ElementType> = {
  LayoutDashboard,
  Calendar,
  Package,
  Microscope,
  ClipboardList,
  Car,
  Award,
  Settings,
  Users,
  Shield,
  Sliders,
  List,
  Plus,
  FileText,
  Box,
  ArrowLeftRight,
  QrCode,
  Truck,
  Cpu,
  Clock,
  Activity,
  Building,
  Bed,
};

interface SidebarProps {
  isOpen: boolean;
  onClose: () => void;
  isCollapsed: boolean;
}

interface NavItemProps {
  item: typeof SIDEBAR_NAVIGATION[0];
  isCollapsed: boolean;
  depth?: number;
}

function NavItem({ item, isCollapsed, depth = 0 }: NavItemProps) {
  const location = useLocation();
  const [isExpanded, setIsExpanded] = useState(false);
  const hasChildren = item.children && item.children.length > 0;
  const isActive = location.pathname === item.href || location.pathname.startsWith(item.href + '/');
  const Icon = iconMap[item.icon] || LayoutDashboard;

  const handleClick = () => {
    if (hasChildren) {
      setIsExpanded(!isExpanded);
    }
  };

  return (
    <div className={cn('w-full', depth > 0 && 'ml-2')}>      
      <Link
        to={item.href}
        onClick={hasChildren ? handleClick : undefined}
        className={cn(
          'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group',
          'hover:bg-primary-50',
          isActive && 'bg-primary-50 text-primary-600',
          !isActive && 'text-gray-600',
          isCollapsed && 'justify-center px-2'
        )}
      >
        <Icon className={cn(
          'w-5 h-5 flex-shrink-0 transition-transform duration-200',
          'group-hover:scale-110',
          isActive && 'text-primary-600'
        )} />
        
        {!isCollapsed && (
          <>
            <span className="flex-1 text-sm font-medium truncate">{item.title}</span>
            {hasChildren && (
              <motion.div
                animate={{ rotate: isExpanded ? 90 : 0 }}
                transition={{ duration: 0.2 }}
              >
                <ChevronRight className="w-4 h-4" />
              </motion.div>
            )}
          </>
        )}
        
        {isCollapsed && isActive && (
          <div className="absolute left-0 w-1 h-8 bg-primary-500 rounded-r-full" />
        )}
      </Link>

      <AnimatePresence>
        {!isCollapsed && hasChildren && isExpanded && (
          <motion.div
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: 'auto', opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{ duration: 0.2 }}
            className="overflow-hidden"
          >
            <div className="pt-1 space-y-0.5">
              {item.children?.map((child) => (
                <NavItem
                  key={child.href}
                  item={{ ...child, icon: child.icon || 'Circle', roles: item.roles || [] }}
                  isCollapsed={isCollapsed}
                  depth={depth + 1}
                />
              ))}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}

export function Sidebar({ isOpen, onClose, isCollapsed }: SidebarProps) {
  const { user, hasRole } = useAuth();

  const filteredNavigation = SIDEBAR_NAVIGATION.filter(item => {
    if (!item.roles) return true;
    return hasRole(item.roles as UserRole[]);
  });

  return (
    <>
      {/* Mobile Overlay */}
      <AnimatePresence>
        {isOpen && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 0.2 }}
            className="fixed inset-0 bg-black/50 z-40 lg:hidden"
            onClick={onClose}
          />
        )}
      </AnimatePresence>

      {/* Sidebar */}
      <motion.aside
        initial={false}
        animate={{
          width: isCollapsed ? 72 : 280,
          x: isOpen || window.innerWidth >= 1024 ? 0 : -280,
        }}
        transition={{ duration: 0.3, ease: [0.4, 0, 0.2, 1] }}
        className={cn(
          'fixed left-0 top-0 h-full bg-white border-r border-gray-200 z-50',
          'flex flex-col',
          'lg:translate-x-0 lg:static'
        )}
      >
        {/* Logo */}
        <div className={cn(
          'flex items-center gap-3 px-4 h-16 border-b border-gray-200',
          isCollapsed && 'justify-center px-2'
        )}>
          <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center flex-shrink-0">
            <span className="text-white font-bold text-lg">C</span>
          </div>
          {!isCollapsed && (
            <div className="flex-1 min-w-0">
              <h1 className="font-semibold text-gray-900 truncate">OneCBC Portal</h1>
              <p className="text-xs text-gray-500 truncate">v2.0.0</p>
            </div>
          )}
          
          {/* Close button for mobile */}
          <button
            onClick={onClose}
            className="lg:hidden p-2 hover:bg-gray-100 rounded-lg"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Navigation */}
        <nav className="flex-1 overflow-y-auto py-4 px-3 space-y-1">
          {filteredNavigation.map((item) => (
            <NavItem
              key={item.href}
              item={item}
              isCollapsed={isCollapsed}
            />
          ))}
        </nav>

        {/* User Profile */}
        {!isCollapsed && user && (
          <div className="p-4 border-t border-gray-200">
            <div className="flex items-center gap-3">
              <img
                src={user.avatar || `https://api.dicebear.com/7.x/avataaars/svg?seed=${user.id}`}
                alt={user.name}
                className="w-10 h-10 rounded-full bg-gray-100"
              />
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 truncate">{user.name}</p>
                <p className="text-xs text-gray-500 truncate capitalize">{user.role.replace('_', ' ')}</p>
              </div>
            </div>
          </div>
        )}
      </motion.aside>
    </>
  );
}
