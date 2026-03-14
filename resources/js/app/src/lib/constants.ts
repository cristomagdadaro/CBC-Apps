// App Constants
export const APP_NAME = 'OneCBC Portal';
export const APP_VERSION = '2.0.0';
export const APP_TAGLINE = 'Your gateway to DA-Crop Biotechnology Center\'s proprietary web apps and services.';
export const APP_SUBTAGLINE = 'Better Crops, Better Lives';

// API Endpoints (mock)
export const API_BASE_URL = '/api';

// Pagination
export const DEFAULT_PAGE_SIZE = 10;
export const PAGE_SIZE_OPTIONS = [10, 25, 50, 100];

// Date Formats
export const DATE_FORMAT = 'MMM dd, yyyy';
export const DATETIME_FORMAT = 'MMM dd, yyyy h:mm a';
export const TIME_FORMAT = 'h:mm a';

// User Roles
export const USER_ROLES = {
  ADMIN: 'admin' as const,
  LAB_MANAGER: 'lab_manager' as const,
  ICT_MANAGER: 'ict_manager' as const,
  ADMIN_ASSISTANT: 'admin_assistant' as const,
  RESEARCHER: 'researcher' as const,
  GUEST: 'guest' as const,
};

export const USER_ROLE_LABELS: Record<string, string> = {
  admin: 'Administrator',
  lab_manager: 'Laboratory Manager',
  ict_manager: 'ICT Manager',
  admin_assistant: 'Administrative Assistant',
  researcher: 'Researcher',
  guest: 'Guest',
};

