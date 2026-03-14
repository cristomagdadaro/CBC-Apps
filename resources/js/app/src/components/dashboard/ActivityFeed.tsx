import { motion } from 'framer-motion';
import {
  Plus,
  Edit,
  Trash2,
  CheckCircle,
  Send,
  FileText,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import type { ActivityItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { formatDistanceToNow } from 'date-fns';

const actionIcons: Record<string, React.ElementType> = {
  create: Plus,
  update: Edit,
  delete: Trash2,
  approve: CheckCircle,
  submit: Send,
};

const actionColors: Record<string, string> = {
  create: 'bg-blue-100 text-blue-600',
  update: 'bg-yellow-100 text-yellow-600',
  delete: 'bg-red-100 text-red-600',
  approve: 'bg-green-100 text-green-600',
  submit: 'bg-purple-100 text-purple-600',
};

interface ActivityFeedProps {
  activities: ActivityItem[];
  maxItems?: number;
  className?: string;
}

export function ActivityFeed({ activities, maxItems = 8, className }: ActivityFeedProps) {
  const displayActivities = activities.slice(0, maxItems);

  return (
    <Card className={cn('overflow-hidden', className)}>
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between">
          <CardTitle className="text-lg font-semibold">Recent Activity</CardTitle>
          <Button variant="ghost" size="sm" className="text-primary-600 hover:text-primary-700">
            View All
          </Button>
        </div>
      </CardHeader>
      
      <CardContent className="p-0">
        <div className="divide-y divide-gray-100">
          {displayActivities.length === 0 ? (
            <div className="px-6 py-8 text-center text-gray-500">
              <FileText className="w-8 h-8 mx-auto mb-2 opacity-50" />
              <p className="text-sm">No recent activity</p>
            </div>
          ) : (
            displayActivities.map((activity, index) => {
              const ActionIcon = actionIcons[activity.type] || Plus;
              
              return (
                <motion.div
                  key={activity.id}
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ duration: 0.3, delay: index * 0.05 }}
                  className="flex items-start gap-3 px-6 py-4 hover:bg-gray-50 transition-colors"
                >
                  {/* User Avatar */}
                  <img
                    src={activity.user.avatar || `https://api.dicebear.com/7.x/avataaars/svg?seed=${activity.user.id}`}
                    alt={activity.user.name}
                    className="w-8 h-8 rounded-full bg-gray-100 flex-shrink-0"
                  />
                  
                  <div className="flex-1 min-w-0">
                    <p className="text-sm text-gray-900">
                      <span className="font-medium">{activity.user.name}</span>
                      {' '}<span className="text-gray-500">{activity.action}</span>{' '}
                      <span className="font-medium">{activity.target}</span>
                    </p>
                    <p className="text-xs text-gray-400 mt-1">
                      {formatDistanceToNow(new Date(activity.timestamp), { addSuffix: true })}
                    </p>
                  </div>
                  
                  {/* Action Icon */}
                  <div className={cn('p-1.5 rounded-lg flex-shrink-0', actionColors[activity.type])}>
                    <ActionIcon className="w-3.5 h-3.5" />
                  </div>
                </motion.div>
              );
            })
          )}
        </div>
      </CardContent>
    </Card>
  );
}
