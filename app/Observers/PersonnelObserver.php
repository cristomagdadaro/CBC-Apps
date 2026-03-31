<?php

namespace App\Observers;

use App\Events\ReferenceDataChanged;
use App\Models\Personnel;

class PersonnelObserver
{
    public function created(Personnel $personnel): void
    {
        event(new ReferenceDataChanged('personnels', 'created', (string) $personnel->getKey()));
    }

    public function updated(Personnel $personnel): void
    {
        event(new ReferenceDataChanged('personnels', 'updated', (string) $personnel->getKey()));
    }

    public function deleted(Personnel $personnel): void
    {
        event(new ReferenceDataChanged('personnels', 'deleted', (string) $personnel->getKey()));
    }
}
