# Changelog - bizzsol/role-registry

Consumers: erp-main, erp-pms, erp-finance, erp-pmd, erp-hrms, erp-production (`"bizzsol/role-registry": "^0.3"`). Guidebook: `erp-main-v11/docs/handover/` (pages 03, 04, 06).

## v0.3.1 - 2026-10-05
- New keys `accounts_requester`, `accounts_assessment`, `accounts_reviewer` (finance accounts chain) with descriptions.
- Migration `2026_10_05_100000_bind_accounts_chain_keys`: binds the existing `Accounts-Requester` / `-Assessment` / `-Reviewer` roles (idempotent). Runs only in the app that enables package migrations (erp-main).
- Total: 22 keys.

## v0.3.0 - 2026-10-05
- `roleRegistry()->recipient($keysOrNames, ['company_id'=>?, 'unit_id'=>?, 'department_id'=>?]): ?int` - ONE holder by company > unit > department (priority rows for unit+department, then unit, then `user_companies` company), lowest user id breaks ties, `null` when nobody matches.

## v0.2.0 and earlier
- `userIs()`/`userHasRole()`, `userHasRoleOrSuper()` (Super Admin passes except limiting-only lists: `employee`, `purchase_employee`, `primary_user`), `role_bindings` table and migration, commands `roles:bindings` and `roles:inventory`, key descriptions for the bindings admin screen.

## Known
- `ROLES_SUPER_ADMIN_DOMINATES` is read with `env()` in `src/helpers.php`; it does not work with a cached config (default `true` applies). Planned fix: `config('role-registry.super_admin_dominates')` in a patch release.
- The package `main` branch contains everything above but is not pushed to GitHub yet (tags are).
