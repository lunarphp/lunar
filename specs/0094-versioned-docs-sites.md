# 0094 — Versioned docs sites published from releases

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-10-28
- TODO item: Versioned docs sites — each major version's docs live in the monorepo on its version branch and publish when that version releases (spec 0094)

## Problem

The documentation is a separate repository, `lunarphp/docs`, built by Mintlify as one site with all versions side by side (`0.x/`, `1.x/`, `2.x/`, plus `support/` and `logs/`). It was split out because Mintlify could not build from a subdirectory or from more than one repository. That arrangement now causes three problems.

**Docs and code change in different places.** A change to v2 behaviour needs a code PR in `lunarphp/lunar` and a separate docs PR in `lunarphp/docs`, with merge order explained across repositories and the same facts written twice. Docs drift as a result: the 2.x pricing reference described the pricing manager reading the auth guard after spec 0091 removed that, and the cart and order extension pages said modifiers never run long after the line actions they relied on had stopped being called.

**A release of one version publishes every version.** Docs PRs for all versions merge into `next`, and `next` is merged into `main` (the branch Mintlify deploys) at release time. A 1.x release therefore publishes every unreleased 2.x docs change waiting on `next`, and the reverse.

**One deploy branch cannot follow version branches.** The monorepo has no trunk: code lives on version branches (`1.x`, `2.x`; `0.1`–`0.8` for v0). A single Mintlify site deploys one directory from one branch, so it cannot take each version's docs from its own branch.

Mintlify has since changed in two ways that make a different arrangement possible: a project can build from a subdirectory ("docs.json is in a subdirectory" in Git Settings), and new projects are created from the dashboard. Mintlify still deploys only branches, not tags or releases, and combining repositories into one site needs an Enterprise plan.

## Proposal

Each supported major version gets its own Mintlify project, built from a `docs/` directory on that version's branch in the monorepo, and published when that version releases. Docs change in the same PR as the code they describe.

### Layout

Each version branch carries a top-level `docs/` directory with its own `docs.json`:

```
docs/
  docs.json
  getting-started/
  guides/
  reference/
  ...
  README.md        contributor guide (see Documentation)
```

The contents are the current `lunarphp/docs` version directory with the version prefix dropped: `2.x/reference/carts.mdx` becomes `docs/reference/carts.mdx` on the `2.x` branch, and `docs.json` keeps only that version's navigation. `docs/` is not a sub-package, so `split_packages.yml` ignores it, and `tests.yml` adds `docs/**` to its `paths-ignore` so a docs-only PR does not run the PHP matrix.

### Live branches

Mintlify watches one branch per project. Each version has a branch that holds what is published:

| Version branch | Live branch | Mintlify project |
| --- | --- | --- |
| `2.x` | `docs-live-2.x` | v2 docs |
| `1.x` | `docs-live-1.x` | v1 docs |

A live branch is never developed on. No PR targets it and nobody commits to it; two workflows move it, and branch protection allows only them. It holds a snapshot of the version branch at the last release, plus any docs hotfixes since. Mintlify reads only its `docs/` directory.

### Publishing a release

`.github/workflows/publish-docs.yml` runs on `release: published`. It reads the major version from the tag (`1.5.0` → `1`, `2.0.0-alpha.6` → `2`) and resets `docs-live-<major>.x` to the release commit. Pre-releases publish too, since the v2 docs describe the alpha.

The branch is reset, not merged, so the published docs are exactly the docs at that release and no merge can conflict. Before resetting, the workflow checks with `git cherry` that every change on the live branch is already contained in the release. If one is not — a hotfix applied by hand that never reached the version branch — it stops and reports the commit instead of silently unpublishing it.

### Hotfixes

A fix to the published docs that should not wait for the next release is an ordinary PR against the version branch, labelled `docs-hotfix`.

- `.github/workflows/docs-hotfix-label.yml` fails a PR carrying the label if it changes anything outside `docs/`, so the label cannot carry code onto a live branch.
- `.github/workflows/publish-docs-hotfix.yml` runs when a labelled PR merges and cherry-picks its commit onto the version's live branch (`-m 1` for a merge commit). Mintlify deploys the push. Only that change is published; other docs merged since the release stay unpublished.
- The fix is on the version branch first, so the next release contains it and the reset cannot lose it.
- If the cherry-pick conflicts, because unreleased docs changed the same lines, the workflow fails and comments on the PR. A maintainer then opens a PR against the live branch by hand. This is the only case in which anyone changes a live branch directly, and the release workflow's `git cherry` check catches it if the fix never reaches the version branch.

