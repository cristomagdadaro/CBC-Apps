import { motion } from 'framer-motion';
import {
  ClipboardList,
  Car,
  FileText,
  CheckCircle,
  XCircle,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import type { PendingItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { formatDistanceToNow } from 'date-fns';

const typeIcons: Record<string, React.ElementType> = {
  fes_request: ClipboardList,
  rental: Car,
  form_submission: FileText,
};

const priorityConfig: Record<string, { color: string; label: string }> = {
  low: { color: 'bg-blue-100 text-blue-700', label: 'Low' },
  medium: { color: 'bg-yellow-100 text-yellow-700', label: 'Medium' },
  high: { color: 'bg-orange-100 text-orange-700', label: 'High' },
  urgent: { color: 'bg-red-100 text-red-700', label: 'Urgent' },
};

interface PendingQueueProps {
  items: PendingItem[];
  maxItems?: number;
  className?: string;
}

export function PendingQueue({ items, maxItems = 5, className }: PendingQueueProps) {
  const displayItems = items.slice(0, maxItems);

  return (
    <Card className={cn('overflow-hidden', className)}>
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <CardTitle className="text-lg font-semibold">Pending Approvals</CardTitle>
            {items.length > 0 && (
              <Badge variant="secondary" className="bg-primary-100 text-primary-700">
                {items.length}
              </Badge>
            )}
          </div>
          <Button variant="ghost" size="sm" className="text-primary-600 hover:text-primary-700">
            View All
          </Button>
        </div>
      </CardHeader>
      
      <CardContent className="p-0">
        <div className="divide-y divide-gray-100">
          {displayItems.length === 0 ? (
            <div className="px-6 py-8 text-center">
              <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <CheckCircle className="w-6 h-6 text-green-600" />
              </div>
              <p className="text-sm font-medium text-gray-900">All caught up!</p>
              <p className="text-xs text-gray-500 mt-1">No pending approvals</p>
            </div>
          ) : (
            displayItems.map((item, index) => {
              const Icon = typeIcons[item.type] || FileText;
              const priority = priorityConfig[item.priority];
              
              return (
                <motion.div
                  key={item.id}
                  initial={{ opacity: 0, x: 20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ duration: 0.3, delay: index * 0.05 }}
                  className="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors"
                >
                  {/* Icon */}
                  <div className="w-10 h-10 bg-primary-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <Icon className="w-5 h-5 text-primary-600" />
                  </div>
                  
                  {/* Content */}
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2">
                      <p className="text-sm font-medium text-gray-900 truncate">{item.title}</p>
                      <Badge className={cn('text-xs', priority.color)}>
                        {priority.label}
                      </Badge>
                    </div>
                    <p className="text-xs text-gray-500 mt-0.5">
                      {item.requestor} • {formatDistanceToNow(new Date(item.submittedAt), { addSuffix: true })}
                    </p>
                  </div>
                  
                  {/* Actions */}
                  <div className="flex items-center gap-1">
                    <Button
                      variant="ghost"
                      size="icon"
                      className="h-8 w-8 text-green-600 hover:text-green-700 hover:bg-green-50"
                    >
                      <CheckCircle className="w-4 h-4" />
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      className="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50"
                    >
                      <XCircle className="w-4 h-4" />
                    </Button>
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
