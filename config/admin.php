<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seeded administrator account
    |--------------------------------------------------------------------------
    |
    | Used by DatabaseSeeder to create the CMS sign-in. Set these in the
    | environment (.env locally, cloud secrets in production) so the real
    | credentials never live in the repository. Outside the local
    | environment the seeder refuses to run without ADMIN_PASSWORD; locally
    | it falls back to "password" for convenience.
    |
    */

    'email' => env('ADMIN_EMAIL', 'admin@cms.local'),

    'name' => env('ADMIN_NAME', 'CMS Admin'),

    'password' => env('ADMIN_PASSWORD'),

];
