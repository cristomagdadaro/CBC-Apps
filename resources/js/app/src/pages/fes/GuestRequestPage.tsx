import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import {
  ChevronLeft,
  ChevronRight,
  Check,
  Building2,
  Microscope,
  Package,
  User,
  Mail,
  Phone,
  Building,
  FileText,
  Calendar,
  Plus,
  Trash2,
  Send,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { mockEquipment, mockInventoryItems } from '@/lib/mockData';

const steps = [
  { id: 1, title: 'Requestor Info', icon: User },
  { id: 2, title: 'Request Details', icon: FileText },
  { id: 3, title: 'Items & Equipment', icon: Package },
  { id: 4, title: 'Review & Submit', icon: Check },
];

export function GuestRequestPage() {
  const navigate = useNavigate();
  const [currentStep, setCurrentStep] = useState(1);
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    affiliation: '',
    philriceId: '',
    purpose: '',
    projectCode: '',
    startDate: '',
    endDate: '',
    items: [] as Array<{ type: string; itemId: string; itemName: string; quantity: number }>,
  });

  const updateFormData = (field: string, value: string) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
  };

  const addItem = (type: string, itemId: string, itemName: string) => {
    setFormData((prev) => ({
      ...prev,
      items: [...prev.items, { type, itemId, itemName, quantity: 1 }],
    }));
  };

  const removeItem = (index: number) => {
    setFormData((prev) => ({
      ...prev,
      items: prev.items.filter((_, i) => i !== index),
    }));
  };

  const handleSubmit = () => {
    // Simulate submission
    alert('Request submitted successfully! Reference: FES-2024-0159');
    navigate('/');
  };

  const canProceed = () => {
    switch (currentStep) {
      case 1:
        return formData.name && formData.email && formData.phone && formData.affiliation;
      case 2:
        return formData.purpose && formData.startDate && formData.endDate;
      case 3:
        return true;
      default:
        return true;
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-primary-50 via-white to-primary-100 py-8 px-4">
      <div className="max-w-4xl mx-auto">
        {/* Header */}
        <div className="text-center mb-8">
          <div className="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center mx-auto mb-4">
            <Building2 className="w-8 h-8 text-white" />
          </div>
          <h1 className="text-2xl font-bold text-gray-900">Facility, Equipment & Supplies Request</h1>
          <p className="text-gray-500 mt-1">Request access to DA-CBC facilities and resources</p>
        </div>

        {/* Progress */}
        <div className="mb-8">
          <div className="flex items-center justify-between">
            {steps.map((step, index) => {
              const Icon = step.icon;
              const isActive = step.id === currentStep;
              const isCompleted = step.id < currentStep;

              return (
                <div key={step.id} className="flex items-center">
                  <div
                    className={cn(
                      'flex items-center gap-2 px-4 py-2 rounded-full transition-all',
                      isActive && 'bg-primary-100 text-primary-700',
                      isCompleted && 'bg-green-100 text-green-700',
                      !isActive && !isCompleted && 'bg-gray-100 text-gray-500'
                    )}
                  >
                    <div
                      className={cn(
                        'w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium',
                        isActive && 'bg-primary-600 text-white',
                        isCompleted && 'bg-green-600 text-white',
                        !isActive && !isCompleted && 'bg-gray-300 text-white'
                      )}
                    >
                      {isCompleted ? <Check className="w-3 h-3" /> : step.id}
                    </div>
                    <span className="text-sm font-medium hidden sm:inline">{step.title}</span>
                  </div>
                  {index < steps.length - 1 && (
                    <div
                      className={cn(
                        'w-8 h-0.5 mx-2',
                        isCompleted ? 'bg-green-300' : 'bg-gray-200'
                      )}
                    />
                  )}
                </div>
              );
            })}
          </div>
        </div>

        {/* Form Content */}
        <Card>
          <CardContent className="p-6">
            {currentStep === 1 && (
              <motion.div
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                className="space-y-4"
              >
                <h2 className="text-lg font-semibold mb-4">Requestor Information</h2>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="name">Full Name *</Label>
                    <Input
                      id="name"
                      value={formData.name}
                      onChange={(e) => updateFormData('name', e.target.value)}
                      placeholder="Enter your full name"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="email">Email Address *</Label>
                    <Input
                      id="email"
                      type="email"
                      value={formData.email}
                      onChange={(e) => updateFormData('email', e.target.value)}
                      placeholder="you@example.com"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="phone">Contact Number *</Label>
                    <Input
                      id="phone"
                      value={formData.phone}
                      onChange={(e) => updateFormData('phone', e.target.value)}
                      placeholder="+63 XXX XXX XXXX"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="affiliation">Affiliation *</Label>
                    <Input
                      id="affiliation"
                      value={formData.affiliation}
                      onChange={(e) => updateFormData('affiliation', e.target.value)}
                      placeholder="Organization/Institution"
                    />
                  </div>
                  <div className="space-y-2 sm:col-span-2">
                    <Label htmlFor="philriceId">PhilRice ID (optional)</Label>
                    <Input
                      id="philriceId"
                      value={formData.philriceId}
                      onChange={(e) => updateFormData('philriceId', e.target.value)}
                      placeholder="PR-XXXX-XXX"
                    />
                  </div>
                </div>
              </motion.div>
            )}

            {currentStep === 2 && (
              <motion.div
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                className="space-y-4"
              >
                <h2 className="text-lg font-semibold mb-4">Request Details</h2>
                <div className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="purpose">Purpose of Request *</Label>
                    <Textarea
                      id="purpose"
                      value={formData.purpose}
                      onChange={(e) => updateFormData('purpose', e.target.value)}
                      placeholder="Describe the purpose of your request..."
                      rows={4}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="projectCode">Project Code (optional)</Label>
                    <Input
                      id="projectCode"
                      value={formData.projectCode}
                      onChange={(e) => updateFormData('projectCode', e.target.value)}
                      placeholder="e.g., GEA-2024-023"
                    />
                  </div>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label htmlFor="startDate">Start Date *</Label>
                      <Input
                        id="startDate"
                        type="date"
                        value={formData.startDate}
                        onChange={(e) => updateFormData('startDate', e.target.value)}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="endDate">End Date *</Label>
                      <Input
                        id="endDate"
                        type="date"
                        value={formData.endDate}
                        onChange={(e) => updateFormData('endDate', e.target.value)}
                      />
                    </div>
                  </div>
                </div>
              </motion.div>
            )}

            {currentStep === 3 && (
              <motion.div
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                className="space-y-4"
              >
                <h2 className="text-lg font-semibold mb-4">Equipment & Supplies</h2>
                
                {/* Selected Items */}
                {formData.items.length > 0 && (
                  <div className="space-y-2 mb-4">
                    {formData.items.map((item, index) => (
                      <div
                        key={index}
                        className="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                      >
                        <div className="flex items-center gap-3">
                          <Badge variant="secondary" className="capitalize">{item.type}</Badge>
                          <span className="font-medium">{item.itemName}</span>
                          <span className="text-gray-500">x{item.quantity}</span>
                        </div>
                        <Button
                          variant="ghost"
                          size="icon"
                          className="h-8 w-8 text-red-500"
                          onClick={() => removeItem(index)}
                        >
                          <Trash2 className="w-4 h-4" />
                        </Button>
                      </div>
                    ))}
                  </div>
                )}

                {/* Add Equipment */}
                <div className="border rounded-lg p-4">
                  <h3 className="font-medium mb-3 flex items-center gap-2">
                    <Microscope className="w-4 h-4" />
                    Add Equipment
                  </h3>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {mockEquipment.slice(0, 4).map((eq) => (
                      <Button
                        key={eq.id}
                        variant="outline"
                        className="justify-start text-left h-auto py-2"
                        onClick={() => addItem('equipment', eq.id, eq.name)}
                      >
                        <Plus className="w-4 h-4 mr-2" />
                        <span className="truncate">{eq.name}</span>
                      </Button>
                    ))}
                  </div>
                </div>

                {/* Add Supplies */}
                <div className="border rounded-lg p-4">
                  <h3 className="font-medium mb-3 flex items-center gap-2">
                    <Package className="w-4 h-4" />
                    Add Supplies
                  </h3>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {mockInventoryItems.slice(0, 4).map((item) => (
                      <Button
                        key={item.id}
                        variant="outline"
                        className="justify-start text-left h-auto py-2"
                        onClick={() => addItem('supply', item.id, item.name)}
                      >
                        <Plus className="w-4 h-4 mr-2" />
                        <span className="truncate">{item.name}</span>
                      </Button>
                    ))}
                  </div>
                </div>
              </motion.div>
            )}

            {currentStep === 4 && (
              <motion.div
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                className="space-y-4"
              >
                <h2 className="text-lg font-semibold mb-4">Review Your Request</h2>
                
                <div className="space-y-4">
                  <div className="bg-gray-50 rounded-lg p-4">
                    <h3 className="font-medium text-gray-900 mb-2">Requestor Information</h3>
                    <div className="grid grid-cols-2 gap-2 text-sm">
                      <div>
                        <span className="text-gray-500">Name:</span>
                        <span className="ml-2">{formData.name}</span>
                      </div>
                      <div>
                        <span className="text-gray-500">Email:</span>
                        <span className="ml-2">{formData.email}</span>
                      </div>
                      <div>
                        <span className="text-gray-500">Phone:</span>
                        <span className="ml-2">{formData.phone}</span>
                      </div>
                      <div>
                        <span className="text-gray-500">Affiliation:</span>
                        <span className="ml-2">{formData.affiliation}</span>
                      </div>
                    </div>
                  </div>

                  <div className="bg-gray-50 rounded-lg p-4">
                    <h3 className="font-medium text-gray-900 mb-2">Request Details</h3>
                    <div className="space-y-2 text-sm">
                      <div>
                        <span className="text-gray-500">Purpose:</span>
                        <p className="mt-1">{formData.purpose}</p>
                      </div>
                      <div className="grid grid-cols-2 gap-2">
                        <div>
                          <span className="text-gray-500">Start Date:</span>
                          <span className="ml-2">{formData.startDate}</span>
                        </div>
                        <div>
                          <span className="text-gray-500">End Date:</span>
                          <span className="ml-2">{formData.endDate}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  {formData.items.length > 0 && (
                    <div className="bg-gray-50 rounded-lg p-4">
                      <h3 className="font-medium text-gray-900 mb-2">Requested Items</h3>
                      <div className="space-y-1">
                        {formData.items.map((item, index) => (
                          <div key={index} className="flex items-center gap-2 text-sm">
                            <Badge variant="secondary" className="capitalize">{item.type}</Badge>
                            <span>{item.itemName}</span>
                            <span className="text-gray-500">x{item.quantity}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              </motion.div>
            )}

            {/* Navigation */}
            <div className="flex items-center justify-between mt-8 pt-6 border-t">
              <Button
                variant="outline"
                onClick={() => setCurrentStep((prev) => Math.max(1, prev - 1))}
                disabled={currentStep === 1}
              >
                <ChevronLeft className="w-4 h-4 mr-2" />
                Previous
              </Button>

              {currentStep < steps.length ? (
                <Button
                  onClick={() => setCurrentStep((prev) => prev + 1)}
                  disabled={!canProceed()}
                >
                  Next
                  <ChevronRight className="w-4 h-4 ml-2" />
                </Button>
              ) : (
                <Button onClick={handleSubmit}>
                  <Send className="w-4 h-4 mr-2" />
                  Submit Request
                </Button>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
