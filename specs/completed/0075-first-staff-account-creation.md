# 0075 — First staff account creation without the Filament admin

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-09-01
- TODO item: First staff account creation in core — `lunar:create-admin` moves out of the Filament admin; panel install offers it (spec 0075)

## Problem

Creating the first staff account is owned by the wrong package. The
`lunar:create-admin` command lives in `lunarphp/admin`
(`Lunar\Admin\Console\Commands\MakeLunarAdminCommand`), even though the `Staff`
model, the staff guard, and the access-control manifest all live in core. Three
things break or degrade for an application that installs the recommended v2
stack — `lunarphp/core` + `lunarphp/panel` — without the Filament admin:

1. **`lunar:install` fails.** Core's installer calls
   `$this->call('lunar:create-admin')` whenever no admin staff exists
   (`packages/core/src/Console/InstallLunar.php:67`) and unconditionally calls
   `filament:assets` (`InstallLunar.php:189`). Neither command exists in a
   panel-only application, so the installer throws `CommandNotFoundException`
   partway through its seeding transaction.
2. **`lunar:panel:install` leaves you locked out.** The panel installer only
   publishes config and assets (`packages/panel/src/Console/Commands/InstallPanelCommand.php`).
   It finishes by printing the panel URL — a login screen no one can log in to,
   because no staff account exists and nothing offered to create one.
3. **The documented workaround is tinker.** The panel installation docs tell
   users to hand-write `Staff::create([...])` with a manual `Hash::make()` in
   tinker when the Filament admin is not installed. First-run experience for
   the flagship panel should not require pasting PHP.

The command itself also has admin-package couplings it does not need: it
hard-references the `Filament` facade for the login URL in its success message
and hardcodes `Lunar\Core\Models\Staff` instead of honouring the
`lunar.staff.model` config key that core's auth wiring already respects
(`packages/core/src/LunarServiceProvider.php:327`).

## Proposal

Move first-staff-account creation into core, where the model and guard live,
and make both installers portable across panel choices.

### 1. `lunar:create-admin` moves to core

New class `Lunar\Core\Console\Commands\CreateAdmin`, registered by
`LunarServiceProvider` alongside the existing core commands. The signature is
unchanged — `lunar:create-admin` with `--firstname`, `--lastname`, `--email`,
`--password` — so v1 muscle memory, deploy scripts, and existing docs keep
working. Behavioural differences from the admin-package original:

- Resolves the staff model through `config('lunar.staff.model', Staff::class)`
  instead of hardcoding `Staff`, matching core's guard registration.
- The success message drops `Filament::getLoginUrl()`. The command reports the
  account was created; each panel's installer already prints its own URL, and
  the command must not know which panels are installed.
- Prompt flow, email validation (valid + unique), password hashing, and
  `admin => true` are carried over. The email rules also run against an
  `--email` option value, so scripted installs fail cleanly instead of
  surfacing a database unique-constraint exception.

`lunarphp/admin` deletes `MakeLunarAdminCommand` and its registration in
`LunarPanelProvider`. The command name still resolves in an admin-package
application — it now comes from core, which admin already depends on.

### 2. `lunar:panel:install` offers to create the first account

After publishing config and assets, when no admin staff account exists
(`Staff::whereAdmin(true)->exists()` against the configured model), the panel
installer offers to create one:

- Interactive: `confirm('No admin staff account exists. Create one now?')`,
  defaulting to yes, then `$this->call('lunar:create-admin')`.
- Non-interactive (`--no-interaction`, CI deploys): skip the offer silently.
  The command is re-run on every deploy, so it must never block or prompt in
  a pipeline. Scripted first-installs create the account explicitly with
  `lunar:create-admin --email=... --password=...`.

The closing message keeps printing the panel URL, so the run ends with an
account and the place to use it.

### 3. `lunar:install` stops assuming Filament

- The staff-creation step works everywhere once the command lives in core; the
  `class_exists(Staff::class)` guard becomes redundant and is removed.
- The `filament:assets` call runs only when the command is registered:
  `$this->getApplication()->has('filament:assets')`. Same guard style for any
  future panel-specific post-install steps.
- When `lunar:panel:install` is registered, the installer offers to run it
  after seeding, mirroring the Filament asset publishing it already does for
  the other panel.

### Tests

- Core suite: `lunar:create-admin` — interactive prompt flow, option-driven
  non-interactive use, duplicate-email rejection, `admin => true`, and that a
  swapped `lunar.staff.model` is honoured.
- Panel suite: `lunar:panel:install` — offers creation when no admin staff
  exists, skips when one does, skips under `--no-interaction`.
- Core suite: `lunar:install` completes in an application without Filament
  (no `filament:assets`, staff step still runs).

## Alternatives considered

- **A separate `lunar:panel:create-admin` in the panel package.** Duplicates
  the command per panel and leaves `lunar:install` broken for panel-only apps.
  The model and guard are core's; the command belongs beside them.
- **Renaming to `lunar:create-staff`.** More accurate (it creates a staff
  member), but breaks v1 docs, tutorials, and muscle memory for no functional
  gain. The `admin => true` default is the point of the command — it bootstraps
  the account that can create the rest.
- **Keeping a login-URL hint in the success message.** Would require core to
  sniff for installed panels (`class_exists` on panel/Filament classes) or a
  new "admin URL" contract panels bind. Both are more coupling than the message
  is worth; the installers already print their panel's URL.
- **Do nothing.** The docs keep shipping a tinker snippet as the first-run
  path for the recommended panel.

## Migration impact

- Database migrations: none.
- Breaking changes: `Lunar\Admin\Console\Commands\MakeLunarAdminCommand` is
  deleted. The Artisan command name and options are unchanged, so only code
  referencing the class directly (rare — subclassing a console command) is
  affected. Add a class-rename Rector rule to the upgrade package pointing at
  `Lunar\Core\Console\Commands\CreateAdmin`.
- v1.x upgrade path: none beyond the Rector rule — v1 usage is via the
  unchanged command name.
- Translation / locale impact: none; console strings are not translated.
- Filament / admin impact: admin-package behaviour is unchanged from the
  outside. The success message no longer prints the Filament login URL.
- Docs: the panel installation page replaces the tinker snippet with
  `lunar:create-admin`; the core installation page drops the "assumes a
  Filament-based panel" warning.

## Open questions

- Should `lunar:panel:install` also offer to run outstanding migrations the
  way `lunar:install` does? It is documented as safe to re-run on every
  deploy, and a migration prompt changes that contract. Current lean: no —
  keep it idempotent publish-plus-offer, and leave migrations to
  `lunar:install` / `php artisan migrate`.

## References

- `packages/core/src/Console/InstallLunar.php` — the Filament-coupled installer.
- `packages/admin/src/Console/Commands/MakeLunarAdminCommand.php` — the command being moved.
- `packages/panel/src/Console/Commands/InstallPanelCommand.php` — the installer gaining the offer.
- [[0010-filament-self-hosting-parity]] — moved `Staff` to core; this finishes the job for the command that creates one.
- lunarphp/docs `2.x/admin/installation.mdx` — the tinker workaround this removes.

## Implementation plan

- [x] Slice 1 — Move `lunar:create-admin` to core (new class, registration, admin-package removal, Rector rule, core tests).
- [x] Slice 2 — `lunar:panel:install` staff-creation offer + panel tests.
- [x] Slice 3 — `lunar:install` portability guards (`filament:assets`, panel-install offer) + test; docs follow-up PR in lunarphp/docs.