| Change | Where | Published |
| --- | --- | --- |
| Docs for new or changed code | The code PR, on the version branch | At the next release of that version |
| Docs hotfix | PR to the version branch, labelled `docs-hotfix` | On merge |
| Release | `docs-live-<major>.x` reset to the release commit | On publishing the GitHub release |

Mintlify's web editor and its agent commit to the deploy branch, where a change would be lost at the next reset. Both are turned off for these projects.

### URLs

| URL | Serves |
| --- | --- |
| `docs-v2.lunarphp.com` | v2 docs, Mintlify project |
| `docs-v1.lunarphp.com` | v1 docs, Mintlify project |
| `docs-v0.lunarphp.com` | v0 docs, archived Mintlify project |
| `docs.lunarphp.com` | Redirects only |

Each version site is a Mintlify custom domain on a single-level subdomain, so Cloudflare's universal certificate covers it whether or not the record is proxied.

`docs.lunarphp.com` stops being a Mintlify site. It becomes a proxied Cloudflare record served by Redirect Rules:

| Request | Redirect |
| --- | --- |
| `docs.lunarphp.com/2.x/<path>` | `docs-v2.lunarphp.com/<path>` (301) |
| `docs.lunarphp.com/1.x/<path>` | `docs-v1.lunarphp.com/<path>` (301) |
| `docs.lunarphp.com/0.x/<path>` | `docs-v0.lunarphp.com/<path>` (301) |
| `docs.lunarphp.com/` and any other path | current stable version, path kept (302) |

Versioned paths redirect permanently: they keep every existing link (search results, issues, the 1.x Boost skill) working. The root and unversioned paths redirect temporarily, because "current stable" changes: a cached permanent redirect would keep sending visitors to v1 after v2 is released. Changing the stable version is one Redirect Rule.

The three redirects in the current `docs.json` move to the version site whose paths they describe.

### Shared content

`support/` (community and Lunar Alliance pages) and `logs/` (the Flight Plan and release announcements) describe no version and move to the main website. Each version site links to them from its navigation.

### v0

v0 has no single version branch (`0.1`–`0.8`) and its docs are frozen. `lunarphp/docs` becomes the v0 archive: `1.x/`, `2.x/`, `support/` and `logs/` are removed once their new homes are live, the Mintlify project moves to `docs-v0.lunarphp.com`, and its README says the repository is archived and where current docs live.

### Version switching

Mintlify's version switcher works within one site. Each site's navigation instead links to the other versions' sites, and each site's search covers that version only.

## Alternatives considered

- **One site, a staging branch per version.** Docs PRs target `release/1.x` or `release/2.x`, each merged into `main` at that version's release, with a CI check against wrong targets. This fixes publishing one version with another and keeps the version switcher and cross-version search, but docs still change in a different repository from the code.
- **One site with `docs/` in the monorepo.** A single site needs every version in one tree on one branch, and the monorepo has no trunk. Putting all versions on `2.x` would put v1's docs on the v2 code branch.
- **Mintlify multi-repository projects.** Enterprise only, and a repository can be added once per project, so it cannot take `docs/` from both `1.x` and `2.x`.
- **Several projects under one domain at different paths** (`docs.lunarphp.com/v2`). Mintlify documents a single project at a subpath; custom subpaths are an Enterprise feature, and several projects sharing one domain is not documented.
- **Publish from tags.** Mintlify deploys branches only, and its deployment API redeploys the configured branch without a ref. The live branch is the smallest thing that gives a release a branch and a hotfix somewhere to go.
- **Publish whenever the version branch changes.** Docs would describe merged but unreleased code — immediately for v2, where every spec PR merges into `2.x` long before a release.
- **`v2.docs.lunarphp.com`.** A second-level subdomain is not covered by Cloudflare's universal certificate, which ties the record to DNS-only or a paid certificate. `docs-v2` also sorts alongside `docs` in DNS and history.
- **Do nothing.** Every change keeps needing two PRs in two repositories, and every release of one version publishes the other's pending docs.

