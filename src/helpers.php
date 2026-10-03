<?php

use Bizzsol\RoleRegistry\RoleRegistry;

if (! function_exists('roleRegistry')) {
    function roleRegistry(): RoleRegistry
    {
        return app(RoleRegistry::class);
    }
}

if (! function_exists('userIs')) {
    /**
     * Does the user (default: the logged-in one) hold any role bound to any of these keys / legacy role names?
     * Replaces `auth()->user()->hasRole('X')` / `->hasAnyRole([...])`. False for guests.
     *
     * @param  string|string[]  $keysOrNames
     */
    function userIs(string|array $keysOrNames, ?object $user = null): bool
    {
        return roleRegistry()->has($user ?? auth()->user(), $keysOrNames);
    }
}
