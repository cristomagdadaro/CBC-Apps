import React, { useState } from 'react';
import { motion } from 'framer-motion';
import {
  Truck,
  Calendar,
  Clock,
  User,
  Phone,
  FileText,
  CheckCircle,
  AlertCircle,
  MapPin,
  Users,
  Fuel,
  Gauge,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { PageHeader } from '@/components/layout/PageHeader';
import { mockRentalItems, mockRentalRequests } from '@/lib/mockData';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { format } from 'date-fns';

const statusConfig: Record<string, { color: string; label: string }> = {
  pending: { color: 'bg-yellow-100 text-yellow-700', label: 'Pending' },
  approved: { color: 'bg-green-100 text-green-700', label: 'Approved' },
  active: { color: 'bg-blue-100 text-blue-700', label: 'Active' },
  completed: { color: 'bg-gray-100 text-gray-600', label: 'Completed' },
};

export function VehicleRentalPage() {
  const [selectedVehicle, setSelectedVehicle] = useState<typeof mockRentalItems[0] | null>(null);
  const [showBookingForm, setShowBookingForm] = useState(false);

  const vehicles = mockRentalItems.filter((item) => item.type === 'vehicle');
  const myRentals = mockRentalRequests.filter((r) => r.type === 'vehicle');

  return (
    <div className="space-y-6">
      <PageHeader
        title="Vehicle Rental"
        subtitle="Reserve and book available vehicles for your research activities"
      />

      <Tabs defaultValue="browse" className="space-y-6">
        <TabsList className="bg-gray-100 p-1">
          <TabsTrigger value="browse" className="data-[state=active]:bg-white">
            Browse Vehicles
          </TabsTrigger>
          <TabsTrigger value="my-rentals" className="data-[state=active]:bg-white">
            My Rentals
          </TabsTrigger>
        </TabsList>

        <TabsContent value="browse" className="space-y-6">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {vehicles.map((vehicle, index) => (
              <motion.div
                key={vehicle.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.3, delay: index * 0.1 }}
              >
                <Card className="group hover:shadow-lg transition-all overflow-hidden">
                  <CardContent className="p-0">
                    <div className="h-40 bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                      <Truck className="w-20 h-20 text-blue-300 group-hover:scale-110 transition-transform" />
                    </div>
                    
                    <div className="p-4">
                      <div className="flex items-start justify-between mb-2">
                        <h3 className="font-semibold text-gray-900">{vehicle.name}</h3>
                        <Badge
                          variant="secondary"
                          className={cn(
                            vehicle.status === 'available'
                              ? 'bg-green-100 text-green-700'
                              : 'bg-gray-100 text-gray-600'
                          )}
                        >
                          {vehicle.status === 'available' ? 'Available' : 'Unavailable'}
                        </Badge>
                      </div>

                      <p className="text-sm text-gray-500 mb-3">{vehicle.description}</p>

                      <div className="flex items-center gap-4 text-sm text-gray-600 mb-4">
                        <div className="flex items-center gap-1">
                          <Users className="w-4 h-4" />
                          <span>{vehicle.capacity} seats</span>
                        </div>
                        <div className="flex items-center gap-1">
                          <Gauge className="w-4 h-4" />
                          <span>₱{vehicle.rate}/{vehicle.rateUnit}</span>
                        </div>
                      </div>

                      <Button
                        className="w-full"
                        disabled={vehicle.status !== 'available'}
                        onClick={() => {
                          setSelectedVehicle(vehicle);
                          setShowBookingForm(true);
                        }}
                      >
                        {vehicle.status === 'available' ? 'Book Now' : 'Not Available'}
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              </motion.div>
            ))}
          </div>
        </TabsContent>

        <TabsContent value="my-rentals">
          <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table className="w-full">
              <thead className="bg-gray-50/50">
                <tr>
                  <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Reference</th>
                  <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Vehicle</th>
                  <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Dates</th>
                  <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Status</th>
                  <th className="text-left px-6 py-3 text-sm font-medium text-gray-500">Cost</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {myRentals.map((rental) => (
                  <tr key={rental.id} className="hover:bg-gray-50/80">
                    <td className="px-6 py-4">
                      <span className="font-mono text-sm font-medium text-primary-600">
                        {rental.reference}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <p className="font-medium">{rental.item.name}</p>
                    </td>
                    <td className="px-6 py-4">
                      <p className="text-sm text-gray-600">
                        {format(new Date(rental.startDate), 'MMM dd')} - {format(new Date(rental.endDate), 'MMM dd, yyyy')}
                      </p>
                    </td>
                    <td className="px-6 py-4">
                      <Badge className={statusConfig[rental.status].color}>
                        {statusConfig[rental.status].label}
                      </Badge>
                    </td>
                    <td className="px-6 py-4">
                      <p className="font-medium">₱{rental.totalCost?.toLocaleString()}</p>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </TabsContent>
      </Tabs>

      {/* Booking Dialog */}
      <Dialog open={showBookingForm} onOpenChange={setShowBookingForm}>
        <DialogContent className="max-w-lg">
          <DialogHeader>
            <DialogTitle>Book {selectedVehicle?.name}</DialogTitle>
          </DialogHeader>
          
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Start Date</Label>
                <Input type="date" />
              </div>
              <div className="space-y-2">
                <Label>End Date</Label>
                <Input type="date" />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Start Time</Label>
                <Input type="time" />
              </div>
              <div className="space-y-2">
                <Label>End Time</Label>
                <Input type="time" />
              </div>
            </div>

            <div className="space-y-2">
              <Label>Purpose *</Label>
              <Textarea placeholder="Describe the purpose of your vehicle rental..." />
            </div>

            <div className="space-y-2">
              <Label>Driver Name *</Label>
              <Input placeholder="Full name of the driver" />
            </div>

            <div className="space-y-2">
              <Label>Driver Contact *</Label>
              <Input placeholder="Contact number" />
            </div>

            <div className="bg-gray-50 rounded-lg p-4">
              <div className="flex items-center justify-between text-sm">
                <span className="text-gray-500">Estimated Cost</span>
                <span className="font-semibold text-lg">
                  ₱{selectedVehicle?.rate?.toLocaleString()}/{selectedVehicle?.rateUnit}
                </span>
              </div>
            </div>

            <Button className="w-full">
              Submit Rental Request
            </Button>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
}
