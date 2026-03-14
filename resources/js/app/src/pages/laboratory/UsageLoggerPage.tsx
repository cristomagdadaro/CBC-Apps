import React, { useState } from 'react';
import { motion } from 'framer-motion';
import {
  Search,
  QrCode,
  Clock,
  Play,
  Square,
  CheckCircle,
  Microscope,
  User,
  FileText,
  AlertCircle,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { PageHeader } from '@/components/layout/PageHeader';
import { mockEquipment, mockEquipmentUsage } from '@/lib/mockData';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { format } from 'date-fns';

export function UsageLoggerPage() {
  const [selectedEquipment, setSelectedEquipment] = useState<string>('');
  const [purpose, setPurpose] = useState('');
  const [projectCode, setProjectCode] = useState('');
  const [isLogging, setIsLogging] = useState(false);
  const [activeUsage, setActiveUsage] = useState<typeof mockEquipmentUsage[0][]>(mockEquipmentUsage);

  const handleStartUsage = () => {
    if (!selectedEquipment || !purpose) return;
    
    const equipment = mockEquipment.find((e) => e.id === selectedEquipment);
    if (!equipment) return;

    const newUsage = {
      id: Date.now().toString(),
      equipmentId: equipment.id,
      equipmentName: equipment.name,
      user: { id: '1', name: 'Current User', email: 'user@example.com', role: 'researcher' as const, isActive: true, createdAt: new Date().toISOString(), updatedAt: new Date().toISOString() },
      purpose,
      projectCode,
      startTime: new Date().toISOString(),
      status: 'active' as const,
    };

    setActiveUsage([newUsage, ...activeUsage]);
    setIsLogging(true);
    setPurpose('');
    setProjectCode('');
  };

  const handleStopUsage = (usageId: string) => {
    setActiveUsage(activeUsage.map((u) =>
      u.id === usageId ? { ...u, status: 'completed', endTime: new Date().toISOString() } : u
    ));
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Equipment Usage Logger"
        subtitle="Track and log laboratory equipment usage"
      />

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Usage Form */}
        <div className="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="text-base flex items-center gap-2">
                <QrCode className="w-5 h-5 text-primary-600" />
                Scan or Select Equipment
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <Select value={selectedEquipment} onValueChange={setSelectedEquipment}>
                  <SelectTrigger className="pl-10">
                    <SelectValue placeholder="Search equipment by name, ID, or scan QR code..." />
                  </SelectTrigger>
                  <SelectContent>
                    {mockEquipment.map((eq) => (
                      <SelectItem key={eq.id} value={eq.id}>
                        <div className="flex items-center gap-2">
                          <Microscope className="w-4 h-4 text-gray-400" />
                          <span>{eq.name}</span>
                          <span className="text-gray-400">-</span>
                          <span className="text-gray-500 text-sm">{eq.model}</span>
                        </div>
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              {selectedEquipment && (
                <motion.div
                  initial={{ opacity: 0, height: 0 }}
                  animate={{ opacity: 1, height: 'auto' }}
                  className="space-y-4 pt-4 border-t"
                >
                  <div>
                    <Label htmlFor="purpose">Purpose *</Label>
                    <Textarea
                      id="purpose"
                      placeholder="Describe the purpose of your equipment usage..."
                      value={purpose}
                      onChange={(e) => setPurpose(e.target.value)}
                      className="mt-1.5"
                    />
                  </div>

                  <div>
                    <Label htmlFor="project">Project Code (optional)</Label>
                    <Input
                      id="project"
                      placeholder="e.g., GEA-2024-023"
                      value={projectCode}
                      onChange={(e) => setProjectCode(e.target.value)}
                      className="mt-1.5"
                    />
                  </div>

                  <Button
                    onClick={handleStartUsage}
                    disabled={!purpose}
                    className="w-full"
                  >
                    <Play className="w-4 h-4 mr-2" />
                    Start Usage Session
                  </Button>
                </motion.div>
              )}
            </CardContent>
          </Card>

          {/* Active Usage */}
          <Card>
            <CardHeader>
              <CardTitle className="text-base flex items-center gap-2">
                <Clock className="w-5 h-5 text-primary-600" />
                Currently Active
                <Badge variant="secondary" className="ml-2">
                  {activeUsage.filter((u) => u.status === 'active').length}
                </Badge>
              </CardTitle>
            </CardHeader>
            <CardContent>
              {activeUsage.filter((u) => u.status === 'active').length === 0 ? (
                <div className="text-center py-8">
                  <div className="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <CheckCircle className="w-6 h-6 text-gray-400" />
                  </div>
                  <p className="text-gray-500">No active usage sessions</p>
                </div>
              ) : (
                <div className="space-y-3">
                  {activeUsage
                    .filter((u) => u.status === 'active')
                    .map((usage) => (
                      <div
                        key={usage.id}
                        className="flex items-center justify-between p-4 bg-primary-50 rounded-lg border border-primary-100"
                      >
                        <div className="flex items-center gap-4">
                          <div className="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                            <Microscope className="w-5 h-5 text-primary-600" />
                          </div>
                          <div>
                            <p className="font-medium text-gray-900">{usage.equipmentName}</p>
                            <p className="text-sm text-gray-500">{usage.purpose}</p>
                            <p className="text-xs text-gray-400 mt-0.5">
                              Started {format(new Date(usage.startTime), 'h:mm a')}
                            </p>
                          </div>
                        </div>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleStopUsage(usage.id)}
                          className="text-red-600 border-red-200 hover:bg-red-50"
                        >
                          <Square className="w-4 h-4 mr-1" />
                          Stop
                        </Button>
                      </div>
                    ))}
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Recent Activity */}
        <div>
          <Card>
            <CardHeader>
              <CardTitle className="text-base">Recent Activity</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {activeUsage
                  .filter((u) => u.status === 'completed')
                  .slice(0, 5)
                  .map((usage) => (
                    <div key={usage.id} className="flex items-start gap-3">
                      <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <CheckCircle className="w-4 h-4 text-green-600" />
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium text-gray-900">{usage.equipmentName}</p>
                        <p className="text-xs text-gray-500 truncate">{usage.purpose}</p>
                        <p className="text-xs text-gray-400 mt-0.5">
                          {format(new Date(usage.startTime), 'MMM dd, h:mm a')}
                        </p>
                      </div>
                    </div>
                  ))}

                {activeUsage.filter((u) => u.status === 'completed').length === 0 && (
                  <div className="text-center py-6 text-gray-500">
                    <FileText className="w-8 h-8 mx-auto mb-2 opacity-50" />
                    <p className="text-sm">No recent activity</p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
