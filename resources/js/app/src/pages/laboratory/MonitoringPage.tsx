import React, { useState } from 'react';
import { motion } from 'framer-motion';
import {
  Activity,
  FlaskConical,
  CheckCircle,
  Clock,
  AlertTriangle,
  Calendar,
  TrendingUp,
  Users,
  Microscope,
  ArrowRight,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { PageHeader } from '@/components/layout/PageHeader';
import { mockExperiments, mockEquipmentUsage } from '@/lib/mockData';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { format, differenceInDays } from 'date-fns';

const statusConfig: Record<string, { color: string; icon: React.ElementType; label: string }> = {
  scheduled: { color: 'bg-purple-100 text-purple-700', icon: Calendar, label: 'Scheduled' },
  in_progress: { color: 'bg-blue-100 text-blue-700', icon: Activity, label: 'In Progress' },
  completed: { color: 'bg-green-100 text-green-700', icon: CheckCircle, label: 'Completed' },
  delayed: { color: 'bg-orange-100 text-orange-700', icon: AlertTriangle, label: 'Delayed' },
  cancelled: { color: 'bg-gray-100 text-gray-500', icon: Clock, label: 'Cancelled' },
};

export function MonitoringPage() {
  const [selectedExperiment, setSelectedExperiment] = useState<typeof mockExperiments[0] | null>(null);

  const stats = {
    active: mockExperiments.filter((e) => e.status === 'in_progress').length,
    completed: mockExperiments.filter((e) => e.status === 'completed').length,
    scheduled: mockExperiments.filter((e) => e.status === 'scheduled').length,
    delayed: mockExperiments.filter((e) => e.status === 'delayed').length,
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Experiment Monitoring"
        subtitle="Monitor and track ongoing experiments in the laboratory"
      />

      {/* Stats */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {[
          { label: 'Active', value: stats.active, color: 'bg-blue-500', icon: Activity },
          { label: 'Completed', value: stats.completed, color: 'bg-green-500', icon: CheckCircle },
          { label: 'Scheduled', value: stats.scheduled, color: 'bg-purple-500', icon: Calendar },
          { label: 'Delayed', value: stats.delayed, color: 'bg-orange-500', icon: AlertTriangle },
        ].map((stat) => (
          <Card key={stat.label}>
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-500">{stat.label}</p>
                  <p className="text-2xl font-bold">{stat.value}</p>
                </div>
                <div className={cn('w-10 h-10 rounded-lg flex items-center justify-center', stat.color.replace('bg-', 'bg-opacity-20 bg-'))}>
                  <stat.icon className={cn('w-5 h-5', stat.color.replace('bg-', 'text-'))} />
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {/* Experiments List */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {mockExperiments.map((experiment, index) => {
          const status = statusConfig[experiment.status];
          const StatusIcon = status.icon;
          const progress = experiment.status === 'completed' ? 100 : experiment.status === 'in_progress' ? 65 : 0;
          const daysRemaining = experiment.expectedEndDate
            ? differenceInDays(new Date(experiment.expectedEndDate), new Date())
            : null;

          return (
            <motion.div
              key={experiment.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.3, delay: index * 0.1 }}
            >
              <Card className="hover:shadow-md transition-shadow">
                <CardContent className="p-5">
                  <div className="flex items-start justify-between mb-4">
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 bg-primary-50 rounded-lg flex items-center justify-center">
                        <FlaskConical className="w-5 h-5 text-primary-600" />
                      </div>
                      <div>
                        <h3 className="font-semibold text-gray-900">{experiment.title}</h3>
                        <p className="text-sm text-gray-500">{experiment.projectCode}</p>
                      </div>
                    </div>
                    <Badge className={status.color}>
                      <StatusIcon className="w-3 h-3 mr-1" />
                      {status.label}
                    </Badge>
                  </div>

                  <p className="text-sm text-gray-600 mb-4 line-clamp-2">
                    {experiment.description}
                  </p>

                  <div className="space-y-3">
                    <div className="flex items-center justify-between text-sm">
                      <span className="text-gray-500">Progress</span>
                      <span className="font-medium">{progress}%</span>
                    </div>
                    <Progress value={progress} className="h-2" />
                  </div>

                  <div className="flex items-center justify-between mt-4 pt-4 border-t">
                    <div className="flex items-center gap-4 text-sm">
                      <div className="flex items-center gap-1.5">
                        <Users className="w-4 h-4 text-gray-400" />
                        <span className="text-gray-600">{experiment.researcher.name}</span>
                      </div>
                      {daysRemaining !== null && (
                        <div className="flex items-center gap-1.5">
                          <Clock className="w-4 h-4 text-gray-400" />
                          <span className={cn(
                            'text-gray-600',
                            daysRemaining < 0 && 'text-red-600'
                          )}>
                            {daysRemaining < 0
                              ? `${Math.abs(daysRemaining)} days overdue`
                              : `${daysRemaining} days left`}
                          </span>
                        </div>
                      )}
                    </div>
                    <Button variant="ghost" size="sm" className="text-primary-600">
                      Details
                      <ArrowRight className="w-4 h-4 ml-1" />
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </motion.div>
          );
        })}
      </div>

      {/* Alerts */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base flex items-center gap-2">
            <AlertTriangle className="w-5 h-5 text-orange-500" />
            Alerts & Notifications
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            <div className="flex items-center gap-3 p-3 bg-red-50 rounded-lg border border-red-100">
              <AlertTriangle className="w-5 h-5 text-red-500 flex-shrink-0" />
              <div className="flex-1">
                <p className="text-sm font-medium text-red-800">Temperature alert in Cold Room B</p>
                <p className="text-xs text-red-600">Temperature exceeded threshold (8°C)</p>
              </div>
              <Button variant="ghost" size="sm" className="text-red-600">
                Acknowledge
              </Button>
            </div>
            <div className="flex items-center gap-3 p-3 bg-yellow-50 rounded-lg border border-yellow-100">
              <Microscope className="w-5 h-5 text-yellow-500 flex-shrink-0" />
              <div className="flex-1">
                <p className="text-sm font-medium text-yellow-800">Equipment maintenance due</p>
                <p className="text-xs text-yellow-600">PCR Thermal Cycler maintenance scheduled in 3 days</p>
              </div>
              <Button variant="ghost" size="sm" className="text-yellow-600">
                Schedule
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
