<?php

namespace App\Observers;

use App\Events\ReferenceDataChanged;
use App\Models\Item;

class ItemObserver
{
    public function created(Item $item): void
    {
        event(new ReferenceDataChanged('items', 'created', (string) $item->getKey()));
    }

    public function updated(Item $item): void
    {
        event(new ReferenceDataChanged('items', 'updated', (string) $item->getKey()));
    }

    public function deleted(Item $item): void
    {
        event(new ReferenceDataChanged('items', 'deleted', (string) $item->getKey()));
    }
}
