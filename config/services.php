<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'certificate_generator' => [
        'python' => env('CERTIFICATE_GENERATOR_PYTHON'),
        'python_path' => env('PYTHON_PATH', env('CERTIFICATE_GENERATOR_PYTHON', PHP_OS_FAMILY === 'Windows' ? 'py' : 'python')),
        'python_hash_seed' => env('CERTIFICATE_GENERATOR_PYTHON_HASH_SEED', '0'),
        'libreoffice_path' => env('LIBREOFFICE_PATH', 'soffice'),
    ],

];
