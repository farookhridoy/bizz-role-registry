# bizzsol/role-registry

Code asks for a **functional key** (`department_head`, `store_manager`, `purchase_department` ...), never for a role *name*.
`role_bindings` maps a key to one or more roles, so a role created later only has to be **bound** to take part everywhere the key is used.

- `RoleRegistry::DEFAULTS` is the list of keys and their original role names (used when a key has no bindings, so a fresh database behaves like the old code).
- `userHasRole('department_head')`, `userHasRole(['accounts','Management'])`, `roleRegistry()->users('purchase_department')` accept keys *or* legacy role names.
- Install: `composer config repositories.role-registry path ../bizz-role-registry && composer require bizzsol/role-registry:@dev`.
- The migration creates `role_bindings`; only the owning app (erp-main) enables `ROLE_REGISTRY_MIGRATIONS=true` / `config/role-registry.php`.
- Tests live in the PMS app (`tests/Feature/RoleRegistryTest.php`) because they need the ERP schema.
