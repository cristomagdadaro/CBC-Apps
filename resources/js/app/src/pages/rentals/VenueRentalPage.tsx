import React, { useState } from 'react';
import { motion } from 'framer-motion';
import {
  Building2,
  Calendar,
  Clock,
  Users,
  MapPin,
  Wifi,
  Projector,
  Volume2,
  CheckCircle,
  Search,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { PageHeader } from '@/components/layout/PageHeader';
import { mockRentalItems } from '@/lib/mockData';
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

const amenityIcons: Record<string, React.ElementType> = {
  'Projector': Projector,
  'Sound System': Volume2,
  'WiFi': Wifi,
  'Whiteboard': CheckCircle,
  'Air Conditioning': CheckCircle,
};

export function VenueRentalPage() {
  const [selectedVenue, setSelectedVenue] = useState<typeof mockRentalItems[0] | null>(null);
  const [showBookingForm, setShowBookingForm] = useState(false);

  const venues = mockRentalItems.filter((item) => item.type === 'venue');

  return (
    <div className="space-y-6">
      <PageHeader
        title="Venue Rental"
        subtitle="Reserve meeting rooms and event spaces for your activities"
      />

      {/* Search */}
      <div className="flex items-center gap-4">
        <div className="relative flex-1 max-w-md">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
          <Input placeholder="Search venues..." className="pl-10" />
        </div>
      </div>

      {/* Venues Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {venues.map((venue, index) => (
          <motion.div
            key={venue.id}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3, delay: index * 0.1 }}
          >
            <Card className="group hover:shadow-lg transition-all overflow-hidden">
              <CardContent className="p-0">
                <div className="h-48 bg-gradient-to-br from-teal-50 to-teal-100 flex items-center justify-center">
                  <Building2 className="w-24 h-24 text-teal-300 group-hover:scale-110 transition-transform" />
                </div>
                
                <div className="p-4">
                  <div className="flex items-start justify-between mb-2">
                    <h3 className="font-semibold text-gray-900">{venue.name}</h3>
                    <Badge
                      variant="secondary"
                      className={cn(
                        venue.status === 'available'
                          ? 'bg-green-100 text-green-700'
                          : 'bg-gray-100 text-gray-600'
                      )}
                    >
                      {venue.status === 'available' ? 'Available' : 'Booked'}
                    </Badge>
                  </div>

                  <p className="text-sm text-gray-500 mb-3">{venue.description}</p>

                  <div className="flex items-center gap-4 text-sm text-gray-600 mb-3">
                    <div className="flex items-center gap-1">
                      <Users className="w-4 h-4" />
                      <span>Up to {venue.capacity} people</span>
                    </div>
                    <div className="flex items-center gap-1">
                      <MapPin className="w-4 h-4" />
                      <span className="truncate">{venue.location}</span>
                    </div>
                  </div>

                  {/* Amenities */}
                  <div className="flex flex-wrap gap-1 mb-4">
                    {venue.amenities?.slice(0, 3).map((amenity) => (
                      <Badge key={amenity} variant="outline" className="text-xs">
                        {amenity}
                      </Badge>
                    ))}
                    {(venue.amenities?.length || 0) > 3 && (
                      <Badge variant="outline" className="text-xs">
                        +{(venue.amenities?.length || 0) - 3} more
                      </Badge>
                    )}
                  </div>

                  <Button
                    className="w-full"
                    disabled={venue.status !== 'available'}
                    onClick={() => {
                      setSelectedVenue(venue);
                      setShowBookingForm(true);
                    }}
                  >
                    {venue.status === 'available' ? 'Book Now' : 'Not Available'}
                  </Button>
                </div>
              </CardContent>
            </Card>
          </motion.div>
        ))}
      </div>

      {/* Booking Dialog */}
      <Dialog open={showBookingForm} onOpenChange={setShowBookingForm}>
        <DialogContent className="max-w-lg">
          <DialogHeader>
            <DialogTitle>Book {selectedVenue?.name}</DialogTitle>
          </DialogHeader>
          
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Date</Label>
                <Input type="date" />
              </div>
              <div className="space-y-2">
                <Label>Number of Attendees</Label>
                <Input type="number" placeholder={`Max ${selectedVenue?.capacity}`} />
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
              <Label>Event Type</Label>
              <Input placeholder="e.g., Meeting, Training, Workshop" />
            </div>

            <div className="space-y-2">
              <Label>Purpose *</Label>
              <Textarea placeholder="Describe the purpose of your venue rental..." />
            </div>

            <div className="space-y-2">
              <Label>Special Requirements</Label>
              <Textarea placeholder="Any special setup or equipment needed..." />
            </div>

            <Button className="w-full">
              Submit Venue Request
            </Button>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
}
