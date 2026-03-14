import React from 'react';
import { motion } from 'framer-motion';
import {
  TrendingUp,
  TrendingDown,
  Users,
  Clock,
  Calendar,
  Microscope,
  CheckCircle,
  FlaskConical,
  ClipboardList,
  Activity,
  FileText,
  Send,
  Award,
  Eye,
  Car,
  Truck,
  Building,
  Package,
  BarChart3,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { useCountUp } from '@/hooks/useCountUp';
import { Card, CardContent } from '@/components/ui/card';

const iconMap: Record<string, React.ElementType> = {
  Users,
  Clock,
  Calendar,
  Microscope,
  CheckCircle,
  FlaskConical,
  ClipboardList,
  Activity,
  FileText,
  Send,
  Award,
  Eye,
  Car,
  Truck,
  Building,
  Package,
  BarChart3,
};

interface StatCardProps {
  title: string;
  value: number;
  icon: string;
  trend?: number;
  trendLabel?: string;
  color?: 'primary' | 'success' | 'warning' | 'error' | 'info';
  className?: string;
}

const colorClasses = {
  primary: {
    bg: 'bg-primary-50',
    icon: 'text-primary-600',
    border: 'border-primary-200',
  },
  success: {
    bg: 'bg-green-50',
    icon: 'text-green-600',
    border: 'border-green-200',
  },
  warning: {
    bg: 'bg-yellow-50',
    icon: 'text-yellow-600',
    border: 'border-yellow-200',
  },
  error: {
    bg: 'bg-red-50',
    icon: 'text-red-600',
    border: 'border-red-200',
  },
  info: {
    bg: 'bg-blue-50',
    icon: 'text-blue-600',
    border: 'border-blue-200',
  },
};

export function StatCard({
  title,
  value,
  icon,
  trend,
  trendLabel,
  color = 'primary',
  className,
}: StatCardProps) {
  const { count } = useCountUp({ end: value, duration: 2000 });
  const Icon = iconMap[icon] || BarChart3;
  const colors = colorClasses[color];

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, ease: [0.16, 1, 0.3, 1] }}
      whileHover={{ y: -4, transition: { duration: 0.2 } }}
    >
      <Card className={cn('overflow-hidden border-0 shadow-md hover:shadow-lg transition-shadow', className)}>
        <CardContent className="p-6">
          <div className="flex items-start justify-between">
            <div className="flex-1">
              <p className="text-sm font-medium text-gray-500">{title}</p>
              <p className="mt-2 text-3xl font-bold text-gray-900">{count.toLocaleString()}</p>
              
              {trend !== undefined && (
                <div className="mt-2 flex items-center gap-1">
                  {trend > 0 ? (
                    <TrendingUp className="w-4 h-4 text-green-500" />
                  ) : trend < 0 ? (
                    <TrendingDown className="w-4 h-4 text-red-500" />
                  ) : (
                    <span className="w-4 h-4 flex items-center justify-center text-gray-400">-</span>
                  )}
                  <span
                    className={cn(
                      'text-sm font-medium',
                      trend > 0 && 'text-green-600',
                      trend < 0 && 'text-red-600',
                      trend === 0 && 'text-gray-500'
                    )}
                  >
                    {trend > 0 ? '+' : ''}{trend}%
                  </span>
                  {trendLabel && (
                    <span className="text-sm text-gray-400">{trendLabel}</span>
                  )}
                </div>
              )}
            </div>
            
            <div className={cn('p-3 rounded-xl', colors.bg)}>
              <Icon className={cn('w-6 h-6', colors.icon)} />
            </div>
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
}
