<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Master Organization Configuration
    |--------------------------------------------------------------------------
    |
    | The master organization is where Super Admins belong. This organization
    | has special protections and cannot be deleted or renamed.
    |
    */

    'master' => [
        'name' => env('MASTER_ORG_NAME', 'example'),
        'description' => env('MASTER_ORG_DESCRIPTION', 'Master organization for system administrators'),
        'email_domain' => env('MASTER_ORG_EMAIL_DOMAIN', 'example.com'),
    ],

];
