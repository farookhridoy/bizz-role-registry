<?php

namespace Bizzsol\RoleRegistry;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Functional role keys. Code should ask "is this user a department head?" (`department_head`), not "does this
 * user have the role named Department-Head?", so a role created later only needs to be BOUND to the key
 * (table `role_bindings`) to take part everywhere the key is used.
 *
 * Every API accepts either a key (`department_head`) or a legacy role name (`Department-Head`) so existing
 * call sites can be migrated one at a time. An unknown name is treated as itself (a plain role). A key with no
 * rows in `role_bindings` falls back to DEFAULTS, so a fresh database behaves exactly like the old code.
 */
class RoleRegistry
{
    /** key => default role names (what the code used to hard-code) */
    public const DEFAULTS = [
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

    /** key => [label, what it controls] - shown on the bindings admin screen */
    public const DESCRIPTIONS = [
        'super_admin' => ['Super Admin', 'Full access (the permission gate lets this role through everything). Protected: bind it only from the command line.'],
        'employee' => ['Employee', 'Ordinary requesters; sees only their own requisitions and records.'],
        'department_head' => ['Department head', 'First approver of a department: gets requisition approval notifications, sees the department\'s requisitions, can acknowledge or forward them.'],
        'sbu_head' => ['SBU head', 'Second approval level above department heads; notified when a department head sends a requisition up.'],
        'management' => ['Management', 'Final approvers: requisitions forwarded by department heads, CS and billing approvals.'],
        'purchase_department' => ['Purchase department', 'Procurement owners: receive purchase notifications, create RFP/CS/PO, see all purchasing lists.'],
        'purchase_employee' => ['Purchase employee', 'Procurement staff working on assigned requisitions (their CS is sent to the purchase department first).'],
        'store_manager' => ['Store manager', 'Store/inventory owners: receive GRN, QC and delivery notifications; see store requisitions.'],
        'store_department' => ['Store department', 'Store staff.'],
        'accounts' => ['Accounts', 'Finance: payments, advances, ledgers, finance approvals and notifications.'],
        'billing' => ['Billing', 'Billing/audit flow: invoices and PO billing.'],
        'audit' => ['Audit', 'Audit step of the billing flow.'],
        'gate_permission' => ['Gate permission', 'Gate pass / goods-received entry at the gate.'],
        'quality_ensure' => ['Quality ensure', 'Quality control approvals and returns.'],
        'pmo' => ['PMO', 'Project management office (projects module).'],
        'pm' => ['Project manager', 'Project managers (projects module).'],
        'sponsor' => ['Sponsor', 'Project sponsors (projects module).'],
        'project_board' => ['Project board', 'Project board members (projects module).'],
        'primary_user' => ['Primary user', 'Primary users of a project.'],
    ];

    /** @var array<string,string[]> per-process cache of key => bound role names */
    private static array $bound = [];

    private static ?bool $tableExists = null;

    public static function flush(): void
    {
        self::$bound = [];
        self::$tableExists = null;
    }

    /** Legacy role name -> key (null when the name is not a known functional role). */
    public function keyFor(string $name): ?string
    {
        foreach (self::DEFAULTS as $key => $names) {
            if (in_array($name, $names, true)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Role names that currently satisfy a key or a legacy name.
     *
     * @return string[]
     */
    public function names(string $keyOrName): array
    {
        $key = isset(self::DEFAULTS[$keyOrName]) ? $keyOrName : $this->keyFor($keyOrName);
        if ($key === null) {
            return [$keyOrName]; // not a functional role: behaves as a plain role name
        }

        if (! array_key_exists($key, self::$bound)) {
            self::$bound[$key] = $this->tableReady()
                ? DB::table('role_bindings as b')->join('roles as r', 'r.id', '=', 'b.role_id')
                    ->where('b.binding_key', $key)->where('r.guard_name', 'web')->orderBy('r.id')->pluck('r.name')->all()
                : [];
        }

        return self::$bound[$key] ?: self::DEFAULTS[$key];
    }

    /**
     * @param  string|string[]  $keysOrNames
     * @return string[] every role name satisfying any of them
     */
    public function namesFor(string|array $keysOrNames): array
    {
        return array_values(array_unique(array_merge(...array_map(fn ($k) => $this->names($k), (array) $keysOrNames))));
    }

    /** Does the user hold any role bound to any of these keys / names? */
    public function has(?object $user, string|array $keysOrNames): bool
    {
        return $user !== null && $user->hasAnyRole($this->namesFor($keysOrNames));
    }

    /** Users holding any role bound to the key(s). */
    public function users(string|array $keysOrNames): Builder
    {
        return ($this->userModel())::role($this->namesFor($keysOrNames));
    }

    public function bind(string $key, int $roleId): void
    {
        DB::table('role_bindings')->updateOrInsert(['binding_key' => $key, 'role_id' => $roleId], ['created_at' => now(), 'updated_at' => now()]);
        self::flush();
    }

    public function unbind(string $key, int $roleId): void
    {
        DB::table('role_bindings')->where(['binding_key' => $key, 'role_id' => $roleId])->delete();
        self::flush();
    }

    /** @return class-string<\Illuminate\Database\Eloquent\Model> */
    private function userModel(): string
    {
        return config('role-registry.user_model', \App\Models\User::class);
    }

    private function tableReady(): bool
    {
        return self::$tableExists ??= Schema::hasTable('role_bindings');
    }
}
