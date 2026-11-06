# 0094 — Versioned docs written in the monorepo and published from releases

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-10-28
- Revised: 2026-11-06 — one site assembled by Mintlify's multirepo Action from each version's latest release, replacing separate per-version sites
- TODO item: Versioned docs — each major version's docs live in the monorepo on its version branch and publish when that version releases (spec 0094)

## Problem

The documentation is a separate repository, `lunarphp/docs`, built by Mintlify as one site with all versions side by side (`0.x/`, `1.x/`, `2.x/`, plus `support/` and `logs/`). It was split out because Mintlify could not build from a subdirectory or from more than one repository. That arrangement now causes three problems.

**Docs and code change in different places.** A change to v2 behaviour needs a code PR in `lunarphp/lunar` and a separate docs PR in `lunarphp/docs`, with merge order explained across repositories and the same facts written twice. Docs drift as a result: the 2.x pricing reference described the pricing manager reading the auth guard after spec 0091 removed that, and the cart and order extension pages said modifiers never run long after the line actions they relied on had stopped being called.

**A release of one version publishes every version.** Docs PRs for all versions merge into `next`, and `next` is merged into `main` (the branch Mintlify deploys) at release time. A 1.x release therefore publishes every unreleased 2.x docs change waiting on `next`, and the reverse.

**One deploy branch cannot follow version branches.** The monorepo has no trunk: code lives on version branches (`1.x`, `2.x`; `0.1`–`0.8` for v0). A Mintlify site deploys one branch, so it cannot take each version's docs from its own branch.

