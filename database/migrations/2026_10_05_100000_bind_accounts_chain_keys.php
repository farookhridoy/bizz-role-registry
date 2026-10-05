<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Binds the existing Accounts-Requester / -Assessment / -Reviewer roles to their new keys (idempotent). */
return new class extends Migration
{
    private const SEED = [
        'accounts_requester' => ['Accounts-Requester'],
        'accounts_assessment' => ['Accounts-Assessment'],
        'accounts_reviewer' => ['Accounts-Reviewer'],
    ];

    public function up(): void
    {
        foreach (self::SEED as $key => $names) {
            foreach (DB::table('roles')->where('guard_name', 'web')->whereIn('name', $names)->pluck('id') as $roleId) {
                DB::table('role_bindings')->updateOrInsert(['binding_key' => $key, 'role_id' => $roleId], ['created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        DB::table('role_bindings')->whereIn('binding_key', array_keys(self::SEED))->delete();
    }
};
