import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import {
  Search,
  Filter,
  Microscope,
  CheckCircle,
  Clock,
  Wrench,
  AlertTriangle,
  Calendar,
  Info,
  ArrowRight,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { PageHeader } from '@/components/layout/PageHeader';
import { mockEquipment } from '@/lib/mockData';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog';

const categories = ['All', 'Molecular Biology', 'Microscopy', 'PCR & qPCR', 'Spectrophotometry', 'Centrifugation'];

const statusConfig: Record<string, { color: string; icon: React.ElementType; label: string }> = {
  available: { color: 'bg-green-100 text-green-700', icon: CheckCircle, label: 'Available' },
  in_use: { color: 'bg-blue-100 text-blue-700', icon: Clock, label: 'In Use' },
  maintenance: { color: 'bg-orange-100 text-orange-700', icon: Wrench, label: 'Maintenance' },
  retired: { color: 'bg-gray-100 text-gray-500', icon: AlertTriangle, label: 'Retired' },
};

export function EquipmentShowcasePage() {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('All');
  const [selectedEquipment, setSelectedEquipment] = useState<typeof mockEquipment[0] | null>(null);

  const filteredEquipment = mockEquipment.filter((eq) => {
    if (selectedCategory !== 'All' && eq.category !== selectedCategory) return false;
    if (searchQuery && !eq.name.toLowerCase().includes(searchQuery.toLowerCase())) return false;
    return true;
  });

  return (
    <div className="space-y-6">
      <PageHeader
        title="Laboratory Equipment"
        subtitle="Browse and request access to our facilities"
        actions={
          <Button asChild>
            <Link to="/laboratory/usage-logger">
              <Clock className="w-4 h-4 mr-2" />
              Log Usage
            </Link>
          </Button>
        }
      />

      {/* Filters */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div className="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0">
          {categories.map((category) => (
            <button
              key={category}
              onClick={() => setSelectedCategory(category)}
              className={cn(
                'px-3 py-1.5 text-sm font-medium rounded-full whitespace-nowrap transition-all',
                selectedCategory === category
                  ? 'bg-primary-100 text-primary-700'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              )}
            >
              {category}
            </button>
          ))}
        </div>

        <div className="relative">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <Input
            type="text"
            placeholder="Search equipment..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="pl-9 w-64"
          />
        </div>
      </div>

      {/* Equipment Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {filteredEquipment.map((equipment, index) => {
          const status = statusConfig[equipment.status];
          const StatusIcon = status.icon;
          
          return (
            <motion.div
              key={equipment.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.3, delay: index * 0.05 }}
            >
              <Card
                className="group hover:shadow-lg transition-all cursor-pointer overflow-hidden"
                onClick={() => setSelectedEquipment(equipment)}
              >
                <CardContent className="p-0">
                  <div className="h-40 bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center">
                    <Microscope className="w-16 h-16 text-primary-300 group-hover:scale-110 transition-transform" />
                  </div>
                  
                  <div className="p-4">
                    <div className="flex items-start justify-between mb-2">
                      <Badge variant="secondary" className="text-xs">
                        {equipment.category}
                      </Badge>
                      <Badge className={cn('text-xs', status.color)}>
                        <StatusIcon className="w-3 h-3 mr-1" />
                        {status.label}
                      </Badge>
                    </div>

                    <h3 className="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">
                      {equipment.name}
                    </h3>
                    <p className="text-sm text-gray-500">{equipment.model}</p>

                    <div className="mt-3 pt-3 border-t border-gray-100">
                      <div className="flex items-center gap-2 text-sm text-gray-500">
                        <Info className="w-4 h-4" />
                        <span className="truncate">{equipment.location}</span>
                      </div>
                    </div>

                    <div className="mt-3 flex items-center text-primary-600 text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                      View Details
                      <ArrowRight className="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" />
                    </div>
                  </div>
                </CardContent>
              </Card>
            </motion.div>
          );
        })}
      </div>

      {/* Equipment Detail Dialog */}
      <Dialog open={!!selectedEquipment} onOpenChange={() => setSelectedEquipment(null)}>
        <DialogContent className="max-w-lg">
          <DialogHeader>
            <DialogTitle>{selectedEquipment?.name}</DialogTitle>
            <DialogDescription>{selectedEquipment?.model}</DialogDescription>
          </DialogHeader>
          
          {selectedEquipment && (
            <div className="space-y-4">
              <div className="h-48 bg-gradient-to-br from-primary-50 to-primary-100 rounded-lg flex items-center justify-center">
                <Microscope className="w-24 h-24 text-primary-300" />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <p className="text-sm text-gray-500">Category</p>
                  <p className="font-medium">{selectedEquipment.category}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-500">Status</p>
                  <Badge className={statusConfig[selectedEquipment.status].color}>
                    {statusConfig[selectedEquipment.status].label}
                  </Badge>
                </div>
                <div>
                  <p className="text-sm text-gray-500">Manufacturer</p>
                  <p className="font-medium">{selectedEquipment.manufacturer}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-500">Serial Number</p>
                  <p className="font-mono text-sm">{selectedEquipment.serialNumber}</p>
                </div>
              </div>

              <div>
                <p className="text-sm text-gray-500">Location</p>
                <p className="font-medium">{selectedEquipment.location}</p>
              </div>

              {selectedEquipment.description && (
                <div>
                  <p className="text-sm text-gray-500">Description</p>
                  <p className="text-sm">{selectedEquipment.description}</p>
                </div>
              )}

              {selectedEquipment.specifications && (
                <div>
                  <p className="text-sm text-gray-500">Specifications</p>
                  <p className="text-sm">{selectedEquipment.specifications}</p>
                </div>
              )}

              <div className="flex gap-2 pt-4">
                <Button className="flex-1">
                  <Calendar className="w-4 h-4 mr-2" />
                  Request Access
                </Button>
                <Button variant="outline" className="flex-1" asChild>
                  <Link to="/laboratory/usage-logger">
                    <Clock className="w-4 h-4 mr-2" />
                    Log Usage
                  </Link>
                </Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
}