Mintlify now ships [`mintlify/multirepo-action`](https://github.com/mintlify/multirepo-action), a GitHub Action that runs in a parent docs repository, copies docs from any number of source repositories at a branch, tag, or commit, and assembles one `docs.json` from them. It needs no Enterprise plan. That makes it possible to write docs next to the code and still publish one site.

## Proposal

Each version's docs are written in a `docs/` directory on that version's branch in the monorepo, in the same PR as the code they describe. `lunarphp/docs` stays the one Mintlify site at `docs.lunarphp.com`, with the version switcher and cross-version search it has today. It becomes private, and the multirepo Action assembles its `1.x/` and `2.x/` folders and its `docs.json` from each version's latest release. A version's docs publish when that version releases, and only then.

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

### What is published

Each version publishes the `docs/` folder at its latest release tag. The tags already exist, so publishing creates no branches, tags, or commits in the monorepo, and nothing there needs protecting.

A version's latest release is its highest stable tag by semantic version (`1.5.0`, not a `1.4.1` cut after it), or its highest pre-release while it has no stable release (`2.0.0-alpha.6` today). Highest by version rather than most recent by date, so a patch to an older minor never replaces newer docs; stable over pre-release, so a `1.6.0-beta.1` does not replace the `1.5.0` docs v1 users rely on. Only a release whose tree contains `docs/docs.json` counts. Until a version has one, its source uses the starting ref set in the Action config: the commit that moved its docs into the monorepo (slice 1).

Docs merged into a version branch, alongside code or on their own, publish at that version's next release. Between releases, the published docs are exactly the docs at the last release.

Only `1.x` and `2.x` are sources. Maintenance branches such as `1.4` (Filament v3, fixes only) carry no `docs/` and publish nothing; their releases have no `docs/docs.json` and sort below the version's latest release anyway, so they never replace its docs. v1 docs fixes go to `1.x`. v0 is no longer supported, so `0.x/` stays in `lunarphp/docs` and is edited there by hand.

### Publishing a release

`.github/workflows/publish-docs.yml` in the monorepo runs on `release: published` and sends a `docs-published` `repository_dispatch` to `lunarphp/docs`. That is its only job; the docs repository works out what to publish. A release event runs the workflow file in the tagged commit, so a release cut from a commit that predates the workflow sends no dispatch, and the daily scheduled sync publishes it instead.

### Assembling the site

`.github/workflows/sync.yml` in `lunarphp/docs` runs on the `docs-published` dispatch, on a daily schedule, on `workflow_dispatch`, and on pushes to `main` that change `docs.base.json` or the Action config. It resolves each version's latest release from the `lunarphp/lunar` releases, writes that tag into the source's `ref`, and runs the multirepo Action in `push` mode on `main`, which Mintlify deploys. The Action commits only when something changed. Every run is a full sync from release tags, so runs can be retried, repeated, or triggered together without drifting.

`.github/mintlify-multirepo.yml` (the workflow replaces each `ref` with the resolved release before the Action runs; the committed value is the starting ref):

```yaml
sources:
  - repo: lunarphp/lunar
    ref: <slice 1 merge commit on 1.x>
    path: docs
    destination: 1.x
    exclude: [README.md]
  - repo: lunarphp/lunar
    ref: <slice 1 merge commit on 2.x>
    path: docs
    destination: 2.x
    exclude: [README.md]
```

Each run replaces `1.x/` and `2.x/` with the `docs/` folder at each version's release, prefixes page paths, redirects, and root-relative links in `.md` and `.mdx` files (outside code blocks) with the destination, and writes `docs.json` from `docs.base.json` with each version's navigation and redirects merged in. It records the commit each folder came from in `.mintlify-multirepo.json`. If either source fails to fetch or merge, the run fails before changing the repository.

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

The `sourceRef` names a destination (`2.x`) rather than the repository, because both sources are `lunarphp/lunar` and a repository reference is ambiguous. The Action matches `sourceRef` by repository only today; [mintlify/multirepo-action#12](https://github.com/mintlify/multirepo-action/pull/12) adds matching by destination. Until it is released, `sync.yml` pins the Action to that PR's commit (`86f8682`) and moves to `@v1` once it ships.

Changing the default version, for example making v2 the default at beta, is a one-line edit to `docs.base.json`.

### The docs repository

| Path | Owner |
| --- | --- |
| `docs.base.json`, `.github/`, `support/`, `logs/`, `0.x/`, shared assets (`logo/`, `favicon.svg`, `style.css`, `tally-embed.js`) | Edited by maintainers |
| `1.x/`, `2.x/`, `docs.json`, `.mintlify-multirepo.json` | Written by the Action; overwritten on every sync |

`lunarphp/docs` becomes private once both versions publish through the Action and its open community PRs are resolved. Its README says where the docs are written and that the generated paths are never edited by hand. Mintlify's web editor and its agent commit to `main`, where the next sync overwrites a change to a version folder, so both are turned off.

### No early publishing

Docs publish with releases and only with releases. There is no hotfix path: a docs fix merges into the version branch like any other change and goes out with that version's next release. When docs need to go out and no code is waiting, a patch or pre-release ships them. That is no different from a patch release today, which tags every split package whether or not it changed. Editing `1.x/` or `2.x/` in `lunarphp/docs` directly does not work, because the next sync overwrites it.

### Tokens

`publish-docs.yml` sends its dispatch with a token that has `contents: write` on `lunarphp/docs` only, from a GitHub App (preferred, since it is not tied to a person) or a fine-grained personal token. The sync in `lunarphp/docs` reads the public monorepo's releases and clones it without a token, and pushes to its own `main` with `github.token`.

## Alternatives considered

- **A Mintlify project per version, on its own subdomain** (the first version of this spec). Each version branch's `docs/` deploys from a live branch as its own site (`docs-v2.lunarphp.com`, `docs-v1.lunarphp.com`), with `docs.lunarphp.com` turned into Cloudflare redirects and v0 archived as a third project. It solves the same three problems but loses the version switcher and cross-version search, changes every docs URL, moves `support/` and `logs/` to the main website, and repoints the panel's 13 hard-coded docs links. Assembling one site keeps all of that as it is.
- **Our own sync script.** Copying folders, prefixing links and navigation, and merging `docs.json` is what the multirepo Action does, including image and snippet paths and links inside code blocks that must be left alone. Maintaining that ourselves earns nothing; the one gap (`sourceRef` by destination) is a small upstream change.
- **A live branch per version in the monorepo** (`docs-live-2.x`), reset at each release and taking docs-only PRs between releases. Corrections could publish on merge, but it adds branches nobody develops on, a ruleset and an App to protect them, and a workflow deciding per PR whether to publish or hold. The release tags already record what was released.
- **Publish a PR that changes only `docs/` on merge.** Most corrections could go out immediately, but a docs-only PR about unreleased work would publish early unless someone held it.
- **Publish chosen PRs early.** Maintainers list merged PRs whose docs publish ahead of the release, applied on top of the latest release's docs. It works, but it is cherry-picking: a PR whose docs conflict with unreleased changes cannot go out, and a list entry has to be checked when added and dropped when a release contains it or no longer applies. Urgent docs fixes are rare, and releasing more often gets everything else out.
- **Publish each version branch as it stands.** The simplest option, but it documents features before they are released.
- **Tag each docs publish in the monorepo.** Tag pushes run the package split and npm publish workflows (`tags: "*"`), and a tag runs the workflow files in its own commit, so narrowing those filters would not protect commits built on earlier releases.
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
- **Manual setup outside the repository:** the dispatch token as a monorepo secret, the Mintlify web editor and agent turned off, and `lunarphp/docs` made private.

## Decisions

- **v1 and v2 move together.** With one site and unchanged URLs there is no new domain to prove first, and `lunarphp/docs` cannot go private while v1 is still edited there.
- **The default version is set in `docs.base.json`.** v2 becomes the default from the first beta that commits to a supported upgrade path to stable.
- **`lunarphp/docs` becomes private** after the switch. Its public source is the monorepo.
- **Docs publish only with releases.** No early or hotfix publishing; a patch or pre-release ships docs that cannot wait. Releases become more frequent so docs improvements are not held for long.

## Open questions

- **Upstream `sourceRef` by destination.** If Mintlify declines [#12](https://github.com/mintlify/multirepo-action/pull/12), `sync.yml` stays pinned to a fork of the Action. Owner: Glenn, resolved before slice 2 merges.
- **Mintlify with a private repository.** The Mintlify GitHub App needs access to the private `lunarphp/docs`; confirmed in the dashboard before slice 5.

## References

- `lunarphp/docs` — the current docs repository; its `docs.json` holds the navigation and the three existing redirects.
- `.github/workflows/tests.yml`, `static-analysis.yml` (`paths-ignore`), `split_packages.yml` and `publish_npm.yml` (tag triggers).
- Mintlify: [multirepo Action](https://www.mintlify.com/docs/deploy/multirepo-action), [`mintlify/multirepo-action`](https://github.com/mintlify/multirepo-action), [multi-repository projects](https://www.mintlify.com/docs/deploy/multi-repo), [monorepo setup](https://www.mintlify.com/docs/deploy/monorepo).

## Implementation plan

Ordered so nothing in `lunarphp/docs` is removed until both versions publish through the Action.

- [ ] Slice 1 — Content. Move `2.x/` from `lunarphp/docs` into `docs/` on `2.x`, and `1.x/` into `docs/` on `1.x` (one PR each), with prefix-free page paths and links, a standalone `docs.json` per branch, and `docs/**` added to `paths-ignore` in `tests.yml` and `static-analysis.yml`. Done by a re-runnable script so docs merged into `lunarphp/docs` meanwhile can be imported again. Checked with `mint dev` and `mint broken-links`. `2.x/` in `lunarphp/docs` is read-only from this point; its open docs PRs move into their code PRs.
- [ ] Slice 2 — Assembly. In `lunarphp/docs`: `docs.base.json`, `.github/mintlify-multirepo.yml` with each version's starting ref, and `sync.yml` (release resolution, the Action pinned to the `sourceRef` change). Proven by a sync whose version folders and navigation match today's site, checked with `mint broken-links`.
- [ ] Slice 3 — Release trigger. `publish-docs.yml` on `2.x` and `1.x`, and the dispatch token. Proven by the next 2.x pre-release publishing through it.
- [ ] Slice 4 — Documentation. `docs/README.md` on each version branch (writing docs alongside code, prefix-free links, docs publishing at the next release, previewing with `mint dev`); a short section in the monorepo `CLAUDE.md` for agents; a docs checkbox in the PR template.
- [ ] Slice 5 — Switch. Mintlify web editor and agent off; the community PRs in `lunarphp/docs` merged or moved to the monorepo; the `next` branch retired (default branch set to `main`, `next` merged into `main`, every PR targeting `next` retargeted or closed, then `next` deleted); `lunarphp/docs` README updated and the repository made private.
