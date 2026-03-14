import React, { useState } from 'react';
import { motion } from 'framer-motion';
import {
  Search,
  Printer,
  Download,
  QrCode,
  Barcode,
  Check,
  Package,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { PageHeader } from '@/components/layout/PageHeader';
import { mockInventoryItems } from '@/lib/mockData';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';

export function BarcodesPage() {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedItems, setSelectedItems] = useState<string[]>([]);
  const [labelSize, setLabelSize] = useState('50x25');
  const [format, setFormat] = useState('code128');

  const filteredItems = mockInventoryItems.filter((item) =>
    item.name.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const toggleItem = (itemId: string) => {
    setSelectedItems((prev) =>
      prev.includes(itemId) ? prev.filter((id) => id !== itemId) : [...prev, itemId]
    );
  };

  const selectAll = () => {
    if (selectedItems.length === filteredItems.length) {
      setSelectedItems([]);
    } else {
      setSelectedItems(filteredItems.map((item) => item.id));
    }
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Barcode Generator"
        subtitle="Generate and print barcodes for inventory items"
      />

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Item Selection */}
        <div className="lg:col-span-2 space-y-4">
          <Card>
            <CardContent className="p-4">
              <div className="flex items-center justify-between mb-4">
                <div className="relative flex-1 max-w-sm">
                  <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <Input
                    type="text"
                    placeholder="Search items..."
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="pl-9"
                  />
                </div>
                <div className="flex items-center gap-2 ml-4">
                  <Checkbox
                    id="select-all"
                    checked={selectedItems.length === filteredItems.length && filteredItems.length > 0}
                    onCheckedChange={selectAll}
                  />
                  <Label htmlFor="select-all" className="text-sm cursor-pointer">
                    Select All ({selectedItems.length})
                  </Label>
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[400px] overflow-y-auto">
                {filteredItems.map((item) => (
                  <div
                    key={item.id}
                    onClick={() => toggleItem(item.id)}
                    className={cn(
                      'flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all',
                      selectedItems.includes(item.id)
                        ? 'border-primary-300 bg-primary-50'
                        : 'border-gray-200 hover:border-gray-300'
                    )}
                  >
                    <Checkbox checked={selectedItems.includes(item.id)} />
                    <div className="w-10 h-10 bg-primary-50 rounded-lg flex items-center justify-center">
                      <Package className="w-5 h-5 text-primary-600" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="font-medium text-sm text-gray-900 truncate">{item.name}</p>
                      <p className="text-xs text-gray-500">{item.sku}</p>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Preview */}
          {selectedItems.length > 0 && (
            <Card>
              <CardContent className="p-6">
                <h3 className="font-semibold text-gray-900 mb-4">Preview</h3>
                <div className="flex flex-wrap gap-4">
                  {selectedItems.slice(0, 6).map((itemId) => {
                    const item = mockInventoryItems.find((i) => i.id === itemId);
                    if (!item) return null;
                    return (
                      <div
                        key={item.id}
                        className="w-48 p-4 bg-white border border-gray-200 rounded-lg"
                      >
                        <div className="text-center">
                          <div className="h-16 bg-gray-100 rounded flex items-center justify-center mb-2">
                            <Barcode className="w-32 h-10 text-gray-800" />
                          </div>
                          <p className="text-xs font-mono text-gray-600">{item.barcode || item.sku}</p>
                          <p className="text-xs text-gray-500 mt-1 truncate">{item.name}</p>
                        </div>
                      </div>
                    );
                  })}
                  {selectedItems.length > 6 && (
                    <div className="w-48 p-4 bg-gray-50 border border-dashed border-gray-200 rounded-lg flex items-center justify-center">
                      <p className="text-sm text-gray-500">+{selectedItems.length - 6} more</p>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>
          )}
        </div>

        {/* Settings */}
        <div className="space-y-4">
          <Card>
            <CardContent className="p-4 space-y-4">
              <h3 className="font-semibold text-gray-900">Print Settings</h3>

              <div>
                <Label>Label Size</Label>
                <Select value={labelSize} onValueChange={setLabelSize}>
                  <SelectTrigger className="mt-1.5">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="50x25">50mm x 25mm</SelectItem>
                    <SelectItem value="50x30">50mm x 30mm</SelectItem>
                    <SelectItem value="40x20">40mm x 20mm</SelectItem>
                    <SelectItem value="a4">A4 Sheet</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <Label>Barcode Format</Label>
                <Select value={format} onValueChange={setFormat}>
                  <SelectTrigger className="mt-1.5">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="code128">Code 128</SelectItem>
                    <SelectItem value="code39">Code 39</SelectItem>
                    <SelectItem value="ean13">EAN-13</SelectItem>
                    <SelectItem value="qr">QR Code</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="pt-4 space-y-2">
                <Button className="w-full" disabled={selectedItems.length === 0}>
                  <Printer className="w-4 h-4 mr-2" />
                  Print Barcodes
                </Button>
                <Button variant="outline" className="w-full" disabled={selectedItems.length === 0}>
                  <Download className="w-4 h-4 mr-2" />
                  Download PDF
                </Button>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-4">
              <h3 className="font-semibold text-gray-900 mb-3">Summary</h3>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-500">Items Selected</span>
                  <span className="font-medium">{selectedItems.length}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-500">Labels per Sheet</span>
                  <span className="font-medium">24</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-500">Sheets Required</span>
                  <span className="font-medium">{Math.ceil(selectedItems.length / 24)}</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
