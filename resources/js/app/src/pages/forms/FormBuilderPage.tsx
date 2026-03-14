import React, { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import {
  ArrowLeft,
  Save,
  Eye,
  Settings,
  Plus,
  Type,
  List,
  Calendar,
  Upload,
  Star,
  GripVertical,
  Trash2,
  Copy,
  ChevronDown,
  Check,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { PageHeader } from '@/components/layout/PageHeader';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';

const fieldTypes = [
  { id: 'text', label: 'Text', icon: Type, description: 'Single line text input' },
  { id: 'textarea', label: 'Text Area', icon: Type, description: 'Multi-line text input' },
  { id: 'email', label: 'Email', icon: Type, description: 'Email address input' },
  { id: 'number', label: 'Number', icon: Type, description: 'Numeric input' },
  { id: 'select', label: 'Dropdown', icon: List, description: 'Single selection dropdown' },
  { id: 'multiselect', label: 'Multi Select', icon: List, description: 'Multiple selection' },
  { id: 'radio', label: 'Radio Buttons', icon: List, description: 'Single choice options' },
  { id: 'checkbox', label: 'Checkboxes', icon: List, description: 'Multiple choice options' },
  { id: 'date', label: 'Date', icon: Calendar, description: 'Date picker' },
  { id: 'file', label: 'File Upload', icon: Upload, description: 'File attachment' },
  { id: 'rating', label: 'Rating', icon: Star, description: 'Star rating input' },
];

export function FormBuilderPage() {
  const { id } = useParams();
  const isEditing = !!id;
  const [activeTab, setActiveTab] = useState('builder');
  const [formTitle, setFormTitle] = useState(isEditing ? 'Regional Crop Biotechnology Symposium' : 'New Event Form');
  const [formDescription, setFormDescription] = useState('');
  const [fields, setFields] = useState<Array<{ id: string; type: string; label: string; required: boolean }>>([
    { id: '1', type: 'text', label: 'Full Name', required: true },
    { id: '2', type: 'email', label: 'Email Address', required: true },
    { id: '3', type: 'select', label: 'Affiliation', required: true },
  ]);
  const [saveStatus, setSaveStatus] = useState<'saved' | 'saving' | 'unsaved'>('saved');

  const handleAddField = (type: string) => {
    const newField = {
      id: Date.now().toString(),
      type,
      label: 'New Field',
      required: false,
    };
    setFields([...fields, newField]);
    setSaveStatus('unsaved');
  };

  const handleRemoveField = (fieldId: string) => {
    setFields(fields.filter((f) => f.id !== fieldId));
    setSaveStatus('unsaved');
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="icon" asChild>
            <Link to="/forms">
              <ArrowLeft className="w-5 h-5" />
            </Link>
          </Button>
          <div>
            <Input
              value={formTitle}
              onChange={(e) => {
                setFormTitle(e.target.value);
                setSaveStatus('unsaved');
              }}
              className="text-xl font-semibold border-0 px-0 focus-visible:ring-0 w-[400px]"
            />
            <div className="flex items-center gap-2 mt-1">
              <Badge
                variant="secondary"
                className={cn(
                  'text-xs',
                  saveStatus === 'saved' && 'bg-green-100 text-green-700',
                  saveStatus === 'saving' && 'bg-yellow-100 text-yellow-700',
                  saveStatus === 'unsaved' && 'bg-gray-100 text-gray-700'
                )}
              >
                {saveStatus === 'saved' && <Check className="w-3 h-3 mr-1" />}
                {saveStatus === 'saved' ? 'Saved' : saveStatus === 'saving' ? 'Saving...' : 'Unsaved changes'}
              </Badge>
            </div>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <Button variant="outline" onClick={() => setActiveTab('preview')}>
            <Eye className="w-4 h-4 mr-2" />
            Preview
          </Button>
          <Button>
            <Save className="w-4 h-4 mr-2" />
            Publish
          </Button>
        </div>
      </div>

      {/* Main Content */}
      <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
        <TabsList className="bg-gray-100 p-1">
          <TabsTrigger value="builder" className="data-[state=active]:bg-white">
            Builder
          </TabsTrigger>
          <TabsTrigger value="settings" className="data-[state=active]:bg-white">
            Settings
          </TabsTrigger>
          <TabsTrigger value="preview" className="data-[state=active]:bg-white">
            Preview
          </TabsTrigger>
        </TabsList>

        <TabsContent value="builder" className="space-y-0">
          <div className="grid grid-cols-1 lg:grid-cols-[280px_1fr_320px] gap-6">
            {/* Field Palette */}
            <div className="bg-white rounded-xl border border-gray-200 p-4">
              <h3 className="font-semibold text-gray-900 mb-4">Field Types</h3>
              <div className="space-y-2">
                {fieldTypes.map((fieldType) => {
                  const Icon = fieldType.icon;
                  return (
                    <button
                      key={fieldType.id}
                      onClick={() => handleAddField(fieldType.id)}
                      className="w-full flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50 transition-colors text-left"
                    >
                      <Icon className="w-4 h-4 text-gray-500" />
                      <div>
                        <p className="text-sm font-medium text-gray-900">{fieldType.label}</p>
                        <p className="text-xs text-gray-500">{fieldType.description}</p>
                      </div>
                      <Plus className="w-4 h-4 ml-auto text-gray-400" />
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Form Canvas */}
            <div className="bg-white rounded-xl border border-gray-200 p-6 min-h-[600px]">
              <div className="max-w-lg mx-auto space-y-4">
                <div className="text-center pb-6 border-b border-gray-100">
                  <h2 className="text-xl font-semibold text-gray-900">{formTitle}</h2>
                  {formDescription && (
                    <p className="text-sm text-gray-500 mt-1">{formDescription}</p>
                  )}
                </div>

                {fields.map((field, index) => (
                  <motion.div
                    key={field.id}
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="group relative p-4 rounded-lg border border-gray-200 hover:border-primary-300 hover:shadow-sm transition-all"
                  >
                    <div className="flex items-start gap-3">
                      <div className="mt-1 opacity-0 group-hover:opacity-100 cursor-move">
                        <GripVertical className="w-4 h-4 text-gray-400" />
                      </div>
                      <div className="flex-1">
                        <Label className="flex items-center gap-1">
                          {field.label}
                          {field.required && <span className="text-red-500">*</span>}
                        </Label>
                        <Input
                          placeholder={`Enter ${field.label.toLowerCase()}`}
                          disabled
                          className="mt-1.5 bg-gray-50"
                        />
                      </div>
                      <div className="opacity-0 group-hover:opacity-100 flex items-center gap-1">
                        <Button variant="ghost" size="icon" className="h-8 w-8">
                          <Copy className="w-4 h-4" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon"
                          className="h-8 w-8 text-red-500"
                          onClick={() => handleRemoveField(field.id)}
                        >
                          <Trash2 className="w-4 h-4" />
                        </Button>
                      </div>
                    </div>
                  </motion.div>
                ))}

                {fields.length === 0 && (
                  <div className="text-center py-12 border-2 border-dashed border-gray-200 rounded-lg">
                    <Plus className="w-8 h-8 text-gray-400 mx-auto mb-2" />
                    <p className="text-gray-500">Click a field type to add it to your form</p>
                  </div>
                )}
              </div>
            </div>

            {/* Properties Panel */}
            <div className="bg-white rounded-xl border border-gray-200 p-4">
              <h3 className="font-semibold text-gray-900 mb-4">Properties</h3>
              <div className="space-y-4">
                <div>
                  <Label>Form Description</Label>
                  <Textarea
                    value={formDescription}
                    onChange={(e) => {
                      setFormDescription(e.target.value);
                      setSaveStatus('unsaved');
                    }}
                    placeholder="Describe your event..."
                    className="mt-1.5"
                  />
                </div>
                <div>
                  <Label>Event Date</Label>
                  <Input type="date" className="mt-1.5" />
                </div>
                <div>
                  <Label>Maximum Registrations</Label>
                  <Input type="number" placeholder="Unlimited" className="mt-1.5" />
                </div>
                <div className="flex items-center justify-between">
                  <Label className="cursor-pointer">Require Approval</Label>
                  <Switch />
                </div>
                <div className="flex items-center justify-between">
                  <Label className="cursor-pointer">Allow Multiple Submissions</Label>
                  <Switch />
                </div>
              </div>
            </div>
          </div>
        </TabsContent>

        <TabsContent value="settings">
          <div className="max-w-2xl bg-white rounded-xl border border-gray-200 p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-6">Form Settings</h3>
            <div className="space-y-6">
              <div>
                <Label>Confirmation Message</Label>
                <Textarea
                  placeholder="Thank you for registering!"
                  className="mt-1.5"
                />
              </div>
              <div>
                <Label>Redirect URL (optional)</Label>
                <Input placeholder="https://..." className="mt-1.5" />
              </div>
              <div className="flex items-center justify-between">
                <div>
                  <Label className="cursor-pointer">Send Confirmation Email</Label>
                  <p className="text-sm text-gray-500">Send an email to respondents after submission</p>
                </div>
                <Switch />
              </div>
              <div className="flex items-center justify-between">
                <div>
                  <Label className="cursor-pointer">Show Progress Bar</Label>
                  <p className="text-sm text-gray-500">Display progress for multi-page forms</p>
                </div>
                <Switch />
              </div>
            </div>
          </div>
        </TabsContent>

        <TabsContent value="preview">
          <div className="max-w-lg mx-auto bg-white rounded-xl border border-gray-200 p-8">
            <div className="text-center pb-6 border-b border-gray-100">
              <h2 className="text-xl font-semibold text-gray-900">{formTitle}</h2>
              {formDescription && (
                <p className="text-sm text-gray-500 mt-1">{formDescription}</p>
              )}
            </div>
            <div className="mt-6 space-y-4">
              {fields.map((field) => (
                <div key={field.id}>
                  <Label className="flex items-center gap-1">
                    {field.label}
                    {field.required && <span className="text-red-500">*</span>}
                  </Label>
                  <Input placeholder={`Enter ${field.label.toLowerCase()}`} className="mt-1.5" />
                </div>
              ))}
              <Button className="w-full mt-6">Submit</Button>
            </div>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  );
}
