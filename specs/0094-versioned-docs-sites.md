# 0094 — Versioned docs written in the monorepo and published from releases

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-10-28
- Revised: 2026-11-06 — one site assembled by Mintlify's multirepo Action, replacing separate per-version sites
- TODO item: Versioned docs — each major version's docs live in the monorepo on its version branch and publish when that version releases (spec 0094)

## Problem

The documentation is a separate repository, `lunarphp/docs`, built by Mintlify as one site with all versions side by side (`0.x/`, `1.x/`, `2.x/`, plus `support/` and `logs/`). It was split out because Mintlify could not build from a subdirectory or from more than one repository. That arrangement now causes three problems.

**Docs and code change in different places.** A change to v2 behaviour needs a code PR in `lunarphp/lunar` and a separate docs PR in `lunarphp/docs`, with merge order explained across repositories and the same facts written twice. Docs drift as a result: the 2.x pricing reference described the pricing manager reading the auth guard after spec 0091 removed that, and the cart and order extension pages said modifiers never run long after the line actions they relied on had stopped being called.

**A release of one version publishes every version.** Docs PRs for all versions merge into `next`, and `next` is merged into `main` (the branch Mintlify deploys) at release time. A 1.x release therefore publishes every unreleased 2.x docs change waiting on `next`, and the reverse.

**One deploy branch cannot follow version branches.** The monorepo has no trunk: code lives on version branches (`1.x`, `2.x`; `0.1`–`0.8` for v0). A Mintlify site deploys one branch, so it cannot take each version's docs from its own branch.

