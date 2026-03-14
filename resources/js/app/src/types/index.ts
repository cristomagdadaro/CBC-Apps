// User Types
export type UserRole = 'admin' | 'lab_manager' | 'ict_manager' | 'admin_assistant' | 'researcher' | 'guest';

export interface User {
  id: string;
  name: string;
  email: string;
  role: UserRole;
  department?: string;
  avatar?: string;
  phone?: string;
  createdAt: string;
  lastLogin?: string;
  isActive: boolean;
}

// Navigation Types
export interface NavItem {
  title: string;
  href: string;
  icon: string;
  roles?: UserRole[];
  children?: NavItem[];
}

// Dashboard Types
export interface StatMetric {
  title: string;
  value: number;
  icon: string;
  trend?: number;
  trendLabel?: string;
  color?: 'primary' | 'success' | 'warning' | 'error' | 'info';
}

export interface ActivityItem {
  id: string;
  user: User;
  action: string;
  target: string;
  timestamp: string;
  type: 'create' | 'update' | 'delete' | 'approve' | 'submit';
}

export interface PendingItem {
  id: string;
  type: 'fes_request' | 'rental' | 'form_submission';
  title: string;
  requestor: string;
  submittedAt: string;
  priority: 'low' | 'medium' | 'high' | 'urgent';
}

// Form Types
export type FormFieldType = 
  | 'text' | 'textarea' | 'email' | 'number' | 'phone'
  | 'select' | 'multiselect' | 'radio' | 'checkbox'
  | 'date' | 'time' | 'datetime'
  | 'file' | 'signature' | 'rating'
  | 'divider' | 'pagebreak' | 'richtext';

export interface FormField {
  id: string;
  type: FormFieldType;
  label: string;
  placeholder?: string;
  helpText?: string;
  required?: boolean;
  options?: string[];
  validation?: {
    min?: number;
    max?: number;
    pattern?: string;
    message?: string;
  };
  conditional?: {
    field: string;
    value: string;
    operator: 'equals' | 'not_equals' | 'contains';
  };
}

export interface EventForm {
  id: string;
  title: string;
  description?: string;
  status: 'draft' | 'published' | 'expired' | 'archived';
  eventDate?: string;
  eventEndDate?: string;
  location?: string;
  fields: FormField[];
  registrationsCount: number;
  maxRegistrations?: number;
  createdBy: User;
  createdAt: string;
  updatedAt: string;
  allowMultipleSubmissions?: boolean;
  requireApproval?: boolean;
}

export interface FormSubmission {
  id: string;
  formId: string;
  formTitle: string;
  submitterName: string;
  submitterEmail: string;
  data: Record<string, any>;
  status: 'pending' | 'approved' | 'rejected';
  submittedAt: string;
  reviewedBy?: User;
  reviewedAt?: string;
  reviewNotes?: string;
}

// Inventory Types
export interface InventoryItem {
  id: string;
  name: string;
  description?: string;
  sku: string;
  barcode?: string;
  category: string;
  subcategory?: string;
  images?: string[];
  quantity: number;
  minStock: number;
  unitOfMeasure: string;
  location?: string;
  shelf?: string;
  supplier?: Supplier;
  cost?: number;
  createdAt: string;
  updatedAt: string;
}

export interface Supplier {
  id: string;
  name: string;
  contactPerson?: string;
  email?: string;
  phone?: string;
  address?: string;
  website?: string;
  notes?: string;
}

export type TransactionType = 'incoming' | 'outgoing' | 'transfer' | 'adjustment';

export interface InventoryTransaction {
  id: string;
  itemId: string;
  itemName: string;
  type: TransactionType;
  quantity: number;
  previousQuantity: number;
  newQuantity: number;
  personnel: User;
  reference?: string;
  notes?: string;
  createdAt: string;
}

// Laboratory Types
export interface Equipment {
  id: string;
  name: string;
  model?: string;
  manufacturer?: string;
  serialNumber?: string;
  category: string;
  location: string;
  description?: string;
  specifications?: string;
  images?: string[];
  status: 'available' | 'in_use' | 'maintenance' | 'retired';
  requiresTraining?: boolean;
  trainingRequiredFor?: UserRole[];
  createdAt: string;
  lastMaintenance?: string;
  nextMaintenance?: string;
}

