<?php

namespace App\Observers;

use App\Events\ReferenceDataChanged;
use App\Models\Supplier;

class SupplierObserver
{
    public function created(Supplier $supplier): void
    {
        event(new ReferenceDataChanged('suppliers', 'created', (string) $supplier->getKey()));
    }

    public function updated(Supplier $supplier): void
    {
        event(new ReferenceDataChanged('suppliers', 'updated', (string) $supplier->getKey()));
    }

    public function deleted(Supplier $supplier): void
    {
        event(new ReferenceDataChanged('suppliers', 'deleted', (string) $supplier->getKey()));
    }
}
