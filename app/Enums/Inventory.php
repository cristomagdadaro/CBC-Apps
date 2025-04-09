<?php

namespace App\Enums;

enum Inventory: string
{
    case INCOMING = 'incoming';

    case OUTGOING = 'outgoing';
}
