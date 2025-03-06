<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('F j, Y');
    }

    protected function serializeTime(string $time): string
    {
        return \Carbon\Carbon::createFromFormat('H:i:s', $time)->format('h:i A');
    }

}