Mintlify now ships [`mintlify/multirepo-action`](https://github.com/mintlify/multirepo-action), a GitHub Action that runs in a parent docs repository, copies docs from any number of source repositories at a branch, tag, or commit, and assembles one `docs.json` from them. It needs no Enterprise plan. That makes it possible to write docs next to the code and still publish one site.

## Proposal

Each version's docs are written in a `docs/` directory on that version's branch in the monorepo, in the same PR as the code they describe. `lunarphp/docs` stays the one Mintlify site at `docs.lunarphp.com`, with the version switcher and cross-version search it has today. It becomes private, and the multirepo Action assembles its `1.x/` and `2.x/` folders and its `docs.json` from each version's published docs. A version's docs publish when that version releases; a PR that changes only docs publishes when it merges.

### Docs on a version branch

Each version branch carries a top-level `docs/` directory:

```
docs/
  docs.json        this version's navigation; previews on its own with `mint dev`
  getting-started/
  guides/
  reference/
  ...
  README.md        contributor guide (see Documentation)
```

The contents are the current `lunarphp/docs` version directory with the version prefix dropped: `2.x/reference/carts.mdx` becomes `docs/reference/carts.mdx` on the `2.x` branch. Page paths in `docs.json` and root-relative links in pages drop the prefix too (`/reference/carts`, not `/2.x/reference/carts`); the Action adds it back when it publishes. A link to another version keeps that version's prefix (`/1.x/...`), and the Action leaves it unchanged.

`docs/docs.json` is a complete Mintlify config so that `mint dev` previews the version on its own. The Action reads only its `navigation` (a `tabs` array) and `redirects`; theme, colours, logo and every other setting come from the docs repository, so differences in the branch copy affect only local previews.

`docs/` is not a sub-package, so `split_packages.yml` ignores it. `tests.yml` and `static-analysis.yml` add `docs/**` to `paths-ignore` so a docs-only PR does not run the PHP matrix.

### What is published: live branches

Each version has a branch in the monorepo holding the commit whose `docs/` is published:

| Version branch | Live branch |
| --- | --- |
| `2.x` | `docs-live-2.x` |
| `1.x` | `docs-live-1.x` |

A live branch is never developed on. No PR targets it and nobody commits to it: the publishing workflows below move it, and a repository ruleset allows only the Lunar Docs GitHub App to update it. Its tree is the version branch at the last release, plus the docs-only PRs published since.

Live branches rather than tags, because `split_packages.yml` and `publish_npm.yml` run on every tag push (`tags: "*"`), and a tag pushed to publish docs would split and publish every package. A tag also runs the workflow files in the tagged commit, so narrowing those filters would not protect commits built on releases cut before the change.

### Publishing a release

`.github/workflows/publish-docs.yml` runs on `release: published`. It reads the major version from the tag (`1.5.0` → `1`, `2.0.0-alpha.7` → `2`) and resets `docs-live-<major>.x` to the release commit. Pre-releases publish too, since the v2 docs describe the alpha.

The branch is reset, not merged, so the published docs are exactly the docs at that release and no merge can conflict. Docs-only PRs published since the previous release are already in the release, because they merged into the version branch first. A docs-only PR that merged and published after the release commit was cut is not; the workflow re-applies each of those after the reset, so a release never unpublishes them.

A release event runs the workflow file in the tagged commit, so `publish-docs.yml` must be on a version branch before that version's first release publishes through it.

### Publishing a docs-only PR

`.github/workflows/publish-docs-pr.yml` runs when a PR into `1.x` or `2.x` that touches `docs/` merges. What the PR contains decides what happens, with no label needed in the common case:

| The merged PR | Outcome |
| --- | --- |
| Changes code and `docs/` | Held until the next release of that version |
| Changes only `docs/`, labelled `docs-hold` | Held until the next release |
| Changes only `docs/`, and its diff applies cleanly to the live branch | Published now |
| Changes only `docs/`, and its diff does not apply | Held until the next release |

A code PR documents that code, so its docs wait for the release that ships it. A docs-only PR is usually a correction, so it publishes on merge. A docs-only PR about unreleased work usually touches pages that are new or changed since the release, so its diff does not apply to the live branch and it holds itself. `docs-hold` covers the remaining case: a docs-only PR describing unreleased behaviour on a page unchanged since the release. Forgetting it publishes docs for merged but unreleased code early; it never stops a fix from publishing.

To publish, the workflow takes the PR's diff as reviewed (from its merge base to its head, limited to `docs/`), so squash, merge, and rebase merges behave the same. It applies the diff to the live branch, commits it as `Publish docs from #<number>`, and pushes.

The workflow comments on every merged PR that touches `docs/` with the outcome: "Published to docs.lunarphp.com/2.x", or "Held until the next 2.x release" with the reason (changes code, labelled `docs-hold`, or does not apply to the published docs). Nothing is published or held silently.

`workflow_dispatch` on the same workflow publishes a merged PR's `docs/` changes on request, for a PR that was held but should go out now. It applies the same diff the same way, and fails with the conflicting files if the diff does not apply.

| Change | Where | Published |
| --- | --- | --- |
| Docs for new or changed code | The code PR, on the version branch | At the next release of that version |
| Docs-only correction | PR to the version branch | On merge |
| Docs-only change for unreleased work | PR to the version branch | At the next release (held automatically, or with `docs-hold`) |
| Release | `docs-live-<major>.x` reset to the release commit | On publishing the GitHub release |

### Assembling the site

After moving a live branch, the publishing workflows send a `docs-published` `repository_dispatch` to `lunarphp/docs`. Its `.github/workflows/sync.yml` runs the multirepo Action in `push` mode on `main`, which Mintlify deploys. The Action also runs on `workflow_dispatch` and on pushes to `main` that change `docs.base.json` or the Action config. Every run is a full sync, so runs can be retried or triggered together without drifting.

`.github/mintlify-multirepo.yml`:

```yaml
sources:
  - repo: lunarphp/lunar
    ref: docs-live-1.x
    path: docs
    destination: 1.x
    exclude: [README.md]
  - repo: lunarphp/lunar
    ref: docs-live-2.x
    path: docs
    destination: 2.x
    exclude: [README.md]
```

Each run replaces `1.x/` and `2.x/` with the `docs/` folder at each live branch, prefixes page paths, redirects, and root-relative links in `.md` and `.mdx` files (outside code blocks) with the destination, and writes `docs.json` from `docs.base.json` with each version's navigation and redirects merged in. It records the commit each folder came from in `.mintlify-multirepo.json`. If either source fails to fetch or merge, the run fails before changing the repository.

`docs.base.json` holds everything that belongs to no single version: the theme and other shared settings, the version labels, their order and which is the default, the v0 entry, the Support and Flight Plan tabs, and redirects between versions. Each version's tabs go where its `sourceRef` is:

```json
{
  "navigation": {
    "versions": [
      {
        "version": "v1.x",
        "default": true,
        "tabs": [
          { "sourceRef": "1.x" },
          { "tab": "Support", "groups": [{ "group": "Community", "pages": ["support/community"] }] },
          { "tab": "Flight Plan", "pages": ["logs/flight-plan"] }
        ]
      },
      {
        "version": "v2.x (alpha)",
        "tabs": [{ "sourceRef": "2.x" }, { "tab": "Support", "...": "..." }, { "tab": "Flight Plan", "...": "..." }]
      },
      { "version": "v0.x", "tabs": ["...unchanged..."] }
    ]
  }
}
```

The `sourceRef` names a destination (`2.x`) rather than the repository, because both sources are `lunarphp/lunar` and a repository reference is ambiguous. The Action matches `sourceRef` by repository only today; [mintlify/multirepo-action#<number>](https://github.com/mintlify/multirepo-action) adds matching by destination. Until it is released, `sync.yml` pins the Action to that PR's commit.

Changing the default version, for example making v2 the default at beta, is a one-line edit to `docs.base.json`.

### The docs repository

| Path | Owner |
| --- | --- |
| `docs.base.json`, `.github/`, `support/`, `logs/`, `0.x/`, shared assets (`logo/`, `favicon.svg`, `style.css`, `tally-embed.js`) | Edited by maintainers |
| `1.x/`, `2.x/`, `docs.json`, `.mintlify-multirepo.json` | Written by the Action; overwritten on every sync |

`lunarphp/docs` becomes private once both versions publish through the Action and its open community PRs are resolved. Its README says where the docs are written and that the generated paths are never edited by hand. Mintlify's web editor and its agent commit to `main`, where the next sync overwrites a change to a version folder, so both are turned off.

### Tokens

A GitHub App, Lunar Docs, is installed on `lunarphp/lunar` and `lunarphp/docs` with `contents: write`. The monorepo workflows use its token to move live branches (the ruleset's only bypass actor) and to dispatch to `lunarphp/docs`. The sync in `lunarphp/docs` clones the public monorepo without a token and pushes to its own `main` with `github.token`. The App's token, unlike `github.token`, is not tied to a person and can be named in a ruleset.

## Alternatives considered

- **A Mintlify project per version, on its own subdomain** (the first version of this spec). Each version branch's `docs/` deploys from a live branch as its own site (`docs-v2.lunarphp.com`, `docs-v1.lunarphp.com`), with `docs.lunarphp.com` turned into Cloudflare redirects and v0 archived as a third project. It solves the same three problems but loses the version switcher and cross-version search, changes every docs URL, moves `support/` and `logs/` to the main website, and repoints the panel's 13 hard-coded docs links. Assembling one site keeps all of that as it is.
- **Our own sync script.** Copying folders, prefixing links and navigation, and merging `docs.json` is what the multirepo Action does, including image and snippet paths and links inside code blocks that must be left alone. Maintaining that ourselves earns nothing; the one gap (`sourceRef` by destination) is a small upstream change.
- **Tags as the published refs.** The Action can sync from a tag, and tags never move, but tag pushes run the package split and npm publish workflows (see Live branches).
- **A `docs-hotfix` label to publish a docs-only PR on merge.** Forgetting the label fails silently: the fix waits for the next release and nobody notices. Deciding by the PR's contents publishes corrections by default and holds unreleased work, with a comment either way.
- **Publish whenever the version branch changes.** Docs would describe merged but unreleased code, immediately for v2, where every spec PR merges into `2.x` long before a release.
- **Mintlify multi-repository projects.** Enterprise only, and a repository can be added once per project, so it cannot take `docs/` from both `1.x` and `2.x`.
- **One site with all versions on one monorepo branch.** The monorepo has no trunk; putting every version's docs on `2.x` would put v1's docs on the v2 code branch.
- **Keep the docs repository and add a staging branch per version.** Fixes publishing one version with another, but docs still change in a different repository from the code.
- **Do nothing.** Every change keeps needing two PRs in two repositories, and every release of one version publishes the other's pending docs.

## Migration impact

- **Database migrations required:** none.
- **Breaking changes to the public contract surface:** none. Docs URLs do not change.
- **Upgrade path for v1.x consumers:** none.
- **Translation / locale impact:** none.
- **Filament / admin impact:** none. The panel's docs links keep pointing at `docs.lunarphp.com`.
- **Contributors:** v1 and v2 docs contributions move from `lunarphp/docs` to the version branches in the monorepo.
- **Manual setup outside the repository:** the Lunar Docs GitHub App, the live-branch ruleset, the `docs-hold` label, the Mintlify web editor and agent turned off, and `lunarphp/docs` made private.

## Decisions

- **v1 and v2 move together.** With one site and unchanged URLs there is no new domain to prove first, and `lunarphp/docs` cannot go private while v1 is still edited there.
- **The default version is set in `docs.base.json`.** v2 becomes the default from the first beta that commits to a supported upgrade path to stable.
- **`lunarphp/docs` becomes private** after the switch. Its public source is the monorepo.

## Open questions

- **Upstream `sourceRef` by destination.** If Mintlify declines the change, `sync.yml` stays pinned to a fork of the Action. Owner: Glenn, resolved before slice 2 merges.
- **Mintlify with a private repository.** The Mintlify GitHub App needs access to the private `lunarphp/docs`; confirmed in the dashboard before slice 5.

## References

- `lunarphp/docs` — the current docs repository; its `docs.json` holds the navigation and the three existing redirects.
- `.github/workflows/tests.yml`, `static-analysis.yml` (`paths-ignore`), `split_packages.yml` and `publish_npm.yml` (tag triggers).
- Mintlify: [multirepo Action](https://www.mintlify.com/docs/deploy/multirepo-action), [`mintlify/multirepo-action`](https://github.com/mintlify/multirepo-action), [multi-repository projects](https://www.mintlify.com/docs/deploy/multi-repo), [monorepo setup](https://www.mintlify.com/docs/deploy/monorepo).

## Implementation plan

Ordered so nothing in `lunarphp/docs` is removed until both versions publish through the Action.

- [ ] Slice 1 — Content. Move `2.x/` from `lunarphp/docs` into `docs/` on `2.x`, and `1.x/` into `docs/` on `1.x` (one PR each), with prefix-free page paths and links, a standalone `docs.json` per branch, and `docs/**` added to `paths-ignore` in `tests.yml` and `static-analysis.yml`. Done by a re-runnable script so docs merged into `lunarphp/docs` meanwhile can be imported again. Checked with `mint dev` and `mint broken-links`. `2.x/` in `lunarphp/docs` is read-only from this point; its open docs PRs move into their code PRs.
- [ ] Slice 2 — Assembly. In `lunarphp/docs`: `docs.base.json`, `.github/mintlify-multirepo.yml`, and `sync.yml`, pinned to the `sourceRef` change. The live branches are seeded from each version's last release plus a commit importing the docs currently on `main`, so the switch publishes nothing new. Proven by a sync whose `docs.json` and version folders match today's site, checked with `mint broken-links`.
- [ ] Slice 3 — Publishing. `publish-docs.yml` and `publish-docs-pr.yml` on `2.x` and `1.x`, the Lunar Docs GitHub App, the live-branch ruleset, and the `docs-hold` label. Proven by a docs-only PR that publishes, a code PR that is held, and the next 2.x pre-release.
- [ ] Slice 4 — Documentation. `docs/README.md` on each version branch (writing docs alongside code, when they publish, `docs-hold`, previewing with `mint dev`); a short section in the monorepo `CLAUDE.md` for agents; a docs checkbox in the PR template.
- [ ] Slice 5 — Switch. Mintlify web editor and agent off; the community PRs in `lunarphp/docs` merged or moved to the monorepo; the `next` branch retired; `lunarphp/docs` README updated and the repository made private.
