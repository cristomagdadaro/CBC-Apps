import React, { useState } from 'react';
import { motion } from 'framer-motion';
import {
  Search,
  Filter,
  CheckCircle,
  XCircle,
  Eye,
  MoreHorizontal,
  ClipboardList,
  Clock,
  AlertCircle,
  FileText,
  Download,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { PageHeader } from '@/components/layout/PageHeader';
import { StatusBadge } from '@/components/common/StatusBadge';
import { mockFESRequests } from '@/lib/mockData';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { format } from 'date-fns';

const tabs = [
  { id: 'pending', label: 'Pending', count: 12 },
  { id: 'approved', label: 'Approved', count: 145 },
  { id: 'rejected', label: 'Rejected', count: 8 },
  { id: 'all', label: 'All', count: 165 },
];

const priorityConfig: Record<string, { color: string; label: string }> = {
  low: { color: 'bg-blue-100 text-blue-700', label: 'Low' },
  medium: { color: 'bg-yellow-100 text-yellow-700', label: 'Medium' },
  high: { color: 'bg-orange-100 text-orange-700', label: 'High' },
  urgent: { color: 'bg-red-100 text-red-700', label: 'Urgent' },
};

export function ApprovalQueuePage() {
  const [activeTab, setActiveTab] = useState('pending');
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedRequest, setSelectedRequest] = useState<typeof mockFESRequests[0] | null>(null);
  const [approvalNote, setApprovalNote] = useState('');

  const filteredRequests = mockFESRequests.filter((req) => {
    if (activeTab !== 'all' && req.status !== activeTab) return false;
    if (searchQuery && !req.requestor.name.toLowerCase().includes(searchQuery.toLowerCase())) return false;
    return true;
  });

  return (
    <div className="space-y-6">
      <PageHeader
        title="FES Request Approvals"
        subtitle="Review and approve facility, equipment, and supply requests"
      />

      {/* Tabs and Filters */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div className="flex items-center gap-1 bg-gray-100 p-1 rounded-lg">
          {tabs.map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={cn(
                'px-3 py-1.5 text-sm font-medium rounded-md transition-all',
                activeTab === tab.id
                  ? 'bg-white text-gray-900 shadow-sm'
                  : 'text-gray-500 hover:text-gray-700'
              )}
            >
              {tab.label}
              <span className={cn(
                'ml-1.5 px-1.5 py-0.5 text-xs rounded-full',
                activeTab === tab.id
                  ? 'bg-primary-100 text-primary-700'
                  : 'bg-gray-200 text-gray-600'
              )}>
                {tab.count}
              </span>
            </button>
          ))}
        </div>

        <div className="flex items-center gap-2">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <Input
              type="text"
              placeholder="Search requests..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-9 w-64"
            />
          </div>
          <Button variant="outline" size="icon">
            <Filter className="w-4 h-4" />
          </Button>
        </div>
      </div>

      {/* Requests Table */}
      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <Table>
          <TableHeader>
            <TableRow className="bg-gray-50/50">
              <TableHead>Reference</TableHead>
              <TableHead>Requestor</TableHead>
              <TableHead>Purpose</TableHead>
              <TableHead>Dates</TableHead>
              <TableHead>Status</TableHead>
              <TableHead className="w-[120px]">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {filteredRequests.map((request) => (
              <TableRow
                key={request.id}
                className="group hover:bg-gray-50/80 transition-colors"
              >
                <TableCell>
                  <span className="font-mono text-sm font-medium text-primary-600">
                    {request.reference}
                  </span>
                </TableCell>
                <TableCell>
                  <div>
                    <p className="font-medium text-gray-900">{request.requestor.name}</p>
                    <p className="text-sm text-gray-500">{request.requestor.affiliation}</p>
                  </div>
                </TableCell>
                <TableCell>
                  <p className="text-sm text-gray-600 line-clamp-1">{request.purpose}</p>
                </TableCell>
                <TableCell>
                  <div className="text-sm text-gray-600">
                    <p>{format(new Date(request.startDate), 'MMM dd')} - {format(new Date(request.endDate), 'MMM dd, yyyy')}</p>
                  </div>
                </TableCell>
                <TableCell>
                  <StatusBadge status={request.status} size="sm" />
                </TableCell>
                <TableCell>
                  <div className="flex items-center gap-1">
                    <Button
                      variant="ghost"
                      size="icon"
                      className="h-8 w-8"
                      onClick={() => setSelectedRequest(request)}
                    >
                      <Eye className="w-4 h-4" />
                    </Button>
                    {request.status === 'submitted' && (
                      <>
                        <Button
                          variant="ghost"
                          size="icon"
                          className="h-8 w-8 text-green-600 hover:text-green-700 hover:bg-green-50"
                        >
                          <CheckCircle className="w-4 h-4" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon"
                          className="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50"
                        >
                          <XCircle className="w-4 h-4" />
                        </Button>
                      </>
                    )}
                  </div>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>

      {/* Request Detail Dialog */}
      <Dialog open={!!selectedRequest} onOpenChange={() => setSelectedRequest(null)}>
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <ClipboardList className="w-5 h-5 text-primary-600" />
              {selectedRequest?.reference}
            </DialogTitle>
          </DialogHeader>

          {selectedRequest && (
            <div className="space-y-6">
              {/* Status Banner */}
              <div className={cn(
                'p-4 rounded-lg',
                selectedRequest.status === 'approved' && 'bg-green-50 border border-green-200',
                selectedRequest.status === 'submitted' && 'bg-yellow-50 border border-yellow-200',
                selectedRequest.status === 'rejected' && 'bg-red-50 border border-red-200'
              )}>
                <div className="flex items-center gap-2">
                  <StatusBadge status={selectedRequest.status} />
                  <span className="text-sm text-gray-500">
                    Submitted {format(new Date(selectedRequest.submittedAt), 'MMM dd, yyyy h:mm a')}
                  </span>
                </div>
              </div>

              {/* Requestor Info */}
              <div>
                <h3 className="text-sm font-medium text-gray-500 mb-2">Requestor Information</h3>
                <div className="bg-gray-50 rounded-lg p-4 space-y-2">
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <span className="text-sm text-gray-500">Name</span>
                      <p className="font-medium">{selectedRequest.requestor.name}</p>
                    </div>
                    <div>
                      <span className="text-sm text-gray-500">Email</span>
                      <p className="font-medium">{selectedRequest.requestor.email}</p>
                    </div>
                    <div>
                      <span className="text-sm text-gray-500">Phone</span>
                      <p className="font-medium">{selectedRequest.requestor.phone}</p>
                    </div>
                    <div>
                      <span className="text-sm text-gray-500">Affiliation</span>
                      <p className="font-medium">{selectedRequest.requestor.affiliation}</p>
                    </div>
                  </div>
                </div>
              </div>

              {/* Request Details */}
              <div>
                <h3 className="text-sm font-medium text-gray-500 mb-2">Request Details</h3>
                <div className="bg-gray-50 rounded-lg p-4 space-y-3">
                  <div>
                    <span className="text-sm text-gray-500">Purpose</span>
                    <p className="mt-1">{selectedRequest.purpose}</p>
                  </div>
                  {selectedRequest.projectCode && (
                    <div>
                      <span className="text-sm text-gray-500">Project Code</span>
                      <p className="font-mono">{selectedRequest.projectCode}</p>
                    </div>
                  )}
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <span className="text-sm text-gray-500">Start Date</span>
                      <p className="font-medium">{format(new Date(selectedRequest.startDate), 'MMM dd, yyyy')}</p>
                    </div>
                    <div>
                      <span className="text-sm text-gray-500">End Date</span>
                      <p className="font-medium">{format(new Date(selectedRequest.endDate), 'MMM dd, yyyy')}</p>
                    </div>
                  </div>
                </div>
              </div>

              {/* Requested Items */}
              {selectedRequest.items.length > 0 && (
                <div>
                  <h3 className="text-sm font-medium text-gray-500 mb-2">Requested Items</h3>
                  <div className="bg-gray-50 rounded-lg p-4">
                    <div className="space-y-2">
                      {selectedRequest.items.map((item, index) => (
                        <div key={index} className="flex items-center gap-2">
                          <Badge variant="secondary" className="capitalize">{item.type}</Badge>
                          <span className="font-medium">{item.itemName}</span>
                          <span className="text-gray-500">x{item.quantity}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              )}

              {/* Approval Notes */}
              {selectedRequest.status === 'submitted' && (
                <div>
                  <h3 className="text-sm font-medium text-gray-500 mb-2">Approval Notes (optional)</h3>
                  <Textarea
                    placeholder="Add notes or conditions for approval..."
                    value={approvalNote}
                    onChange={(e) => setApprovalNote(e.target.value)}
                  />
                </div>
              )}

              {/* Actions */}
              {selectedRequest.status === 'submitted' && (
                <div className="flex gap-2 pt-4">
                  <Button className="flex-1 bg-green-600 hover:bg-green-700">
                    <CheckCircle className="w-4 h-4 mr-2" />
                    Approve Request
                  </Button>
                  <Button variant="outline" className="flex-1 text-red-600 border-red-200 hover:bg-red-50">
                    <XCircle className="w-4 h-4 mr-2" />
                    Reject
                  </Button>
                </div>
              )}

              {selectedRequest.status === 'approved' && (
                <div className="flex gap-2 pt-4">
                  <Button variant="outline" className="flex-1">
                    <FileText className="w-4 h-4 mr-2" />
                    View Form
                  </Button>
                  <Button variant="outline" className="flex-1">
                    <Download className="w-4 h-4 mr-2" />
                    Download PDF
                  </Button>
                </div>
              )}
            </div>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
}
