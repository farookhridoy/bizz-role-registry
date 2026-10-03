<?php

namespace Bizzsol\RoleRegistry\Console;


use Bizzsol\RoleRegistry\RoleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Manage which roles satisfy a functional key until the admin screen exists.
 *
 *   php artisan roles:bindings                       list every key and its roles
 *   php artisan roles:bindings department_head "Section Lead"          bind a role
 *   php artisan roles:bindings department_head "Section Lead" --remove unbind it
 *
 * Writes the shared `role_bindings` table - back the database up first.
 */
class RoleBindings extends Command
{
    protected $signature = 'roles:bindings {key? : functional key, e.g. department_head} {role? : role name} {--remove : unbind instead of bind}';

    protected $description = 'List, bind or unbind roles of a functional role key (department_head, store_manager ...)';

    public function handle(RoleRegistry $registry): int
    {
        $key = $this->argument('key');
        $roleName = $this->argument('role');

        if (! $key) {
            $rows = [];
            foreach (array_keys(RoleRegistry::DEFAULTS) as $k) {
                $rows[] = [$k, implode(', ', $registry->names($k))];
            }
            $this->table(['Key', 'Roles that satisfy it'], $rows);

            return self::SUCCESS;
        }

        if (! isset(RoleRegistry::DEFAULTS[$key])) {
            $this->error("Unknown key [{$key}]. Known keys: ".implode(', ', array_keys(RoleRegistry::DEFAULTS)));

            return self::FAILURE;
        }
        if (! $roleName) {
            $this->line("{$key}: ".implode(', ', $registry->names($key)));

            return self::SUCCESS;
        }

        $roleId = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->value('id');
        if (! $roleId) {
            $this->error("No role named [{$roleName}].");

            return self::FAILURE;
        }

        $this->option('remove') ? $registry->unbind($key, $roleId) : $registry->bind($key, $roleId);
        $this->info(($this->option('remove') ? 'Unbound' : 'Bound')." [{$roleName}] ".($this->option('remove') ? 'from' : 'to')." {$key}. Now: ".implode(', ', $registry->names($key)));

        return self::SUCCESS;
    }
}
