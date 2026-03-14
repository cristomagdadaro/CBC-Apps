import React, { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import {
  ArrowLeft,
  Edit,
  QrCode,
  Package,
  MapPin,
  TrendingUp,
  TrendingDown,
  Calendar,
  DollarSign,
  Box,
  AlertTriangle,
  CheckCircle,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { PageHeader } from '@/components/layout/PageHeader';
import { mockInventoryItems, mockTransactions } from '@/lib/mockData';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { format } from 'date-fns';

export function ItemDetailPage() {
  const { id } = useParams();
  const item = mockInventoryItems.find((i) => i.id === id);
  const transactions = mockTransactions.filter((t) => t.itemId === id);

  if (!item) {
    return (
      <div className="text-center py-12">
        <Package className="w-12 h-12 text-gray-400 mx-auto mb-4" />
        <h2 className="text-xl font-semibold text-gray-900">Item not found</h2>
        <p className="text-gray-500 mt-2">The item you're looking for doesn't exist.</p>
        <Button asChild className="mt-4">
          <Link to="/inventory/items">Back to Items</Link>
        </Button>
      </div>
    );
  }

  const stockStatus = item.quantity === 0 
    ? { label: 'Out of Stock', color: 'bg-red-100 text-red-700' }
    : item.quantity <= item.minStock
    ? { label: 'Low Stock', color: 'bg-yellow-100 text-yellow-700' }
    : { label: 'In Stock', color: 'bg-green-100 text-green-700' };

  return (
    <div className="space-y-6">
      <PageHeader
        title={item.name}
        subtitle={item.sku}
        backLink="/inventory/items"
        actions={
          <>
            <Button variant="outline">
              <QrCode className="w-4 h-4 mr-2" />
              Print Barcode
            </Button>
            <Button>
              <Edit className="w-4 h-4 mr-2" />
              Edit Item
            </Button>
          </>
        }
      />

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Main Info */}
        <div className="lg:col-span-2 space-y-6">
          <Card>
            <CardContent className="p-6">
              <div className="flex items-start gap-6">
                <div className="w-24 h-24 bg-primary-50 rounded-xl flex items-center justify-center flex-shrink-0">
                  <Package className="w-12 h-12 text-primary-600" />
                </div>
                <div className="flex-1">
                  <div className="flex items-start justify-between">
                    <div>
                      <h2 className="text-xl font-semibold text-gray-900">{item.name}</h2>
                      <p className="text-gray-500 mt-1">{item.description}</p>
                    </div>
                    <Badge className={stockStatus.color}>{stockStatus.label}</Badge>
                  </div>

                  <div className="grid grid-cols-3 gap-4 mt-6">
                    <div>
                      <p className="text-sm text-gray-500">Category</p>
                      <p className="font-medium">{item.category}</p>
                    </div>
                    <div>
                      <p className="text-sm text-gray-500">Unit</p>
                      <p className="font-medium capitalize">{item.unitOfMeasure}</p>
                    </div>
                    <div>
                      <p className="text-sm text-gray-500">Cost</p>
                      <p className="font-medium">₱{item.cost?.toLocaleString()}</p>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Tabs */}
          <Tabs defaultValue="transactions">
            <TabsList className="bg-gray-100 p-1">
              <TabsTrigger value="transactions" className="data-[state=active]:bg-white">
                Transactions
              </TabsTrigger>
              <TabsTrigger value="suppliers" className="data-[state=active]:bg-white">
                Suppliers
              </TabsTrigger>
              <TabsTrigger value="history" className="data-[state=active]:bg-white">
                History
              </TabsTrigger>
            </TabsList>

            <TabsContent value="transactions" className="mt-4">
              <Card>
                <CardContent className="p-0">
                  <Table>
                    <TableHeader>
                      <TableRow className="bg-gray-50/50">
                        <TableHead>Date</TableHead>
                        <TableHead>Type</TableHead>
                        <TableHead>Quantity</TableHead>
                        <TableHead>Reference</TableHead>
                        <TableHead>Personnel</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {transactions.map((transaction) => (
                        <TableRow key={transaction.id}>
                          <TableCell>
                            {format(new Date(transaction.createdAt), 'MMM dd, yyyy')}
                          </TableCell>
                          <TableCell>
                            <Badge
                              variant="secondary"
                              className={cn(
                                transaction.type === 'incoming' && 'bg-green-100 text-green-700',
                                transaction.type === 'outgoing' && 'bg-blue-100 text-blue-700'
                              )}
                            >
                              {transaction.type}
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
                            <span className="font-mono text-sm">{transaction.reference}</span>
                          </TableCell>
                          <TableCell>{transaction.personnel.name}</TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="suppliers">
              <Card>
                <CardContent className="p-6">
                  <div className="text-center py-8 text-gray-500">
                    <Box className="w-12 h-12 mx-auto mb-3 opacity-50" />
                    <p>No supplier information available</p>
                  </div>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="history">
              <Card>
                <CardContent className="p-6">
                  <div className="space-y-4">
                    <div className="flex items-start gap-3">
                      <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <Edit className="w-4 h-4 text-blue-600" />
                      </div>
                      <div>
                        <p className="text-sm font-medium">Item details updated</p>
                        <p className="text-xs text-gray-500">{format(new Date(item.updatedAt), 'MMM dd, yyyy h:mm a')}</p>
                      </div>
                    </div>
                    <div className="flex items-start gap-3">
                      <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <CheckCircle className="w-4 h-4 text-green-600" />
                      </div>
                      <div>
                        <p className="text-sm font-medium">Item created</p>
                        <p className="text-xs text-gray-500">{format(new Date(item.createdAt), 'MMM dd, yyyy h:mm a')}</p>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </TabsContent>
          </Tabs>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="text-base">Stock Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <p className="text-sm text-gray-500">Current Stock</p>
                <p className={cn(
                  'text-3xl font-bold',
                  item.quantity === 0 ? 'text-red-600' : item.quantity <= item.minStock ? 'text-yellow-600' : 'text-green-600'
                )}>
                  {item.quantity}
                </p>
              </div>
              <div>
                <p className="text-sm text-gray-500">Minimum Stock</p>
                <p className="text-xl font-semibold">{item.minStock}</p>
              </div>
              {item.quantity <= item.minStock && (
                <div className="flex items-center gap-2 p-3 bg-yellow-50 rounded-lg">
                  <AlertTriangle className="w-5 h-5 text-yellow-600" />
                  <p className="text-sm text-yellow-700">Stock level is below minimum</p>
                </div>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="text-base">Location</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex items-center gap-3">
                <MapPin className="w-5 h-5 text-gray-400" />
                <div>
                  <p className="font-medium">{item.location}</p>
                  <p className="text-sm text-gray-500">Shelf {item.shelf}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="text-base">Quick Actions</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
              <Button variant="outline" className="w-full justify-start">
                <TrendingUp className="w-4 h-4 mr-2" />
                Receive Stock
              </Button>
              <Button variant="outline" className="w-full justify-start">
                <TrendingDown className="w-4 h-4 mr-2" />
                Issue Items
              </Button>
              <Button variant="outline" className="w-full justify-start">
                <Calendar className="w-4 h-4 mr-2" />
                Schedule Count
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
