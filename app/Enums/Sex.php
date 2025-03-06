<?php

namespace App\Enums;

enum Sex: string
{
    case Male = 'Male';
    case Female = 'Female';
    case PreferNotToSay = 'Prefer not to say';
}
