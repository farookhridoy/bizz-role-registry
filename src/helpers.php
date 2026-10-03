<?php

use Bizzsol\RoleRegistry\RoleRegistry;

if (! function_exists('roleRegistry')) {
    function roleRegistry(): RoleRegistry
    {
        return app(RoleRegistry::class);
    }
}

if (! function_exists('userHasRole')) {
    /**
     * Does the user (default: the logged-in one) hold any role bound to any of these keys / legacy role names?
     * STRICT: no Super Admin override (use it where the role itself drives behaviour). False for guests.
     * Apps keep their own friendlier wrappers (PMS: userIs(); erp-main: userIs() with the Super Admin override).
     *
     * @param  string|string[]  $keysOrNames
     */
    function userHasRole(string|array $keysOrNames, ?object $user = null): bool
    {
        return roleRegistry()->has($user ?? auth()->user(), $keysOrNames);
    }
}
