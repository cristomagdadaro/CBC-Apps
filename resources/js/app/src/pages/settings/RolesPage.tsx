import React, { useState } from 'react';
import { motion } from 'framer-motion';
import {
  Shield,
  Check,
  X,
  Edit,
  Plus,
  Trash2,
  Save,
  User,
  Users,
  Microscope,
  Package,
  Car,
  Award,
  Settings,
  LayoutDashboard,
  Calendar,
  ClipboardList,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { PageHeader } from '@/components/layout/PageHeader';
import { USER_ROLE_LABELS } from '@/lib/mockData';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

const modules = [
  { id: 'dashboard', name: 'Dashboard', icon: LayoutDashboard },
  { id: 'forms', name: 'Event Forms', icon: Calendar },
  { id: 'inventory', name: 'Inventory', icon: Package },
  { id: 'laboratory', name: 'Laboratory', icon: Microscope },
  { id: 'fes', name: 'FES Requests', icon: ClipboardList },
  { id: 'rentals', name: 'Rentals', icon: Car },
  { id: 'certificates', name: 'Certificates', icon: Award },
  { id: 'settings', name: 'Settings', icon: Settings },
];

const permissions = [
  { id: 'view', label: 'View' },
  { id: 'create', label: 'Create' },
  { id: 'edit', label: 'Edit' },
  { id: 'delete', label: 'Delete' },
  { id: 'approve', label: 'Approve' },
  { id: 'export', label: 'Export' },
];

const defaultRoles = [
  {
    id: 'admin',
    name: 'Administrator',
    description: 'Full system access with all permissions',
    userCount: 2,
    color: 'bg-purple-100 text-purple-700',
  },
  {
    id: 'lab_manager',
    name: 'Laboratory Manager',
    description: 'Manage laboratory equipment and FES requests',
    userCount: 3,
    color: 'bg-blue-100 text-blue-700',
  },
  {
    id: 'ict_manager',
    name: 'ICT Manager',
    description: 'Manage forms and certificates',
    userCount: 2,
    color: 'bg-green-100 text-green-700',
  },
  {
    id: 'admin_assistant',
    name: 'Administrative Assistant',
    description: 'Manage rentals and approvals',
    userCount: 4,
    color: 'bg-yellow-100 text-yellow-700',
  },
  {
    id: 'researcher',
    name: 'Researcher',
    description: 'Access to laboratory and rentals',
    userCount: 25,
    color: 'bg-orange-100 text-orange-700',
  },
  {
    id: 'guest',
    name: 'Guest',
    description: 'Limited access to public forms',
    userCount: 120,
    color: 'bg-gray-100 text-gray-700',
  },
];

export function RolesPage() {
  const [selectedRole, setSelectedRole] = useState(defaultRoles[0]);
  const [showAddDialog, setShowAddDialog] = useState(false);
  const [rolePermissions, setRolePermissions] = useState<Record<string, string[]>>({
    dashboard: ['view'],
    forms: ['view', 'create', 'edit', 'delete'],
    inventory: ['view', 'create', 'edit'],
    laboratory: ['view', 'create', 'edit'],
    fes: ['view', 'approve'],
    rentals: ['view', 'create'],
    certificates: ['view', 'create'],
    settings: [],
  });

  const togglePermission = (module: string, permission: string) => {
    setRolePermissions((prev) => {
      const current = prev[module] || [];
      const updated = current.includes(permission)
        ? current.filter((p) => p !== permission)
        : [...current, permission];
      return { ...prev, [module]: updated };
    });
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Roles & Permissions"
        subtitle="Configure role-based access control"
        actions={
          <Button onClick={() => setShowAddDialog(true)}>
            <Plus className="w-4 h-4 mr-2" />
            Create Role
          </Button>
        }
      />

      <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {/* Role List */}
        <div className="lg:col-span-1 space-y-3">
          {defaultRoles.map((role) => (
            <button
              key={role.id}
              onClick={() => setSelectedRole(role)}
              className={cn(
                'w-full text-left p-4 rounded-xl border transition-all',
                selectedRole.id === role.id
                  ? 'border-primary-300 bg-primary-50 shadow-sm'
                  : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
              )}
            >
              <div className="flex items-start justify-between">
                <div>
                  <h3 className="font-semibold text-gray-900">{role.name}</h3>
                  <p className="text-sm text-gray-500 mt-1 line-clamp-2">
                    {role.description}
                  </p>
                </div>
                <Badge className={role.color}>{role.userCount}</Badge>
              </div>
            </button>
          ))}
        </div>

        {/* Permission Matrix */}
        <div className="lg:col-span-3">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between">
              <div>
                <CardTitle>{selectedRole.name}</CardTitle>
                <p className="text-sm text-gray-500 mt-1">{selectedRole.description}</p>
              </div>
              <div className="flex items-center gap-2">
                <Button variant="outline" size="sm">
                  <Edit className="w-4 h-4 mr-2" />
                  Edit
                </Button>
                <Button size="sm">
                  <Save className="w-4 h-4 mr-2" />
                  Save Changes
                </Button>
              </div>
            </CardHeader>
            <CardContent>
              <div className="border rounded-lg overflow-hidden">
                <table className="w-full">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="text-left px-4 py-3 text-sm font-medium text-gray-700">Module</th>
                      {permissions.map((perm) => (
                        <th
                          key={perm.id}
                          className="text-center px-2 py-3 text-sm font-medium text-gray-700 w-[80px]"
                        >
                          {perm.label}
                        </th>
                      ))}
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {modules.map((module) => {
                      const Icon = module.icon;
                      const modulePerms = rolePermissions[module.id] || [];
                      
                      return (
                        <tr key={module.id} className="hover:bg-gray-50/50">
                          <td className="px-4 py-3">
                            <div className="flex items-center gap-3">
                              <div className="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center">
                                <Icon className="w-4 h-4 text-primary-600" />
                              </div>
                              <span className="font-medium text-gray-900">{module.name}</span>
                            </div>
                          </td>
                          {permissions.map((perm) => (
                            <td key={perm.id} className="px-2 py-3 text-center">
                              <button
                                onClick={() => togglePermission(module.id, perm.id)}
                                className={cn(
                                  'w-6 h-6 rounded flex items-center justify-center transition-colors',
                                  modulePerms.includes(perm.id)
                                    ? 'bg-primary-500 text-white'
                                    : 'bg-gray-100 text-gray-400 hover:bg-gray-200'
                                )}
                              >
                                {modulePerms.includes(perm.id) ? (
                                  <Check className="w-4 h-4" />
                                ) : (
                                  <X className="w-4 h-4" />
                                )}
                              </button>
                            </td>
                          ))}
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      {/* Add Role Dialog */}
      <Dialog open={showAddDialog} onOpenChange={setShowAddDialog}>
        <DialogContent className="max-w-lg">
          <DialogHeader>
            <DialogTitle>Create New Role</DialogTitle>
          </DialogHeader>
          
          <div className="space-y-4">
            <div className="space-y-2">
              <Label>Role Name *</Label>
              <Input placeholder="e.g., Department Head" />
            </div>

            <div className="space-y-2">
              <Label>Description</Label>
              <Input placeholder="Brief description of this role" />
            </div>

            <div className="space-y-2">
              <Label>Base Role (optional)</Label>
              <select className="w-full p-2 border rounded-lg">
                <option value="">Start from scratch</option>
                {defaultRoles.map((role) => (
                  <option key={role.id} value={role.id}>
                    Copy from {role.name}
                  </option>
                ))}
              </select>
            </div>

            <div className="flex gap-2 pt-4">
              <Button variant="outline" className="flex-1" onClick={() => setShowAddDialog(false)}>
                Cancel
              </Button>
              <Button className="flex-1">
                <Plus className="w-4 h-4 mr-2" />
                Create Role
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
}