// Status Colors
export const STATUS_COLORS: Record<string, { bg: string; text: string; border: string }> = {
  draft: { bg: 'bg-gray-100', text: 'text-gray-700', border: 'border-gray-200' },
  published: { bg: 'bg-green-100', text: 'text-green-700', border: 'border-green-200' },
  expired: { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-200' },
  archived: { bg: 'bg-gray-100', text: 'text-gray-500', border: 'border-gray-200' },
  pending: { bg: 'bg-yellow-100', text: 'text-yellow-700', border: 'border-yellow-200' },
  approved: { bg: 'bg-green-100', text: 'text-green-700', border: 'border-green-200' },
  rejected: { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-200' },
  active: { bg: 'bg-blue-100', text: 'text-blue-700', border: 'border-blue-200' },
  completed: { bg: 'bg-green-100', text: 'text-green-700', border: 'border-green-200' },
  in_progress: { bg: 'bg-blue-100', text: 'text-blue-700', border: 'border-blue-200' },
  scheduled: { bg: 'bg-purple-100', text: 'text-purple-700', border: 'border-purple-200' },
  delayed: { bg: 'bg-orange-100', text: 'text-orange-700', border: 'border-orange-200' },
  cancelled: { bg: 'bg-gray-100', text: 'text-gray-500', border: 'border-gray-200' },
  available: { bg: 'bg-green-100', text: 'text-green-700', border: 'border-green-200' },
  in_use: { bg: 'bg-blue-100', text: 'text-blue-700', border: 'border-blue-200' },
  maintenance: { bg: 'bg-orange-100', text: 'text-orange-700', border: 'border-orange-200' },
  retired: { bg: 'bg-gray-100', text: 'text-gray-500', border: 'border-gray-200' },
  under_review: { bg: 'bg-yellow-100', text: 'text-yellow-700', border: 'border-yellow-200' },
  low: { bg: 'bg-blue-100', text: 'text-blue-700', border: 'border-blue-200' },
  medium: { bg: 'bg-yellow-100', text: 'text-yellow-700', border: 'border-yellow-200' },
  high: { bg: 'bg-orange-100', text: 'text-orange-700', border: 'border-orange-200' },
  urgent: { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-200' },
};

// Priority Config
export const PRIORITY_CONFIG = {
  low: { color: 'blue', label: 'Low' },
  medium: { color: 'yellow', label: 'Medium' },
  high: { color: 'orange', label: 'High' },
  urgent: { color: 'red', label: 'Urgent' },
};

// Inventory Categories
export const INVENTORY_CATEGORIES = [
  'Laboratory Supplies',
  'Chemicals & Reagents',
  'Glassware',
  'Plasticware',
  'Equipment Parts',
  'Safety Equipment',
  'Office Supplies',
  'ICT Equipment',
  'Other',
];

// Equipment Categories
export const EQUIPMENT_CATEGORIES = [
  'Molecular Biology',
  'Microscopy',
  'PCR & qPCR',
  'Sequencing',
  'Spectrophotometry',
  'Centrifugation',
  'Incubation',
  'Sample Preparation',
  'General Laboratory',
  'ICT Equipment',
];

// Vehicle Types
export const VEHICLE_TYPES = [
  { id: 'sedan', name: 'Sedan', capacity: 4 },
  { id: 'suv', name: 'SUV', capacity: 7 },
  { id: 'van', name: 'Van', capacity: 15 },
  { id: 'truck', name: 'Truck', capacity: 3 },
  { id: 'pickup', name: 'Pickup', capacity: 5 },
];

// Venue Types
export const VENUE_TYPES = [
  { id: 'conference_room', name: 'Conference Room', capacity: 20 },
  { id: 'meeting_room', name: 'Meeting Room', capacity: 10 },
  { id: 'training_hall', name: 'Training Hall', capacity: 50 },
  { id: 'auditorium', name: 'Auditorium', capacity: 200 },
  { id: 'laboratory', name: 'Laboratory', capacity: 30 },
];

// Hostel Room Types
export const HOSTEL_ROOM_TYPES = [
  { id: 'single', name: 'Single Room', capacity: 1 },
  { id: 'double', name: 'Double Room', capacity: 2 },
  { id: 'dormitory', name: 'Dormitory', capacity: 8 },
];

// Sidebar Navigation
export const SIDEBAR_NAVIGATION = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: 'LayoutDashboard',
    roles: ['admin', 'lab_manager', 'ict_manager', 'admin_assistant', 'researcher'],
  },
  {
    title: 'Event Forms',
    href: '/forms',
    icon: 'Calendar',
    roles: ['admin', 'ict_manager', 'admin_assistant'],
    children: [
      { title: 'All Forms', href: '/forms', icon: 'List' },
      { title: 'Create Form', href: '/forms/builder', icon: 'Plus' },
      { title: 'Submissions', href: '/forms/submissions', icon: 'FileText' },
    ],
  },
  {
    title: 'Inventory',
    href: '/inventory/items',
    icon: 'Package',
    roles: ['admin', 'lab_manager', 'admin_assistant'],
    children: [
      { title: 'Items', href: '/inventory/items', icon: 'Box' },
      { title: 'Transactions', href: '/inventory/transactions', icon: 'ArrowLeftRight' },
      { title: 'Barcodes', href: '/inventory/barcodes', icon: 'QrCode' },
      { title: 'Suppliers', href: '/inventory/suppliers', icon: 'Truck' },
    ],
  },
  {
    title: 'Laboratory',
    href: '/laboratory/equipments',
    icon: 'Microscope',
    roles: ['admin', 'lab_manager', 'researcher'],
    children: [
      { title: 'Equipment', href: '/laboratory/equipments', icon: 'Cpu' },
      { title: 'Usage Logger', href: '/laboratory/usage-logger', icon: 'Clock' },
      { title: 'Monitoring', href: '/laboratory/monitoring', icon: 'Activity' },
    ],
  },
  {
    title: 'FES Requests',
    href: '/fes/approvals',
    icon: 'ClipboardList',
    roles: ['admin', 'lab_manager', 'admin_assistant'],
  },
  {
    title: 'Rentals',
    href: '/rentals/vehicle',
    icon: 'Car',
    roles: ['admin', 'admin_assistant', 'researcher'],
    children: [
      { title: 'Vehicle', href: '/rentals/vehicle', icon: 'Truck' },
      { title: 'Venue', href: '/rentals/venue', icon: 'Building' },
      { title: 'Hostel', href: '/rentals/hostel', icon: 'Bed' },
    ],
  },
  {
    title: 'Certificates',
    href: '/certificates',
    icon: 'Award',
    roles: ['admin', 'ict_manager'],
  },
  {
    title: 'Settings',
    href: '/settings/users',
    icon: 'Settings',
    roles: ['admin'],
    children: [
      { title: 'Users', href: '/settings/users', icon: 'Users' },
      { title: 'Roles', href: '/settings/roles', icon: 'Shield' },
      { title: 'Options', href: '/settings/options', icon: 'Sliders' },
    ],
  },
];

// Public Services
export const PUBLIC_SERVICES = [
  {
    id: 'event-forms',
    title: 'Event Forms',
    description: 'Register and participate in DA-CBC events with comprehensive event forms',
    icon: 'CalendarDays',
    href: '/forms/event',
    color: 'bg-blue-500',
  },
  {
    id: 'fes-request',
    title: 'FES Request Form',
    description: 'Facility, Equipment, and Supplies Request Form (Borrower\'s Slip)',
    icon: 'ClipboardList',
    href: '/forms/request-to-use',
    color: 'bg-green-500',
  },
  {
    id: 'equipment-logger',
    title: 'Equipment Logger',
    description: 'Access and track laboratory and ICT equipment availability and usage logs',
    icon: 'Microscope',
    href: '/laboratory/equipments',
    color: 'bg-purple-500',
  },
  {
    id: 'supplies-checkout',
    title: 'Supplies Checkout',
    description: 'Check out and track supplies and equipment from our inventory',
    icon: 'Package',
    href: '/inventory/outgoing',
    color: 'bg-orange-500',
  },
  {
    id: 'vehicle-rental',
    title: 'Vehicle Rental',
    description: 'Reserve and book available vehicles for your research activities',
    icon: 'Truck',
    href: '/rental/vehicle',
    color: 'bg-red-500',
  },
  {
    id: 'venue-rental',
    title: 'Venue Rental',
    description: 'Reserve meeting rooms and event spaces for your activities',
    icon: 'Building2',
    href: '/rental/venue',
    color: 'bg-teal-500',
  },
  {
    id: 'file-report',
    title: 'File a Report',
    description: 'Report incidents, maintenance issues, or equipment damage',
    icon: 'Flag',
    href: '/file-report/create-guest',
    color: 'bg-pink-500',
  },
  {
    id: 'experiment-monitoring',
    title: 'Experiment Monitoring',
    description: 'Monitor and track ongoing experiments in the laboratory',
    icon: 'FlaskConical',
    href: '/laboratory/experiments-monitoring',
    color: 'bg-indigo-500',
  },
];

// Animation Durations
export const ANIMATION_DURATION = {
  fast: 0.15,
  normal: 0.2,
  standard: 0.3,
  slow: 0.5,
};

// Toast Config
export const TOAST_CONFIG = {
  duration: 5000,
  position: 'top-right' as const,
};
