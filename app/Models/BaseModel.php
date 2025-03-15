<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected array $searchable = [];

    public function getSearchable(): array
    {
        return $this->searchable;
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
        //return $date->format('F j, Y'); //January 1, 2023
    }


    protected function serializeTime(string $time): string
    {
        return \Carbon\Carbon::createFromFormat('H:i', $time)->format('h:i A');
    }

}
