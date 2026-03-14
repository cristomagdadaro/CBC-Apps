import React, { useState } from 'react';
import { motion } from 'framer-motion';
import {
  Award,
  Upload,
  FileSpreadsheet,
  Eye,
  Download,
  CheckCircle,
  User,
  Calendar,
  FileText,
  Image,
  Trash2,
  Plus,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { PageHeader } from '@/components/layout/PageHeader';
import { mockCertificateTemplates } from '@/lib/mockData';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

export function CertificateGeneratorPage() {
  const [selectedTemplate, setSelectedTemplate] = useState<typeof mockCertificateTemplates[0] | null>(null);
  const [showPreview, setShowPreview] = useState(false);
  const [participants, setParticipants] = useState<Array<{ name: string; email: string }>>([
    { name: 'Maria Clara', email: 'maria@example.com' },
    { name: 'Jose Rizal', email: 'jose@example.com' },
    { name: 'Andres Bonifacio', email: 'andres@example.com' },
  ]);

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    // Simulate file upload
    alert('CSV file uploaded successfully!');
  };

  const generateCertificates = () => {
    alert(`Generating ${participants.length} certificates...`);
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Certificate Generator"
        subtitle="Generate and download certificates for events and training"
      />

      <Tabs defaultValue="templates" className="space-y-6">
        <TabsList className="bg-gray-100 p-1">
          <TabsTrigger value="templates" className="data-[state=active]:bg-white">
            Templates
          </TabsTrigger>
          <TabsTrigger value="batch" className="data-[state=active]:bg-white">
            Batch Generation
          </TabsTrigger>
          <TabsTrigger value="history" className="data-[state=active]:bg-white">
            History
          </TabsTrigger>
        </TabsList>

        <TabsContent value="templates" className="space-y-6">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {mockCertificateTemplates.map((template, index) => (
              <motion.div
                key={template.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.3, delay: index * 0.1 }}
              >
                <Card
                  className={cn(
                    'group cursor-pointer overflow-hidden transition-all',
                    selectedTemplate?.id === template.id
                      ? 'ring-2 ring-primary-500 shadow-lg'
                      : 'hover:shadow-lg'
                  )}
                  onClick={() => setSelectedTemplate(template)}
                >
                  <CardContent className="p-0">
                    <div
                      className="h-48 flex items-center justify-center"
                      style={{
                        background: `linear-gradient(135deg, ${template.design.primaryColor}20, ${template.design.secondaryColor}20)`,
                      }}
                    >
                      <Award
                        className="w-24 h-24"
                        style={{ color: template.design.primaryColor }}
                      />
                    </div>
                    
                    <div className="p-4">
                      <div className="flex items-center justify-between mb-2">
                        <h3 className="font-semibold text-gray-900">{template.name}</h3>
                        <Badge variant="secondary" className="capitalize">
                          {template.type}
                        </Badge>
                      </div>
                      <p className="text-sm text-gray-500 mb-3">{template.description}</p>
                      
                      <div className="flex items-center gap-2">
                        <Button
                          variant="outline"
                          size="sm"
                          className="flex-1"
                          onClick={(e) => {
                            e.stopPropagation();
                            setSelectedTemplate(template);
                            setShowPreview(true);
                          }}
                        >
                          <Eye className="w-4 h-4 mr-2" />
                          Preview
                        </Button>
                        <Button size="sm" className="flex-1">
                          <Plus className="w-4 h-4 mr-2" />
                          Use
                        </Button>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </motion.div>
            ))}
          </div>
        </TabsContent>

        <TabsContent value="batch" className="space-y-6">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Upload Section */}
            <div className="lg:col-span-2 space-y-6">
              <Card>
                <CardContent className="p-6">
                  <h3 className="font-semibold text-gray-900 mb-4">Upload Participant List</h3>
                  
                  <div className="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-primary-300 transition-colors">
                    <Upload className="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p className="text-gray-600 mb-2">
                      Drag and drop your CSV or Excel file here
                    </p>
                    <p className="text-sm text-gray-400 mb-4">
                      or click to browse files
                    </p>
                    <Input
                      type="file"
                      accept=".csv,.xlsx,.xls"
                      onChange={handleFileUpload}
                      className="hidden"
                      id="file-upload"
                    />
                    <Button variant="outline" asChild>
                      <label htmlFor="file-upload" className="cursor-pointer">
                        <FileSpreadsheet className="w-4 h-4 mr-2" />
                        Select File
                      </label>
                    </Button>
                  </div>

                  <div className="mt-4 p-4 bg-gray-50 rounded-lg">
                    <p className="text-sm font-medium text-gray-700 mb-2">Required columns:</p>
                    <ul className="text-sm text-gray-500 list-disc list-inside">
                      <li>name (Participant full name)</li>
                      <li>email (Participant email address)</li>
                      <li>event_name (Name of the event)</li>
                      <li>event_date (Date of the event)</li>
                    </ul>
                  </div>
                </CardContent>
              </Card>

              {/* Participant List */}
              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center justify-between mb-4">
                    <h3 className="font-semibold text-gray-900">
                      Participants ({participants.length})
                    </h3>
                    <Button variant="outline" size="sm">
                      <Plus className="w-4 h-4 mr-2" />
                      Add Manually
                    </Button>
                  </div>

                  <div className="space-y-2">
                    {participants.map((participant, index) => (
                      <div
                        key={index}
                        className="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                      >
                        <div className="flex items-center gap-3">
                          <div className="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                            <User className="w-4 h-4 text-primary-600" />
                          </div>
                          <div>
                            <p className="font-medium text-sm">{participant.name}</p>
                            <p className="text-xs text-gray-500">{participant.email}</p>
                          </div>
                        </div>
                        <Button variant="ghost" size="icon" className="h-8 w-8 text-red-500">
                          <Trash2 className="w-4 h-4" />
                        </Button>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Settings */}
            <div className="space-y-6">
              <Card>
                <CardContent className="p-6 space-y-4">
                  <h3 className="font-semibold text-gray-900">Generation Settings</h3>

                  <div className="space-y-2">
                    <Label>Selected Template</Label>
                    <div className="p-3 bg-gray-50 rounded-lg flex items-center gap-3">
                      <Award className="w-8 h-8 text-primary-600" />
                      <div>
                        <p className="font-medium text-sm">
                          {selectedTemplate?.name || 'Participation Certificate'}
                        </p>
                        <p className="text-xs text-gray-500">
                          {selectedTemplate?.type || 'participation'}
                        </p>
                      </div>
                    </div>
                  </div>

                  <div className="space-y-2">
                    <Label>Event Name</Label>
                    <Input placeholder="e.g., Regional Crop Biotechnology Symposium" />
                  </div>

                  <div className="space-y-2">
                    <Label>Event Date</Label>
                    <Input type="date" />
                  </div>

                  <div className="pt-4">
                    <Button className="w-full" onClick={generateCertificates}>
                      <Award className="w-4 h-4 mr-2" />
                      Generate {participants.length} Certificates
                    </Button>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardContent className="p-6">
                  <h3 className="font-semibold text-gray-900 mb-4">Preview</h3>
                  <div className="aspect-[1.4/1] bg-gradient-to-br from-primary-50 to-primary-100 rounded-lg flex items-center justify-center">
                    <div className="text-center">
                      <Award className="w-16 h-16 text-primary-300 mx-auto mb-2" />
                      <p className="text-sm text-gray-500">Certificate Preview</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </TabsContent>

        <TabsContent value="history">
          <Card>
            <CardContent className="p-6">
              <div className="text-center py-12 text-gray-500">
                <FileText className="w-12 h-12 mx-auto mb-4 opacity-50" />
                <p>No certificate generation history</p>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      {/* Preview Dialog */}
      <Dialog open={showPreview} onOpenChange={setShowPreview}>
        <DialogContent className="max-w-3xl">
          <DialogHeader>
            <DialogTitle>Certificate Preview</DialogTitle>
          </DialogHeader>
          <div className="aspect-[1.4/1] bg-gradient-to-br from-primary-50 to-primary-100 rounded-lg flex items-center justify-center border-4 border-double border-primary-200">
            <div className="text-center p-8">
              <Award className="w-24 h-24 text-primary-400 mx-auto mb-4" />
              <h2 className="text-2xl font-serif text-primary-800 mb-2">Certificate of Participation</h2>
              <p className="text-gray-600 mb-4">This is to certify that</p>
              <p className="text-xl font-semibold text-gray-900 mb-4">[Participant Name]</p>
              <p className="text-gray-600 mb-4">has successfully participated in</p>
              <p className="text-lg font-medium text-gray-900 mb-4">[Event Name]</p>
              <p className="text-gray-500">held on [Event Date]</p>
              <div className="mt-8 pt-8 border-t border-primary-200">
                <div className="flex items-center justify-center gap-8">
                  <div className="text-center">
                    <div className="w-32 h-0.5 bg-gray-400 mb-2" />
                    <p className="text-sm text-gray-500">Authorized Signature</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
}
