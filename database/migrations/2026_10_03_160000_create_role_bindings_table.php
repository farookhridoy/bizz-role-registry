<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Role bindings: a stable functional key (department_head, store_manager ...) -> one or more roles.
 * Code asks for the KEY, so a role created tomorrow only has to be bound to the key to take part everywhere
 * the key is used (notifications, approver lookups, visibility), without touching any code.
 *
 * Seeded from the roles that exist today (matched by name). Shared database: other apps ignore this table.
 */
return new class extends Migration
{
    /** key => current role names (mirror of App\Services\Roles\RoleRegistry::DEFAULTS at the time of writing) */
    private const SEED = [
        'super_admin' => ['Super Admin'],
        'employee' => ['Employee'],
        'department_head' => ['Department-Head'],
        'sbu_head' => ['SBU Head'],
        'management' => ['Management'],
        'purchase_department' => ['Purchase-Department'],
        'purchase_employee' => ['Purchase-Employee'],
        'store_manager' => ['Store-Manager'],
        'store_department' => ['Store-Department'],
        'accounts' => ['Accounts'],
        'billing' => ['Billing'],
        'audit' => ['Audit'],
        'gate_permission' => ['Gate Permission'],
        'quality_ensure' => ['Quality-Ensure'],
        'pmo' => ['PMO'],
        'pm' => ['PM'],
        'sponsor' => ['Sponsor'],
        'project_board' => ['Project-Board'],
        'primary_user' => ['Primary-User'],
    ];

    public function up(): void
    {
        Schema::create('role_bindings', function (Blueprint $table) {
            $table->id();
            $table->string('binding_key', 60)->index();
            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            $table->unique(['binding_key', 'role_id']);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        foreach (self::SEED as $key => $names) {
            foreach (DB::table('roles')->where('guard_name', 'web')->whereIn('name', $names)->pluck('id') as $roleId) {
                DB::table('role_bindings')->insert(['binding_key' => $key, 'role_id' => $roleId, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_bindings');
    }
};
