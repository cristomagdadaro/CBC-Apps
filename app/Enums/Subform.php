<?php

namespace App\Enums;

enum Subform: string
{
    case PREREGISTRATION = 'preregistration';

    case PREREGISTRATION_BIOTECH = 'preregistration_biotech';

    case PREREGISTRATION_QUIZBEE = 'preregistration_quizbee';

    case REGISTRATION = 'registration';

    case FEEDBACK = 'feedback';

    case POSTTEST = 'posttest';

    case PRETEST = 'pretest';
}
