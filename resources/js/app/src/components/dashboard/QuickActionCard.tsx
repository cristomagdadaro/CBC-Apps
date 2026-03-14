import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import {
  CalendarPlus,
  CheckCircle,
  PlusCircle,
  Award,
  Package,
  BarChart3,
  ArrowRight,
  type LucideIcon,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { Card, CardContent } from '@/components/ui/card';

const iconMap: Record<string, LucideIcon> = {
  'calendar-plus': CalendarPlus,
  'check-circle': CheckCircle,
  'plus-circle': PlusCircle,
  'award': Award,
  'package': Package,
  'barchart': BarChart3,
};

const colorMap: Record<string, { bg: string; icon: string; hover: string }> = {
  blue: {
    bg: 'bg-blue-50',
    icon: 'text-blue-600',
    hover: 'hover:bg-blue-100',
  },
  green: {
    bg: 'bg-green-50',
    icon: 'text-green-600',
    hover: 'hover:bg-green-100',
  },
  purple: {
    bg: 'bg-purple-50',
    icon: 'text-purple-600',
    hover: 'hover:bg-purple-100',
  },
  orange: {
    bg: 'bg-orange-50',
    icon: 'text-orange-600',
    hover: 'hover:bg-orange-100',
  },
  teal: {
    bg: 'bg-teal-50',
    icon: 'text-teal-600',
    hover: 'hover:bg-teal-100',
  },
  primary: {
    bg: 'bg-primary-50',
    icon: 'text-primary-600',
    hover: 'hover:bg-primary-100',
  },
};

interface QuickActionCardProps {
  title: string;
  description?: string;
  icon: string;
  href: string;
  color?: string;
  className?: string;
}

export function QuickActionCard({
  title,
  description,
  icon,
  href,
  color = 'primary',
  className,
}: QuickActionCardProps) {
  const Icon = iconMap[icon] || PlusCircle;
  const colors = colorMap[color] || colorMap.primary;

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, ease: [0.16, 1, 0.3, 1] }}
      whileHover={{ y: -4, transition: { duration: 0.2 } }}
    >
      <Link to={href}>
        <Card className={cn(
          'overflow-hidden border-0 shadow-md hover:shadow-lg transition-all cursor-pointer group',
          className
        )}>
          <CardContent className="p-5">
            <div className="flex items-start gap-4">
              <div className={cn('p-3 rounded-xl transition-colors', colors.bg, colors.hover)}>
                <Icon className={cn('w-5 h-5', colors.icon)} />
              </div>
              
              <div className="flex-1 min-w-0">
                <h3 className="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">
                  {title}
                </h3>
                {description && (
                  <p className="mt-1 text-sm text-gray-500 line-clamp-2">{description}</p>
                )}
              </div>
              
              <ArrowRight className="w-5 h-5 text-gray-400 group-hover:text-primary-600 group-hover:translate-x-1 transition-all flex-shrink-0" />
            </div>
          </CardContent>
        </Card>
      </Link>
    </motion.div>
  );
}