## Migration impact

- **Database migrations required:** none.
- **Breaking changes to the public contract surface:** none to code. Docs URLs change, and every existing `docs.lunarphp.com` versioned URL redirects to its new location.
- **Upgrade path for v1.x consumers:** none.
- **Translation / locale impact:** none.
- **Filament / admin impact:** the v2 panel links to `docs.lunarphp.com/` — `config('lunar.panel.support_url')`, which the navigation reads, and links hard-coded in 13 panel pages. Under this spec that root sends v2 panel users to the stable version, which is v1 until v2 is released. The links point at `docs-v2.lunarphp.com` and read the config value instead of hard-coding it. The 2.x `README.md` documentation link changes to match.
- **Contributors:** v2 (and later v1) docs contributions move from `lunarphp/docs` to the monorepo.
- **Manual setup outside the repository:** creating the Mintlify projects (custom domain, deploy branch, subdirectory `docs`, editor and agent off), the Cloudflare records and Redirect Rules, branch protection on the live branches, and the `docs-hotfix` label.

## Open questions

- **Mintlify billing per project.** Creating projects is self-serve, but whether each project needs its own plan, and how many free projects an organization can have, is unconfirmed. Owner: Glenn, check the dashboard before slice 1.
- **When v1 moves.** v1 can follow as soon as v2 is proven, or stay in `lunarphp/docs` until v2 is stable. Proposed: move it once the v2 pilot has published at least one release and one hotfix. Owner: Glenn.
- **Where the shared content goes on the main website.** URLs for the community, Alliance and Flight Plan pages, and redirects from their current `docs.lunarphp.com` paths. Owner: Glenn.

## References

- `lunarphp/docs` — the current docs repository; its `docs.json` holds the navigation and the three existing redirects.
- `.github/workflows/tests.yml` (`paths-ignore`), `.github/workflows/split_packages.yml`.
- `packages/panel/config/panel.php` (`support_url`), panel pages under `packages/panel/resources/js/pages/`.
- Mintlify: [monorepo setup](https://www.mintlify.com/docs/deploy/monorepo), [multi-repository projects](https://www.mintlify.com/docs/deploy/multi-repo), [subpath hosting](https://www.mintlify.com/docs/deploy/docs-subpath), [deployment API](https://www.mintlify.com/docs/api/update/trigger).

## Implementation plan

Ordered so the v2 site is live and proven before anything in `lunarphp/docs` is removed.

- [ ] Slice 1 — v2 content. Move `2.x/` from `lunarphp/docs` into `docs/` on `2.x` with its own `docs.json`, links updated for the dropped prefix, and `docs/**` added to `tests.yml`'s `paths-ignore`. Checked locally with `mint dev`.
- [ ] Slice 2 — Publishing. `publish-docs.yml` (release reset with the `git cherry` check), `publish-docs-hotfix.yml`, `docs-hotfix-label.yml`, a header comment in each, and the `docs-hotfix` label. Manual: the v2 Mintlify project on `docs-v2.lunarphp.com`, deploying `docs` from `docs-live-2.x`, editor and agent off; branch protection on `docs-live-2.x`. Proven by publishing the next 2.x pre-release.
- [ ] Slice 3 — Documentation. `docs/README.md` (writing docs alongside code, when they publish, the hotfix label and its conflict path, never committing to a live branch, previewing with `mint dev`); a short section in the monorepo `CLAUDE.md` for agents; a docs checkbox and hotfix reminder in the PR template.
- [ ] Slice 4 — Panel links. The panel's docs links point at `docs-v2.lunarphp.com` and read `config('lunar.panel.support_url')`; the 2.x `README.md` link updated.
- [ ] Slice 5 — Redirects and shared content. Cloudflare Redirect Rules for `docs.lunarphp.com` as above; `support/` and `logs/` moved to the main website, with redirects from their old paths.
- [ ] Slice 6 — v1. The same as slices 1–2 for `1.x` and `docs-live-1.x`, published by the next 1.x release.
- [ ] Slice 7 — Archive. `lunarphp/docs` reduced to `0.x/`, its Mintlify project moved to `docs-v0.lunarphp.com`, and its README and `CLAUDE.md` updated to say it is archived and where current docs live.
