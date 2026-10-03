<?php

return [
    // Run this package's migration (the `role_bindings` table). Enable in exactly ONE app per shared database (erp-main).
    'load_migrations' => env('ROLE_REGISTRY_MIGRATIONS', false),

    // Eloquent user model that uses Spatie's HasRoles.
    'user_model' => \App\Models\User::class,
];
