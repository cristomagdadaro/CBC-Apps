import React, { useState } from 'react';
import { motion } from 'framer-motion';
import {
  Plus,
  Search,
  Filter,
  ArrowDownLeft,
  ArrowUpRight,
  ArrowLeftRight,
  RefreshCw,
  Download,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { PageHeader } from '@/components/layout/PageHeader';
import { mockTransactions } from '@/lib/mockData';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { format } from 'date-fns';

const tabs = [
  { id: 'all', label: 'All', count: 245 },
  { id: 'incoming', label: 'Incoming', count: 89 },
  { id: 'outgoing', label: 'Outgoing', count: 156 },
];

const typeConfig: Record<string, { icon: React.ElementType; color: string; label: string }> = {
  incoming: { icon: ArrowDownLeft, color: 'bg-green-100 text-green-700', label: 'Incoming' },
  outgoing: { icon: ArrowUpRight, color: 'bg-blue-100 text-blue-700', label: 'Outgoing' },
  transfer: { icon: ArrowLeftRight, color: 'bg-purple-100 text-purple-700', label: 'Transfer' },
  adjustment: { icon: RefreshCw, color: 'bg-orange-100 text-orange-700', label: 'Adjustment' },
};

export function TransactionsPage() {
  const [activeTab, setActiveTab] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');

  const filteredTransactions = mockTransactions.filter((t) => {
    if (activeTab !== 'all' && t.type !== activeTab) return false;
    if (searchQuery && !t.itemName.toLowerCase().includes(searchQuery.toLowerCase())) return false;
    return true;
  });

  return (
    <div className="space-y-6">
      <PageHeader
        title="Inventory Transactions"
        subtitle="Track all inventory movements"
        actions={
          <>
            <Button variant="outline">
              <Download className="w-4 h-4 mr-2" />
              Export
            </Button>
            <Button>
              <Plus className="w-4 h-4 mr-2" />
              New Transaction
            </Button>
          </>
        }
      />

      {/* Quick Actions */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        {[
          { label: 'Receive Stock', icon: ArrowDownLeft, color: 'bg-green-50 text-green-600' },
          { label: 'Issue Items', icon: ArrowUpRight, color: 'bg-blue-50 text-blue-600' },
          { label: 'Transfer', icon: ArrowLeftRight, color: 'bg-purple-50 text-purple-600' },
          { label: 'Adjust', icon: RefreshCw, color: 'bg-orange-50 text-orange-600' },
        ].map((action) => (
          <Button
            key={action.label}
            variant="outline"
            className="justify-start h-auto py-3"
          >
            <div className={cn('w-10 h-10 rounded-lg flex items-center justify-center mr-3', action.color)}>
              <action.icon className="w-5 h-5" />
            </div>
            <span className="font-medium">{action.label}</span>
          </Button>
        ))}
      </div>

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
              placeholder="Search transactions..."
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

      {/* Transactions Table */}
      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <Table>
          <TableHeader>
            <TableRow className="bg-gray-50/50">
              <TableHead>Date</TableHead>
              <TableHead>Transaction ID</TableHead>
              <TableHead>Item</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Quantity</TableHead>
              <TableHead>Personnel</TableHead>
              <TableHead>Reference</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {filteredTransactions.map((transaction) => {
              const typeInfo = typeConfig[transaction.type];
              const Icon = typeInfo.icon;
              return (
                <TableRow key={transaction.id} className="hover:bg-gray-50/80">
                  <TableCell>
                    {format(new Date(transaction.createdAt), 'MMM dd, yyyy')}
                  </TableCell>
                  <TableCell>
                    <span className="font-mono text-sm text-gray-600">
                      #{transaction.id.padStart(6, '0')}
                    </span>
                  </TableCell>
                  <TableCell>
                    <p className="font-medium text-gray-900">{transaction.itemName}</p>
                  </TableCell>
                  <TableCell>
                    <Badge className={cn('text-xs', typeInfo.color)}>
                      <Icon className="w-3 h-3 mr-1" />
                      {typeInfo.label}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <span
                      className={cn(
                        'font-medium',
                        transaction.type === 'incoming' ? 'text-green-600' : 'text-blue-600'
                      )}
                    >
                      {transaction.type === 'incoming' ? '+' : '-'}
                      {transaction.quantity}
                    </span>
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      <img
                        src={transaction.personnel.avatar || `https://api.dicebear.com/7.x/avataaars/svg?seed=${transaction.personnel.id}`}
                        alt={transaction.personnel.name}
                        className="w-6 h-6 rounded-full"
                      />
                      <span className="text-sm">{transaction.personnel.name}</span>
                    </div>
                  </TableCell>
                  <TableCell>
                    <span className="font-mono text-sm text-gray-500">{transaction.reference}</span>
                  </TableCell>
                </TableRow>
              );
            })}
          </TableBody>
        </Table>
      </div>
    </div>
  );
}