export interface EquipmentUsage {
  id: string;
  equipmentId: string;
  equipmentName: string;
  user: User;
  purpose: string;
  projectCode?: string;
  startTime: string;
  endTime?: string;
  duration?: number;
  notes?: string;
  status: 'active' | 'completed';
}

export interface Experiment {
  id: string;
  title: string;
  description?: string;
  researcher: User;
  projectCode?: string;
  startDate: string;
  expectedEndDate?: string;
  actualEndDate?: string;
  status: 'scheduled' | 'in_progress' | 'completed' | 'delayed' | 'cancelled';
  equipment?: Equipment[];
  notes?: string;
  createdAt: string;
  updatedAt: string;
}

// FES Request Types
export type FESRequestStatus = 'draft' | 'submitted' | 'under_review' | 'approved' | 'rejected' | 'completed';

export interface FESRequest {
  id: string;
  reference: string;
  requestor: {
    name: string;
    email: string;
    phone: string;
    affiliation: string;
    philriceId?: string;
  };
  purpose: string;
  projectCode?: string;
  startDate: string;
  endDate: string;
  status: FESRequestStatus;
  items: FESRequestItem[];
  approver?: User;
  approverNotes?: string;
  submittedAt: string;
  reviewedAt?: string;
  createdAt: string;
  updatedAt: string;
}

export interface FESRequestItem {
  id: string;
  type: 'facility' | 'equipment' | 'supply';
  itemId: string;
  itemName: string;
  quantity: number;
  notes?: string;
}

// Rental Types
export type RentalType = 'vehicle' | 'venue' | 'hostel';
export type RentalStatus = 'pending' | 'approved' | 'rejected' | 'active' | 'completed' | 'cancelled';

export interface RentalItem {
  id: string;
  type: RentalType;
  name: string;
  description?: string;
  images?: string[];
  capacity?: number;
  amenities?: string[];
  rate?: number;
  rateUnit?: 'hour' | 'day' | 'week';
  location?: string;
  status: 'available' | 'in_use' | 'maintenance' | 'unavailable';
}

export interface RentalRequest {
  id: string;
  reference: string;
  type: RentalType;
  item: RentalItem;
  requestor: User;
  startDate: string;
  endDate: string;
  startTime?: string;
  endTime?: string;
  purpose: string;
  attendees?: number;
  specialRequests?: string;
  driverInfo?: {
    name: string;
    licenseNumber?: string;
    contact?: string;
  };
  status: RentalStatus;
  totalCost?: number;
  approvedBy?: User;
  approvedAt?: string;
  approvalNotes?: string;
  createdAt: string;
  updatedAt: string;
}

// Certificate Types
export interface CertificateTemplate {
  id: string;
  name: string;
  description?: string;
  thumbnail?: string;
  type: 'participation' | 'completion' | 'achievement' | 'custom';
  design: {
    background?: string;
    layout: 'portrait' | 'landscape';
    primaryColor: string;
    secondaryColor?: string;
    fontFamily?: string;
  };
  fields: string[];
}

export interface Certificate {
  id: string;
  templateId: string;
  recipientName: string;
  recipientEmail?: string;
  eventName?: string;
  eventDate?: string;
  data: Record<string, string>;
  generatedAt: string;
  generatedBy: User;
  downloadUrl?: string;
}

// Notification Types
export interface Notification {
  id: string;
  userId: string;
  title: string;
  message: string;
  type: 'info' | 'success' | 'warning' | 'error';
  read: boolean;
  actionUrl?: string;
  createdAt: string;
}

// Report Types
export interface ReportFilter {
  dateRange?: { start: string; end: string };
  category?: string;
  status?: string;
  user?: string;
}

export interface UsageReport {
  period: string;
  totalRequests: number;
  approvedRequests: number;
  rejectedRequests: number;
  pendingRequests: number;
  topEquipment: { name: string; usageCount: number }[];
  topUsers: { name: string; requestCount: number }[];
}
